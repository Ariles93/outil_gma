<?php include __DIR__ . '/../partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Gestion des Catégories</h2>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="<?= url('categories/create') ?>" class="btn btn-primary">Ajouter une catégorie</a>
    <?php endif; ?>
</div>

<div class="content-box">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= e($category['name']) ?></td>
                    <td>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="<?= url('categories/edit?id=' . $category['id']) ?>"
                                class="btn btn-sm btn-primary">Modifier</a>
                            <form method="post" action="<?= url('categories/delete') ?>" style="display:inline;"
                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>