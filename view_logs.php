<?php
require_once 'db.php';
require_once 'protect.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}

// Logique de pagination
$per_page = 10; // 25 entrées par page
$page = (int)($_GET['page'] ?? 1);
$offset = ($page - 1) * $per_page;

$total_logs = $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();
$total_pages = ceil($total_logs / $per_page);

// Récupérer les logs pour la page actuelle
$stmt = $pdo->prepare("
    SELECT l.action, l.created_at, u.first_name
    FROM logs l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

include 'header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Journal d'audit</h2>
        <a href="view_materials.php" class="btn btn-primary">Matériels</a>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Date et Heure</th>
                <th>Utilisateur</th>
                <th>Action Effectuée</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="3" style="text-align: center; padding: 2rem;">Aucune action enregistrée.</td></tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= e((new DateTime($log['created_at']))->format('d/m/Y H:i:s')) ?></td>
                        <td><?= e($log['first_name']) ?></td>
                        <td><?= e($log['action']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="pagination">
    <?php if ($total_pages > 1): ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>