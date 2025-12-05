<?php include __DIR__ . '/../partials/header.php'; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error"><?= e($_SESSION['error_message']) ?></div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success_message']) ?></div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<h2>Corbeille</h2>

<div class="content-box">
    <?php if (empty($materials)): ?>
        <p>La corbeille est vide.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Matériel</th>
                    <th>N° Série</th>
                    <th>Date de suppression</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materials as $material): ?>
                    <tr>
                        <td>
                            <strong><?= e($material['category_name']) ?></strong><br>
                            <?= e($material['brand']) ?>         <?= e($material['model']) ?>
                        </td>
                        <td><?= e($material['serial_number']) ?></td>
                        <td><?= e((new DateTime($material['deleted_at']))->format('d/m/Y H:i')) ?></td>
                        <td>
                            <form method="post" action="<?= url('trash/restore') ?>" style="display:inline;">
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= e($material['id']) ?>">
                                <button type="submit" class="btn btn-secondary btn-sm">Restaurer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>