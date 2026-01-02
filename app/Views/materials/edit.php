<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Modifier le matériel</h2>
    <a href="<?= url('materials/view?id=' . $material['id']) ?>" class="btn btn-secondary">&larr; Retour</a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= url('materials/edit?id=' . $material['id']) ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="dashboard-grid"
            style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 0;">
            <!-- Column 1 -->
            <div>
                <div class="form-group">
                    <label for="category_id">Catégorie <span style="color: var(--color-danger);">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Choisir une catégorie --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category['id']) ?>" <?= $material['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="brand">Marque <span style="color: var(--color-danger);">*</span></label>
                    <input id="brand" name="brand" type="text" required value="<?= e($material['brand']) ?>">
                </div>

                <div class="form-group">
                    <label for="model">Modèle <span style="color: var(--color-danger);">*</span></label>
                    <input id="model" name="model" type="text" required value="<?= e($material['model']) ?>">
                </div>

                <div class="form-group">
                    <label for="asset_tag">Etiquette (Asset Tag)</label>
                    <input id="asset_tag" name="asset_tag" type="text" value="<?= e($material['asset_tag'] ?? '') ?>">
                </div>
            </div>

            <!-- Column 2 -->
            <div>
                <div class="form-group">
                    <label for="serial_number">Numéro de série</label>
                    <input id="serial_number" name="serial_number" type="text"
                        value="<?= e($material['serial_number']) ?>">
                </div>

                <div class="form-group">
                    <label for="inventory_number">Numéro d'inventaire</label>
                    <input id="inventory_number" name="inventory_number" type="text"
                        value="<?= e($material['inventory_number']) ?>">
                </div>

                <div class="d-flex gap-4">
                    <div class="form-group w-full">
                        <label for="purchase_date">Date d'achat</label>
                        <input id="purchase_date" name="purchase_date" type="date"
                            value="<?= e($material['purchase_date']) ?>">
                    </div>
                    <div class="form-group w-full">
                        <label for="warranty_expiry">Fin de garantie</label>
                        <input id="warranty_expiry" name="warranty_expiry" type="date"
                            value="<?= e($material['warranty_expiry']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="cost">Coût (€)</label>
                    <input id="cost" name="cost" type="number" step="0.01" value="<?= e($material['cost']) ?>">
                </div>
            </div>
        </div>

        <div class="form-group mt-4">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4"><?= e($material['notes']) ?></textarea>
        </div>

        <div class="d-flex justify-between items-center mt-4">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="<?= url('materials/view?id=' . $material['id']) ?>" class="text-muted"
                style="font-size: 0.9rem;">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>