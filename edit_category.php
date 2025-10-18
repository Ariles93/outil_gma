<?php
require_once 'db.php';
require_once 'protect.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}

$category_id = (int)($_GET['id'] ?? 0);
if ($category_id <= 0) die('Catégorie invalide.');

$error = '';
$success = false;

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF invalide');
    
    $name = trim($_POST['name'] ?? '');
    if (!empty($name)) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $category_id]);
        $success = true;
    } else {
        $error = "Le nom ne peut pas être vide.";
    }
}

// Récupérer les infos de la catégorie
$stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();
if (!$category) die('Catégorie introuvable.');

include 'header.php';
?>

<h2>Modifier la catégorie</h2>

<div class="form-container" style="max-width: 600px; margin: 0 auto;">
    <?php if ($success): ?>
        <p class="alert alert-success">Catégorie mise à jour ! <a href="manage_categories.php">Retour à la liste</a></p>
    <?php elseif ($error): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label for="name">Nom de la catégorie:</label>
        <input id="name" type="text" name="name" required value="<?= e($category['name']) ?>">
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php include 'footer.php'; ?>
