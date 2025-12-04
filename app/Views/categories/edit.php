<?php include __DIR__ . '/../partials/header.php'; ?>

<h2>Modifier une catégorie</h2>

<div class="form-container">
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form action="<?= url('categories/update?id=' . $category['id']) ?>" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="name">Nom de la catégorie</label>
            <input type="text" id="name" name="name" value="<?= e($category['name']) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="<?= url('categories') ?>" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>