<?php
require_once 'db.php';
require_once 'protect.php';


$allowed_roles = ['admin', 'gestionnaire'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    die("Accès refusé. Vous n'avez pas les permissions pour effectuer cette action.");
}


$error = '';
$success = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF');
    $agent_id = (int)($_POST['agent_id'] ?? 0);
    $material_id = (int)($_POST['material_id'] ?? 0);
    $assigned_at = $_POST['assigned_at'] ?? date('Y-m-d');
    if ($agent_id <= 0 || $material_id <= 0) {
        $error = "Veuillez sélectionner un agent et un matériel.";
    } else {
        try {
            $pdo->beginTransaction();
            // Vérifier que le matériel est bien disponible avant de l'attribuer
            $stmt = $pdo->prepare("SELECT status FROM materials WHERE id = ? FOR UPDATE");
            $stmt->execute([$material_id]);
            $mat = $stmt->fetch();
            if (!$mat || $mat['status'] !== 'available') {
                throw new Exception('Ce matériel n\'est pas disponible pour une attribution.');
            }
            // Insérer l'attribution
            $ins = $pdo->prepare("INSERT INTO assignments (agent_id, material_id, assigned_at, condition_on_assign, note)
                VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$agent_id, $material_id, $assigned_at, $_POST['condition_on_assign'] ?? null, $_POST['note'] ?? null]);
            
            // On récupère l'ID de la nouvelle attribution
            $assignment_id = $pdo->lastInsertId();

            $upd = $pdo->prepare("UPDATE materials SET status = 'assigned' WHERE id = ?");
            $upd->execute([$material_id]);

            $pdo->commit();
            
            // --- DÉBUT LOG ---
            $stmt_agent = $pdo->prepare("SELECT first_name, last_name FROM agents WHERE id = ?");
            $stmt_agent->execute([$agent_id]);
            $agent = $stmt_agent->fetch();
            $agent_name = $agent['first_name'] . ' ' . $agent['last_name'];

            $stmt_mat = $pdo->prepare("SELECT c.name, m.brand FROM materials m JOIN categories c ON m.category_id = c.id WHERE m.id = ?");
            $stmt_mat->execute([$material_id]);
            $mat_info = $stmt_mat->fetch();
            $mat_name = $mat_info['name'] . ' ' . $mat_info['brand'];
            
            log_action($pdo, "Attribution du matériel \"{$mat_name}\" à l'agent \"{$agent_name}\"");
            // --- FIN LOG ---

            // On redirige vers le script de génération de PDF
            header('Location: view_agent.php?id=' . $agent_id . '&download_pdf=' . $assignment_id);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de l'attribution: ".e($e->getMessage());
        }
    }
}

// Récupérer uniquement les agents
$agents = $pdo->query("SELECT id, first_name, last_name FROM agents ORDER BY last_name, first_name")->fetchAll();

include 'header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Attribuer matériel</h2>
        <a href="add_material.php" class="btn btn-primary">+Ajouter matériel</a>
</div>

