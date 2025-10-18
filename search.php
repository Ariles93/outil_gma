<?php
require_once 'db.php';
require_once 'protect.php';

// --- Début de la logique de Pagination ---
$per_page = 6;
$page = (int)($_GET['page'] ?? 1);
$offset = ($page - 1) * $per_page;
$q = trim($_GET['q'] ?? '');

$count_sql = "SELECT COUNT(*) FROM agents";
$main_sql = "SELECT id, first_name, last_name, email, phone, department, position, employee_id FROM agents";
$where_sql = "";
$order_by_sql = " ORDER BY created_at DESC";
$params = [];

if ($q !== '') {
    $like = '%' . str_replace(' ', '%', $q) . '%';
    $where_sql = " WHERE CONCAT(first_name,' ',last_name) LIKE ? OR first_name LIKE ? OR last_name LIKE ?";
    $order_by_sql = " ORDER BY last_name, first_name";
    $params = [$like, $like, $like];
}

// 1. Compter le nombre total de résultats
$total_stmt = $pdo->prepare($count_sql . $where_sql);
$total_stmt->execute($params);
$total_results = $total_stmt->fetchColumn();
$total_pages = ceil($total_results / $per_page);

// --- CORRECTION CI-DESSOUS ---

// 2. Construire la requête principale
$main_sql .= $where_sql . $order_by_sql . " LIMIT ? OFFSET ?";

// 3. Préparer la requête
$main_stmt = $pdo->prepare($main_sql);

// 4. Lier les paramètres de recherche (s'il y en a)
$param_index = 1;
foreach ($params as $value) {
    $main_stmt->bindValue($param_index, $value);
    $param_index++;
}

// 5. Lier les paramètres de pagination explicitement comme des entiers
$main_stmt->bindValue($param_index, $per_page, PDO::PARAM_INT);
$param_index++;
$main_stmt->bindValue($param_index, $offset, PDO::PARAM_INT);

// 6. Exécuter la requête
$main_stmt->execute();
$results = $main_stmt->fetchAll();

include 'header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Rechercher un agent</h2>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="add_agent.php" class="btn btn-primary">+ Nouvel agent</a>
    <?php endif; ?>
</div>

<div class="form-container" style="margin-bottom: 2rem;">
    <form method="get">
      <input name="q" placeholder="Rechercher par prénom ou nom..." type="text" value="<?= e($q) ?>">
      <button type="submit" class="btn btn-primary">Rechercher</button>
        <a href="search.php" class="btn btn-secondary">Réinitialiser</a>
        <a href="export_agents.php" class="btn btn-secondary">Exporter CSV</a>
    </form>
</div>

<?php if ($q !== ''): ?>
    <h3>Résultats de la recherche pour "<?= e($q) ?>"</h3>
<?php else: ?>
    <h3>Derniers agents ajoutés</h3>
<?php endif; ?>

<?php if (empty($results)): ?>
    <p>Aucun agent trouvé.</p>
<?php else: ?>
    <div class="agent-results-grid">
        <?php foreach ($results as $agent): ?>
            <div class="agent-card">
                <div class="agent-card-header">
                    <a href="view_agent.php?id=<?= e($agent['id']) ?>" class="agent-name"><?= e($agent['first_name'].' '.$agent['last_name']) ?></a>
                    <div class="agent-position"><?= e($agent['position'] ?? 'Poste non défini') ?></div>
                </div>
                <div class="agent-card-body">
                    <div class="agent-details">
                        <span><strong>Département :</strong> <?= e($agent['department'] ?? '-') ?></span>
                        <span><strong>Email :</strong> <?= e($agent['email'] ?? '-') ?></span>
                        <span><strong>Téléphone :</strong> <?= e($agent['phone'] ?? '-') ?></span>
                        <span><strong>Matricule :</strong> <?= e($agent['employee_id'] ?? '-') ?></span>
                    </div>
                    <?php
                    $stmtMat = $pdo->prepare("SELECT c.name as category_name, m.brand, m.model FROM assignments a JOIN materials m ON a.material_id = m.id LEFT JOIN categories c ON m.category_id = c.id WHERE a.agent_id = ? AND a.returned_at IS NULL");
                    $stmtMat->execute([$agent['id']]);
                    $materials = $stmtMat->fetchAll();
                    ?>
                    <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire']) && count($materials) > 0): ?>
                        <a href="view_agent.php?id=<?= e($agent['id']) ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; text-align: center;">Enregistrer un retour</a>
                    <?php endif; ?>
                    <?php if (count($materials) == 0): ?>
                        <a href="view_agent.php?id=<?= e($agent['id']) ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; text-align: center;">Voir les détails</a>
                    <?php endif; ?>
                    <div class="agent-materials">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h4>Matériel Actif (<?= count($materials) ?>)</h4>
                        </div>
                        <?php if (empty($materials)): ?>
                            <p class="no-material">Aucun matériel actuellement attribué.</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($materials as $m): ?>
                                    <li><?= e($m['category_name'].' '.$m['brand'].' '.$m['model']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="pagination">
        <?php if ($total_pages > 1): ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&q=<?= e($q) ?>" class="<?= ($i == $page) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>