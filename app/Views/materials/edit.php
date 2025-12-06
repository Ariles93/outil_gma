<?php include __DIR__ . '/../partials/header.php'; ?>

<h2>Modifier le matériel</h2>

<div class="form-container">
    <?php if (!empty($error)): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= url('materials/edit?id=' . $material['id']) ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label for="category_id">Catégorie:</label>
        <select id="category_id" name="category_id" required>
            <option value="">Sélectionner une catégorie</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category['id']) ?>" <?= $material['category_id'] == $category['id'] ? 'selected' : '' ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="brand">Marque:</label>
        <input id="brand" name="brand" type="text" required value="<?= e($material['brand']) ?>"><br><br>

        <label for="model">Modèle:</label>
        <input id="model" name="model" type="text" required value="<?= e($material['model']) ?>"><br><br>

        <label for="serial_number">Numéro de série:</label>
        <input id="serial_number" name="serial_number" type="text" value="<?= e($material['serial_number']) ?>"><br><br>

        <input id="inventory_number" name="inventory_number" type="text"
            value="<?= e($material['inventory_number']) ?>"><br><br>

        <label for="asset_tag">Etiquette (Asset Tag):</label>
        <input id="asset_tag" name="asset_tag" type="text" value="<?= e($material['asset_tag'] ?? '') ?>"><br><br>

        <label for="purchase_date">Date d'achat:</label>
        <input id="purchase_date" name="purchase_date" type="date" value="<?= e($material['purchase_date']) ?>"><br><br>

        <label for="warranty_expiry">Garantie jusqu'au:</label>
        <input id="warranty_expiry" name="warranty_expiry" type="date"
            value="<?= e($material['warranty_expiry']) ?>"><br><br>

        <label for="cost">Coût (€):</label>
        <input id="cost" name="cost" type="number" step="0.01" value="<?= e($material['cost']) ?>"><br><br>

        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"><?= e($material['notes']) ?></textarea>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>