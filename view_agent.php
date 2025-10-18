<?php
require_once 'db.php';
require_once 'protect.php';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { echo "Agent introuvable"; exit; }

$agent = $pdo->prepare("SELECT * FROM agents WHERE id = ?");
$agent->execute([$id]); $agent = $agent->fetch();
if (!$agent) { echo "Agent introuvable"; exit; }

// matériels actuellement attribués
$stmt = $pdo->prepare("
  SELECT ass.id as assign_id, m.*, c.name as category_name, ass.assigned_at, ass.condition_on_assign, ass.note
  FROM assignments ass 
  JOIN materials m ON ass.material_id = m.id
  JOIN categories c ON m.category_id = c.id
  WHERE ass.agent_id = ? AND ass.returned_at IS NULL");
$stmt->execute([$id]);
$current = $stmt->fetchAll();

// historique (y compris retours)
$hist = $pdo->prepare("
  SELECT ass.*, m.asset_tag, c.name as category_name, m.brand, m.model
  FROM assignments ass 
  JOIN materials m ON ass.material_id = m.id
  JOIN categories c ON m.category_id = c.id
  WHERE ass.agent_id = ? ORDER BY ass.assigned_at DESC");
$hist->execute([$id]); $history = $hist->fetchAll();

include 'header.php';
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <h2 font-weight="900"><?= e($agent['first_name'].' '.$agent['last_name']) ?></h2>
<?php if ($_SESSION['user_role'] === 'admin'): ?>
    <a href="edit_agent.php?id=<?= e($id) ?>" class="btn btn-primary">Modifier</a>
  <?php endif; ?>
</div>
<div class="content-box">
  <ul style="list-style: none; padding: 0;">
    <li><strong>Email :</strong> <?= e($agent['email']) ?></li>
    <li><strong>Tél :</strong> <?= e($agent['phone']) ?></li>
    <li><strong>Département :</strong> <?= e($agent['department']) ?></li>
    <li><strong>Poste :</strong> <?= e($agent['position']) ?></li>
    <li><strong>ID employé :</strong> <?= e($agent['employee_id']) ?></li>
    <li><strong>Notes :</strong> <?= nl2br(e($agent['notes'])) ?></li>
  </ul>
</div><br>

<h3>Matériel actuellement attribué</h3>
<div id="current-assignments-container" class="table-container">  
  <?php if(empty($current)): ?>
    <p id="no-current-material" style="text-align: center; padding: 2rem;">Aucun matériel actuellement attribué.</p>
  <?php else: ?>
    <table>
      <thead>
          <tr><th>Asset</th><th>Type</th><th>Assigné le</th><th>État</th><th class="actions-cell">Action</th>
              <!--<?php if ($_SESSION['user_role'] === 'admn'): ?>
                  <th class="actions-cell">Action</th>
              <?php endif; ?>-->
          </tr>
      </thead>
      <tbody>
          <?php foreach($current as $c): ?>
            <tr id="assignment-row-<?= e($c['assign_id']) ?>">
              <td><?= e($c['asset_tag'] ?: '#'.$c['id']) ?></td>
              <td><?= e($c['category_name'].' '.$c['brand'].' '.$c['model']) ?></td>
              <td><?= e((new DateTime($c['assigned_at']))->format('d/m/Y')) ?></td>
              <td><?= e($c['condition_on_assign']) ?></td>
              <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
                  <td class="actions-cell">
                    <form method="post" action="return_assignment.php" class="return-form">
                      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="assignment_id" value="<?= e($c['assign_id']) ?>">
                      <button type="submit" class="btn btn-success" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Marquer comme retourné</button>
                    </form>
                  </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div><br>

<h3>Historique des attributions</h3>
<?php if(empty($history)): ?>
  <p>Aucun historique.</p>
<?php else: ?>

<div class="table-container">
  <table>
    <tr><th>Asset</th><th>Type</th><th>Assigné</th><th>Retourné</th><th>Note</th></tr>
    <?php foreach($history as $h): ?>
      <tr>
        <td><?= e($h['asset_tag'] ?: '#'.$h['material_id']) ?></td>
        <td><?= e($h['category_name'].' '.$h['brand'].' '.$h['model']) ?></td>
        <td><?= e($h['assigned_at']) ?></td>
        <td><?= e($h['returned_at'] ?? '-') ?></td>
        <td><?= e($h['note']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- PARTIE 1 : GESTION DU TÉLÉCHARGEMENT POST-ATTRIBUTION ---
    const urlParams = new URLSearchParams(window.location.search);
    const pdfToDownload = urlParams.get('download_pdf');

    if (pdfToDownload) {
        window.location.href = 'generate_assignment_pdf.php?id=' + pdfToDownload;
        setTimeout(() => {
            const newUrl = window.location.pathname + '?id=' + urlParams.get('id');
            window.history.replaceState({}, document.title, newUrl);
        }, 1000);
    }

    // --- PARTIE 2 : GESTION DU RETOUR DE MATÉRIEL EN AJAX ---
    const returnForms = document.querySelectorAll('.return-form');
    returnForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            if (!confirm('Confirmer le retour de ce matériel ? Un PDF sera généré.')) {
                return;
            }

            const formData = new FormData(this);
            const actionUrl = this.action;

            fetch(actionUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const rowId = 'assignment-row-' + data.assignment_id;
                    const row = document.getElementById(rowId);
                    if (row) {
                        row.style.transition = 'opacity 0.5s';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            const tbody = row.closest('tbody');
                            if (tbody && tbody.children.length === 0) {
                                document.getElementById('current-assignments-container').innerHTML = '<p id="no-current-material" style="text-align: center; padding: 2rem;">Aucun matériel actuellement attribué.</p>';
                            }
                        }, 500);
                    }
                    
                    // LIGNE MANQUANTE AJOUTÉE ICI
                    // On attend un court instant que l'animation de suppression se termine avant de lancer le téléchargement
                    setTimeout(() => {
                        window.location.href = 'generate_return_pdf.php?id=' + data.assignment_id;
                    }, 600);


                } else {
                    alert('Erreur : ' + (data.message || 'Une erreur est survenue.'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur de communication est survenue.');
            });
        });
    });
});
</script>