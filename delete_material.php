<?php
require_once 'db.php';
require_once 'protect.php';

// VÉRIFICATION DU RÔLE
if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé. Vous n'avez pas les permissions d'administrateur.");
}

// 1. Vérifier que la méthode est bien POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Méthode non autorisée');
}

// 2. Vérifier le token CSRF
if (!check_csrf($_POST['csrf'] ?? '')) {
    die('Token CSRF invalide');
}

// 3. Récupérer et valider l'ID du matériel
$material_id = (int)($_POST['material_id'] ?? 0);
if ($material_id <= 0) {
    die('ID de matériel invalide');
}

try {
    // 4. Sécurité : vérifier que le matériel n'est pas actuellement attribué
    $stmt = $pdo->prepare("SELECT status FROM materials WHERE id = ?");
    $stmt->execute([$material_id]);
    $material = $stmt->fetch();

    if ($material && $material['status'] === 'assigned') {
// MODIFICATION : On stocke l'erreur en session au lieu de 'die()'
        $_SESSION['error_message'] = 'Impossible de mettre à la corbeille un matériel déjà attribué.';
        header('Location: view_materials.php');
        exit;
    }

    $updateStmt = $pdo->prepare("UPDATE materials SET deleted_at = NOW() WHERE id = ?");
    $updateStmt->execute([$material_id]);
    
    // --- DÉBUT LOG ---
    //$mat_name = $material['name'] . ' ' . $material['brand'];
    //log_action($pdo, "Mise à la corbeille du matériel \"{$mat_name}\"");
    // --- FIN LOG ---

    // On peut aussi mettre un message de succès
    $_SESSION['success_message'] = 'Le matériel a bien été mis à la corbeille.';
    header('Location: view_materials.php');
    exit;


} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erreur base de données : ' . e($e->getMessage());
    header('Location: view_materials.php');
    exit;
}
?>
