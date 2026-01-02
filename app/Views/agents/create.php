<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Ajouter un agent</h2>
    <a href="<?= url('agents') ?>" class="btn btn-secondary">&larr; Retour</a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <?php if (!empty($error)): ?>
        <div class="alert badge-danger mb-4"
            style="display: block; width: 100%; border-radius: var(--radius-md); padding: 1rem; border: 1px solid currentColor; color: #991B1B; background-color: #FEF2F2;">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= url('agents/create') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="dashboard-grid"
            style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 0;">
            <!-- Column 1 -->
            <div>
                <div class="form-group">
                    <label for="first_name">Prénom <span style="color: red;">*</span></label>
                    <input id="first_name" name="first_name" type="text" required
                        value="<?= e($agent['first_name'] ?? '') ?>" placeholder="Ex: Jean">
                </div>

                <div class="form-group">
                    <label for="last_name">Nom <span style="color: red;">*</span></label>
                    <input id="last_name" name="last_name" type="text" required
                        value="<?= e($agent['last_name'] ?? '') ?>" placeholder="Ex: Dupont">
                </div>

                <div class="form-group">
                    <label for="email">Email <span style="color: red;">*</span></label>
                    <input id="email" name="email" type="email" required value="<?= e($agent['email'] ?? '') ?>"
                        placeholder="jean.dupont@entreprise.com">
                </div>
            </div>

            <!-- Column 2 -->
            <div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input id="phone" name="phone" type="tel" value="<?= e($agent['phone'] ?? '') ?>"
                        placeholder="0123456789">
                </div>

                <div class="form-group">
                    <label for="department">Service <span style="color: red;">*</span></label>
                    <input id="department" name="department" type="text" required
                        value="<?= e($agent['department'] ?? '') ?>" placeholder="Ex: DSI">
                </div>

                <div class="form-group">
                    <label for="position">Poste <span style="color: red;">*</span></label>
                    <input id="position" name="position" type="text" required value="<?= e($agent['position'] ?? '') ?>"
                        placeholder="Ex: Technicien Support">
                </div>

                <div class="form-group">
                    <label for="employee_id">ID employé</label>
                    <input id="employee_id" name="employee_id" type="number"
                        value="<?= e($agent['employee_id'] ?? '') ?>" placeholder="12345">
                </div>
            </div>
        </div>

        <div class="form-group mt-4">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3"
                placeholder="Lieu d'affectation, bureau ... etc"><?= e($agent['notes'] ?? '') ?></textarea>
        </div>

        <div class="d-flex justify-between items-center mt-6">
            <button type="submit" class="btn btn-primary">Créer l'agent</button>
            <a href="<?= url('agents') ?>" class="text-muted">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>