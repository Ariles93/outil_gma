<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Gestion des Catégories</h2>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="<?= url('categories/create') ?>" class="btn btn-primary">
            + Ajouter une catégorie
        </a>
    <?php endif; ?>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td style="font-weight: 500;"><?= e($category['name']) ?></td>
                        <td class="text-right">
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <a href="<?= url('categories/edit?id=' . $category['id']) ?>" class="btn btn-sm btn-secondary"
                                    style="margin-right: 0.5rem;">Modifier</a>
                                <form method="post" action="<?= url('categories/delete') ?>" style="display:inline;"
                                    onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted text-sm">Lecture seule</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>