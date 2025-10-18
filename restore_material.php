<?php
require_once 'db.php';
require_once 'protect.php';

// VÉRIFICATION DU RÔLE
if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé. Seul un administrateur peut effectuer cette action.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Méthode non autorisée');
}

if (!check_csrf($_POST['csrf'] ?? '')) {
    die('CSRF token invalide');
}

$material_id = (int)($_POST['material_id'] ?? 0);
if ($material_id <= 0) {
    die('ID de matériel invalide');
}

try {
    // On remet le champ deleted_at à NULL
    $stmt = $pdo->prepare("UPDATE materials SET deleted_at = NULL WHERE id = ?");
    $stmt->execute([$material_id]);

    // --- DÉBUT LOG ---
    //$mat_name = $material['name'] . ' ' . $material['brand'];
    //log_action($pdo, "Restauration du matériel \"{$mat_name}\" depuis la corbeille");
    // --- FIN LOG ---

    $_SESSION['success_message'] = "Le matériel a été restauré avec succès.";
    header('Location: trash.php?status=restored');
    exit;

} catch (PDOException $e) {
    die('Erreur base de données : ' . e($e->getMessage()));
}
?>
