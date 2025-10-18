<?php
require_once 'db.php';
require_once 'protect.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}

$error = '';
$success = '';

// Traitement de l'ajout d'une nouvelle catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF invalide');
    
    $name = trim($_POST['name'] ?? '');
    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            $success = "La catégorie a été ajoutée avec succès.";
        } catch (PDOException $e) {
            // Gérer le cas où la catégorie existe déjà (contrainte UNIQUE)
            if ($e->getCode() == 23000) {
                $error = "Cette catégorie existe déjà.";
            } else {
                $error = "Erreur de base de données.";
            }
        }
    } else {
        $error = "Le nom de la catégorie ne peut pas être vide.";
    }
}

// Récupérer toutes les catégories
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

include 'header.php';
?>

<h2>Gestion des catégories</h2>
<?php
if (isset($_SESSION['error_message'])) {
    echo '<p class="alert alert-error">' . e($_SESSION['error_message']) . '</p>';
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    echo '<p class="alert alert-success">' . e($_SESSION['success_message']) . '</p>';
    unset($_SESSION['success_message']);
}
?>
<div class="dashboard-grid-secondary">
    <div class="form-container">
        <h3>Ajouter une catégorie</h3>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <label for="name">Nom de la nouvelle catégorie:</label>
            <input id="name" name="name" type="text" required>
            <button type="submit" name="add_category" class="btn btn-primary">Ajouter</button>
        </form>
        <?php if ($success): ?><p class="alert alert-success" style="margin-top: 1rem;"><?= e($success) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="alert alert-error" style="margin-top: 1rem;"><?= e($error) ?></p><?php endif; ?>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nom de la catégorie</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="2" style="text-align:center;">Aucune catégorie trouvée.</td></tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= e($category['name']) ?></td>
                            <td>
                                <a href="edit_category.php?id=<?= e($category['id']) ?>" class="btn btn-secondary" style="text-decoration: none; padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-right: 5px;">Modifier</a>
                                <form method="post" action="delete_category.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')" style="display: inline;">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="category_id" value="<?= e($category['id']) ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php include 'footer.php'; ?>
