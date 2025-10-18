<?php
require_once 'db.php';
require_once 'protect.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF invalide');

    $category_id = (int)($_POST['category_id'] ?? 0);
    if ($category_id <= 0) {
        $error = 'Veuillez sélectionner un type de matériel.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO materials
                (category_id, asset_tag, brand, model, serial_number, purchase_date, warranty_end, status, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'available', ?)
            ");
            $stmt->execute([
                $category_id,
                $_POST['asset_tag'] ?: null,
                $_POST['brand'] ?: null,
                $_POST['model'] ?: null,
                $_POST['serial_number'] ?: null,
                $_POST['purchase_date'] ?: null,
                $_POST['warranty_end'] ?: null,
                $_POST['notes'] ?: null
            ]);
            $success = true;
            // --- DÉBUT LOG ---
            $category_name = $pdo->query("SELECT name FROM categories WHERE id = " . $category_id)->fetchColumn();
            $mat_name = $category_name . ' ' . ($_POST['brand'] ?? '');
            log_action($pdo, "Création du matériel \"{$mat_name}\"");
            // --- FIN LOG ---
        } catch (PDOException $e) {
            $error = 'Erreur base de données : ' . e($e->getMessage());
        }
    }
}

include 'header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Ajouter un matériel</h2>
        <a href="manage_categories.php" class="btn btn-primary">+Nouvelle Catégorie</a>
</div>
<div class="form-container">
    <?php if ($success): ?>
        <p class="alert alert-success">Matériel créé avec succès ! <a href="view_materials.php">Retour à la liste</a></p>
    <?php elseif (!empty($error)): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label for="category_id">Type de matériel:</label>
        <select id="category_id" name="category_id" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['id']) ?>" <?= (($category_id ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label for="asset_tag">Étiquette (asset tag):</label>
        <input id="asset_tag" name="asset_tag" type="text" value="<?= e($_POST['asset_tag'] ?? '') ?>" required>
        
        <label for="brand">Marque:</label>
        <input id="brand" name="brand" type="text" value="<?= e($_POST['brand'] ?? '') ?>" required>
        
        <label for="model">Modèle:</label>
        <input id="model" name="model" type="text" value="<?= e($_POST['model'] ?? '') ?>" required>
        
        <label for="serial_number">Numéro de série:</label>
        <input id="serial_number" name="serial_number" type="text" value="<?= e($_POST['serial_number'] ?? '') ?>" required>
        
        <label for="purchase_date">Date d'achat:</label>
        <input id="purchase_date" name="purchase_date" type="date" value="<?= e($_POST['purchase_date'] ?? '') ?>">
        
        <label for="warranty_end">Fin garantie:</label>
        <input id="warranty_end" name="warranty_end" type="date" value="<?= e($_POST['warranty_end'] ?? '') ?>">
        
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes" placeholder="Ajouter détails sur ce matériel : État, MàJ ..."><?= e($_POST['notes'] ?? '') ?></textarea>

        <button type="submit" class="btn btn-primary">Créer le matériel</button>
    </form>
</div>

<?php include 'footer.php'; ?>