<div class="form-container">
    <?php if(!empty($error)): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>
    <?php if(isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <p class="alert alert-success">Attribution réussie !</p>
    <?php endif; ?>

    <form method="post" id="assignmentForm">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      
        <label for="agent_id">Agent:</label>
        <select id="agent_id" name="agent_id" required>
          <option value="">-- choisir un agent --</option>
          <?php foreach($agents as $a): ?>
            <option value="<?= e($a['id']) ?>"><?= e($a['last_name'].' '.$a['first_name']) ?></option>
          <?php endforeach; ?>
        </select>
        
        <hr style="border: none; border-top: 1px solid var(--color-border); margin: 2rem 0;">

        <label for="material_search">Rechercher un matériel disponible:</label>
        <input type="text" id="material_search" placeholder="Commencez à taper pour filtrer...">
        <div id="material_results"></div>
        <input type="hidden" name="material_id" id="selected_material_id">
      
        <hr style="border: none; border-top: 1px solid var(--color-border); margin: 2rem 0;">

        <label for="assigned_at">Date d'attribution:</label>
        <input id="assigned_at" name="assigned_at" type="date" value="<?= date('Y-m-d') ?>" required>
        
        <label for="condition_on_assign">État lors de l'attribution:</label>
        <input id="condition_on_assign" type="text" name="condition_on_assign" required>
        
        <label for="note">Note:</label>
        <textarea id="note" name="note" placeholder="Ajouter d'autres informations sur l'attribution ..."></textarea>
        
        <button type="submit" class="btn btn-primary">Attribuer le matériel</button>
    </form>
</div>

<style>
    #material_results { border: 1px solid var(--color-border); max-height: 250px; overflow-y: auto; margin-top: 5px; border-radius: var(--radius); }
    .result-item { padding: 10px; border-bottom: 1px solid var(--color-border); cursor: pointer; }
    .result-item:last-child { border-bottom: none; }
    .result-item:hover { background-color: var(--color-bg); }
    .result-item.selected { background-color: #DBEAFE; }
    .result-item span { display: block; font-size: 0.9em; color: var(--color-text-muted); }
    .result-item .status-assigned { color: var(--color-danger); font-weight: bold; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('material_search');
    const resultsContainer = document.getElementById('material_results');
    const selectedMaterialId = document.getElementById('selected_material_id');
    const form = document.getElementById('assignmentForm');
    let selectedDiv = null;

    function displayResults(materials) {
        resultsContainer.innerHTML = '';
        if (materials.length === 0) {
            resultsContainer.innerHTML = '<div class="result-item">Aucun matériel trouvé.</div>';
        } else {
            materials.forEach(mat => {
                const div = document.createElement('div');
                div.classList.add('result-item');
                
                let statusText = '';
                if (mat.status !== 'available') {
                    statusText = ` <span class="status-assigned">(Déjà attribué)</span>`;
                    div.style.opacity = '0.6';
                } else {
                    div.dataset.id = mat.id; // Only add data-id if available
                }
                
                div.innerHTML = `
                    <strong>${mat.type} ${mat.brand} ${mat.model}</strong> ${statusText}
                    <span>S/N: ${mat.serial_number || '-'} | Asset: ${mat.asset_tag || '-'}</span>
                `;
                
                if (mat.status === 'available') {
                    div.addEventListener('click', function() {
                        if (selectedDiv) {
                            selectedDiv.classList.remove('selected');
                        }
                        this.classList.add('selected');
                        selectedDiv = this;
                        selectedMaterialId.value = this.dataset.id;
                        searchInput.value = `${mat.type} ${mat.brand} (S/N: ${mat.serial_number})`;
                        resultsContainer.style.display = 'none'; // Cacher les résultats après sélection
                    });
                }
                resultsContainer.appendChild(div);
            });
        }
    }

    function performSearch(query) {
        fetch(`search_material_api.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                resultsContainer.style.display = 'block';
                displayResults(data.results);
            });
    }

    // --- CORRECTIONS LOGIQUES CI-DESSOUS ---

    // 1. Logique au chargement de la page
    if (selectedMaterialId.value > 0) {
        // Un matériel est déjà sélectionné, on cache la liste de recherche.
        resultsContainer.style.display = 'none';
    } else {
        // Aucun matériel n'est pré-sélectionné, on charge la liste complète.
        performSearch('');
    }

    // 2. Logique quand l'utilisateur clique dans la barre de recherche
    searchInput.addEventListener('focus', function() {
        // On affiche toujours la liste quand on clique dans le champ
        resultsContainer.style.display = 'block';
    });

    // 3. Logique quand l'utilisateur tape du texte
    searchInput.addEventListener('keyup', function() {
        // Si l'utilisateur tape, c'est pour faire une nouvelle recherche.
        // On vide l'ID pré-sélectionné pour le forcer à re-cliquer sur un item.
        selectedMaterialId.value = '';
        performSearch(this.value);
    });

    // 4. Logique du bouton "Rechercher"
    searchButton.addEventListener('click', function() {
        performSearch(searchInput.value);
    });

    searchInput.addEventListener('keyup', function() {
        const query = searchInput.value;
        selectedMaterialId.value = ''; // Reset on new search
        if (selectedDiv) {
            selectedDiv.classList.remove('selected');
            selectedDiv = null;
        }
        performSearch(query);
    });

    searchInput.addEventListener('focus', function() {
        resultsContainer.style.display = 'block';
    });

    // === NOUVEAU : On charge la liste initiale au chargement de la page ===
    performSearch(''); 
});
</script>

<?php include 'footer.php'; ?>
