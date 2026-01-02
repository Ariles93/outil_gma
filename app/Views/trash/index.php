<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Corbeille</h2>
    <a href="<?= url('materials') ?>" class="btn btn-secondary">&larr; Retour Matériel</a>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Matériel</th>
                    <th>N° Série</th>
                    <th>Supprimé le</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($materials)): ?>
                    <tr>
                        <td colspan="4" class="text-center p-4 text-muted">
                            <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🗑️</div>
                            La corbeille est vide.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($materials as $material): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 500;"><?= e($material['category_name']) ?></div>
                                <div class="text-sm text-muted"><?= e($material['brand'] . ' ' . $material['model']) ?></div>
                            </td>
                            <td class="text-sm"><?= e($material['serial_number']) ?></td>
                            <td class="text-sm text-muted">
                                <?= e((new DateTime($material['deleted_at']))->format('d/m/Y H:i')) ?></td>
                            <td class="text-right">
                                <form method="post" action="<?= url('trash/restore') ?>" style="display:inline;">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= e($material['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-secondary" title="Restaurer">Restaurer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>