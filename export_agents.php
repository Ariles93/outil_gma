<?php
require_once 'db.php';
require_once 'protect.php';

// Only admins or managers can export
$allowed_roles = ['admin', 'gestionnaire'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    die("Accès refusé.");
}

try {
    // Fetch all agents
    $stmt = $pdo->query("
        SELECT
            first_name, last_name, email, phone, department, position, employee_id, notes, created_at
        FROM agents
        ORDER BY last_name, first_name
    ");
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    $filename = "export_agents_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM

    // Add header row
    fputcsv($output, [
        'Prenom', 'Nom', 'Email', 'Telephone', 'Departement', 'Poste', 'ID Employe', 'Notes', 'Date Creation'
    ], ';');

    // Add data rows
    foreach ($agents as $agent) {
        fputcsv($output, [
            $agent['first_name'],
            $agent['last_name'],
            $agent['email'],
            $agent['phone'],
            $agent['department'],
            $agent['position'],
            $agent['employee_id'],
            $agent['notes'],
            $agent['created_at']
        ], ';');
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de l'exportation des agents: " . e($e->getMessage());
    header('Location: search.php'); // Redirect back with error
    exit;
}
?>