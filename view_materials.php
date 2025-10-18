<?php
require_once 'db.php';
require_once 'protect.php';

// --- Début de la logique de Tri ---
$allowed_columns = ['category_name', 'model', 'status', 'agent_name'];
$sort_column = $_GET['sort'] ?? 'status';
if (!in_array($sort_column, $allowed_columns)) $sort_column = 'status';

$allowed_orders = ['asc', 'desc'];
$sort_order = strtolower($_GET['order'] ?? 'desc');
if (!in_array($sort_order, $allowed_orders)) $sort_order = 'desc';

$order_by_sql = "";
if ($sort_column === 'agent_name') {
    $order_by_sql = "ag.last_name " . $sort_order . ", ag.first_name " . $sort_order;
} else {
    $column_map = [
        'category_name' => 'c.name',
        'model' => 'm.brand ' . $sort_order . ', m.model',
        'status' => 'm.status'
    ];
    $order_by_sql = $column_map[$sort_column] . " " . $sort_order;
}

// --- Début de la logique de Pagination ---
$per_page = 10; // ✅ Affiche bien 10 matériels par page
$page = (int)($_GET['page'] ?? 1);
$offset = ($page - 1) * $per_page;
$q = trim($_GET['q'] ?? '');

// --- Construction de la requête ---
$base_sql = "
    FROM materials m
    LEFT JOIN categories c ON m.category_id = c.id
    LEFT JOIN assignments a ON a.material_id = m.id AND a.returned_at IS NULL
    LEFT JOIN agents ag ON ag.id = a.agent_id
    WHERE m.deleted_at IS NULL
";
$where_sql = "";
$params = [];

if ($q !== '') { // ✅ La recherche fonctionne toujours
    $where_sql = " AND (m.asset_tag LIKE ? OR c.name LIKE ? OR m.brand LIKE ? OR m.model LIKE ? OR m.serial_number LIKE ?)";
    $like_q = '%' . $q . '%';
    $params = [$like_q, $like_q, $like_q, $like_q, $like_q];
}

// 1. Compter le nombre total de résultats
$count_stmt = $pdo->prepare("SELECT COUNT(m.id)" . $base_sql . $where_sql);
$count_stmt->execute($params);
$total_results = $count_stmt->fetchColumn();
$total_pages = ceil($total_results / $per_page);

// 2. Récupérer les résultats pour la page actuelle
$select_sql = "SELECT m.id, m.asset_tag, c.name AS category_name, m.brand, m.model, m.serial_number, m.status, a.id AS assignment_id, ag.id AS agent_id, ag.first_name, ag.last_name, CONCAT(ag.last_name, ' ', ag.first_name) as agent_name";
$main_sql = $select_sql . $base_sql . $where_sql . " ORDER BY " . $order_by_sql . " LIMIT ? OFFSET ?";
$main_stmt = $pdo->prepare($main_sql);

$param_index = 1;
foreach ($params as $value) {
    $main_stmt->bindValue($param_index, $value);
    $param_index++;
}
$main_stmt->bindValue($param_index, $per_page, PDO::PARAM_INT);
$main_stmt->bindValue($param_index + 1, $offset, PDO::PARAM_INT);

$main_stmt->execute();
$materials = $main_stmt->fetchAll();

// ---- Fonction de tri ----
function sort_link($column, $text, $current_column, $current_order) {
    $order = ($current_column === $column && $current_order === 'asc') ? 'desc' : 'asc';
    $arrow = '';
    if ($current_column === $column) {
        $arrow = ($current_order === 'asc') ? ' ▲' : ' ▼';
    }
    // On préserve les autres paramètres de l'URL (recherche, page, etc.)
    $query_params = http_build_query(array_merge($_GET, ['sort' => $column, 'order' => $order]));
    return '<a href="?' . $query_params . '">' . $text . $arrow . '</a>';
}

include 'header.php';

?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Liste du Matériels</h2>
        <a href="index.php" class="btn btn-primary">Tabeeau de bord</a>
</div>
<?php
// Vérifie s'il y a un message d'erreur en session
if (isset($_SESSION['error_message'])) {
    // Si oui, l'affiche dans une alerte
    echo '<p class="alert alert-error">' . e($_SESSION['error_message']) . '</p>';
    // Important : Supprime le message pour qu'il ne s'affiche qu'une fois
    unset($_SESSION['error_message']);
}
// Fait la même chose pour les messages de succès
if (isset($_SESSION['success_message'])) {
    echo '<p class="alert alert-success">' . e($_SESSION['success_message']) . '</p>';
    unset($_SESSION['success_message']);
}
?>
<div class="form-container" style="margin-bottom: 1.5rem;">
    <form method="get">
      <input name="q" placeholder="Rechercher..." type="text" value="<?= e($q) ?>" style="min-width: 300px;">
      <button type="submit" class="btn btn-primary">Rechercher</button>
      <a href="view_materials.php" class="btn btn-secondary">Réinitialiser</a>
      <a href="export_materials.php?<?= http_build_query($_GET) ?>" class="btn btn-secondary" target="_blank">Exporter CSV</a>
      <!--<a href="export_materials.php" class="btn btn-secondary" target="_blank">Exporter CSV</a>-->
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Étiquette</th>
                <th><?= sort_link('category_name', 'Type', $sort_column, $sort_order) ?></th>
                <th><?= sort_link('model', 'Modèle', $sort_column, $sort_order) ?></th>
                <th><?= sort_link('status', 'Statut', $sort_column, $sort_order) ?></th>
                <th><?= sort_link('agent_name', 'Agent (si attribué)', $sort_column, $sort_order) ?></th>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($materials)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 2rem;">Aucun matériel trouvé.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($materials as $m): ?>
                    <tr>
                        <td>
                            <a href="view_material_details.php?id=<?= e($m['id']) ?>">
                                <strong><?= e($m['asset_tag'] ?: '#'.$m['id']) ?></strong>
                            </a>
                        </td>
                        <td><?= e($m['category_name']) ?></td>
                        <td><?= e($m['brand'] . ' ' . $m['model']) ?></td>
                        <td><?= e(ucfirst($m['status'])) ?></td>
                        <td>
                            <?php if ($m['status'] === 'assigned' && $m['agent_id']): ?>
                                <a href="view_agent.php?id=<?= e($m['agent_id']) ?>"><?= e($m['last_name'].' '.$m['first_name']) ?></a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <td class="actions-cell">
                                <form method="post" action="delete_material.php" onsubmit="return confirm('Mettre ce matériel à la corbeille ?')">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="material_id" value="<?= e($m['id']) ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Supprimer</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="pagination">
    <?php if ($total_pages > 1): ?>
        <?php
        // On garde les paramètres de tri et de recherche dans les liens de pagination
        $query_params = $_GET;
        ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php $query_params['page'] = $i; ?>
            <a href="?<?= http_build_query($query_params) ?>" class="<?= ($i == $page) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>