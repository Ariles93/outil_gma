<?php
require_once 'db.php';
require_once 'protect.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Méthode non autorisée');
}

if (!check_csrf($_POST['csrf'] ?? '')) {
    die('CSRF invalide');
}

$category_id = (int)($_POST['category_id'] ?? 0);
if ($category_id <= 0) {
    die('ID de catégorie invalide.');
}

try {
    // ÉTAPE CRUCIALE : Vérifier si la catégorie est utilisée
    $check = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE category_id = ?");
    $check->execute([$category_id]);
    $count = $check->fetchColumn();

    if ($count > 0) {
//  On stocke l'erreur en session et on redirige
        $_SESSION['error_message'] = "Impossible de supprimer cette catégorie car elle est utilisée par " . $count . " matériel(s).";
        header('Location: manage_categories.php');
        exit;
    }

    $delete = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $delete->execute([$category_id]);

    $_SESSION['success_message'] = "La catégorie a été supprimée avec succès.";
    header('Location: manage_categories.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur de base de données : " . e($e->getMessage());
    header('Location: manage_categories.php');
    exit;
}
