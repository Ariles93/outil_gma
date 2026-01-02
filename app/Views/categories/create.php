<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Ajouter une catégorie</h2>
    <a href="<?= url('categories') ?>" class="btn btn-secondary">&larr; Retour</a>
</div>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('categories/store') ?>" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="name">Nom de la catégorie <span style="color: var(--color-primary);">*</span></label>
            <input type="text" id="name" name="name" required placeholder="Ex: Ordinateurs portables">
        </div>

        <div class="d-flex justify-between items-center mt-6">
            <button type="submit" class="btn btn-primary">Ajouter</button>
            <a href="<?= url('categories') ?>" class="text-muted">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>