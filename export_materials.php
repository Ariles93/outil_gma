<?php
require_once 'db.php';
require_once 'protect.php';

// Only admins or managers can export
$allowed_roles = ['admin', 'gestionnaire'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    die("Accès refusé.");
}

// --- Récupération des paramètres de tri et recherche ---
$allowed_columns = ['category_name', 'brand', 'model', 'status', 'agent_name'];
$sort_column = $_GET['sort'] ?? 'category_name';
if (!in_array($sort_column, $allowed_columns)) $sort_column = 'category_name';

$allowed_orders = ['asc', 'desc'];
$sort_order = strtolower($_GET['order'] ?? 'asc');
if (!in_array($sort_order, $allowed_orders)) $sort_order = 'asc';

$q = trim($_GET['q'] ?? '');

// --- Construction de la requête ---
$order_by_sql = "";
if ($sort_column === 'agent_name') {
    $order_by_sql = "ag.last_name $sort_order, ag.first_name $sort_order";
} elseif ($sort_column === 'brand') {
    $order_by_sql = "m.brand $sort_order, m.model";
} else {
    $column_map = [
        'category_name' => 'c.name',
        'model' => 'm.model',
        'status' => 'm.status'
    ];
    $order_by_sql = $column_map[$sort_column] . " $sort_order";
}

$where_sql = "";
$params = [];

if ($q !== '') {
    $where_sql = " AND (m.asset_tag LIKE ? OR c.name LIKE ? OR m.brand LIKE ? OR m.model LIKE ? OR m.serial_number LIKE ?)";
    $like_q = '%' . $q . '%';
    $params = [$like_q, $like_q, $like_q, $like_q, $like_q];
}

$sql = "
    SELECT
        m.asset_tag,
        c.name AS category_name,
        m.brand,
        m.model,
        m.serial_number,
        m.purchase_date,
        m.warranty_end,
        m.status,
        m.notes,
        ag.first_name,
        ag.last_name,
        CONCAT(ag.last_name, ' ', ag.first_name) AS agent_name
    FROM materials m
    LEFT JOIN categories c ON m.category_id = c.id
    LEFT JOIN assignments a ON a.material_id = m.id AND a.returned_at IS NULL
    LEFT JOIN agents ag ON ag.id = a.agent_id
    WHERE m.deleted_at IS NULL
    $where_sql
    ORDER BY $order_by_sql
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Préparer le CSV ---
    $filename = "export_materiels_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for Excel
    fputs($output, "\xEF\xBB\xBF");

    // Header row
    fputcsv($output, [
        'Etiquette', 'Type', 'Marque', 'Modele', 'Numero Serie',
        'Date Achat', 'Fin Garantie', 'Statut', 'Notes',
        'Agent Prenom', 'Agent Nom'
    ], ';');

    // Data rows
    foreach ($materials as $m) {
        fputcsv($output, [
            $m['asset_tag'],
            $m['category_name'],
            $m['brand'],
            $m['model'],
            $m['serial_number'],
            $m['purchase_date'],
            $m['warranty_end'],
            ucfirst($m['status']),
            $m['notes'],
            $m['first_name'] ?: '',
            $m['last_name'] ?: ''
        ], ';');
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de l'exportation des matériels: " . e($e->getMessage());
    header('Location: view_materials.php');
    exit;
}