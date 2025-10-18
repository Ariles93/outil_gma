<?php
require_once 'db.php';
require_once 'protect.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}

$material_id = (int)($_GET['id'] ?? 0);
if ($material_id <= 0) die('Matériel invalide.');

$error = '';
$success = false;

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF invalide');

    $category_id = (int)($_POST['category_id'] ?? 0);
    if ($category_id <= 0) {
        $error = 'Veuillez sélectionner un type de matériel.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE materials SET
                    category_id = ?,
                    asset_tag = ?,
                    brand = ?,
                    model = ?,
                    serial_number = ?,
                    purchase_date = ?,
                    warranty_end = ?,
                    notes = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $category_id,
                $_POST['asset_tag'] ?: null,
                $_POST['brand'] ?: null,
                $_POST['model'] ?: null,
                $_POST['serial_number'] ?: null,
                $_POST['purchase_date'] ?: null,
                $_POST['warranty_end'] ?: null,
                $_POST['notes'] ?: null,
                $material_id
            ]);
            $success = true;
        } catch (PDOException $e) {
            $error = 'Erreur base de données : ' . e($e->getMessage());
        }
    }
}

// Récupérer les informations actuelles du matériel pour pré-remplir le formulaire
$stmt = $pdo->prepare("SELECT * FROM materials WHERE id = ?");
$stmt->execute([$material_id]);
$material = $stmt->fetch();
if (!$material) die('Matériel introuvable.');

// Récupérer toutes les catégories pour le menu déroulant
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

include 'header.php';
?>

<h2>Modifier le matériel</h2>

<div class="form-container">
    <?php if ($success): ?>
        <p class="alert alert-success">Matériel mis à jour avec succès ! <a href="view_material_details.php?id=<?= e($material_id) ?>">Retour aux détails</a></p>
    <?php elseif (!empty($error)): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label for="category_id">Type de matériel:</label>
        <select id="category_id" name="category_id" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['id']) ?>" <?= ($material['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label for="asset_tag">Étiquette (asset tag):</label>
        <input id="asset_tag" name="asset_tag" type="text" value="<?= e($material['asset_tag']) ?>">
        
        <label for="brand">Marque:</label>
        <input id="brand" name="brand" type="text" value="<?= e($material['brand']) ?>">
        
        <label for="model">Modèle:</label>
        <input id="model" name="model" type="text" value="<?= e($material['model']) ?>">
        
        <label for="serial_number">Numéro de série:</label>
        <input id="serial_number" name="serial_number" type="text" value="<?= e($material['serial_number']) ?>">
        
        <label for="purchase_date">Date d'achat:</label>
        <input id="purchase_date" name="purchase_date" type="date" value="<?= e($material['purchase_date']) ?>">
        
        <label for="warranty_end">Fin garantie:</label>
        <input id="warranty_end" name="warranty_end" type="date" value="<?= e($material['warranty_end']) ?>">
        
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"><?= e($material['notes']) ?></textarea>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php include 'footer.php'; ?>
