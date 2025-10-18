<?php
require_once 'db.php';
require_once 'protect.php';

/*if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}*/

$stmt = $pdo->query("
    SELECT m.id, m.asset_tag, c.name as category_name, m.brand, m.model, m.deleted_at
    FROM materials m
    LEFT JOIN categories c ON m.category_id = c.id
    WHERE m.deleted_at IS NOT NULL
    ORDER BY m.deleted_at DESC
");
$materials = $stmt->fetchAll();

include 'header.php';
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Corbeille</h2>
        <a href="view_materials.php" class="btn btn-primary">Retour à la liste</a>
</div>
<?php
if (isset($_SESSION['success_message'])) {
    echo '<p class="alert alert-success">' . e($_SESSION['success_message']) . '</p>';
    unset($_SESSION['success_message']);
}
?>
<p style="color: var(--color-text-secondary); margin-top: -1rem; margin-bottom: 1.5rem;">Voici les matériels qui ont été supprimés. Seul un administrateur peut les restaurer.</p>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Étiquette</th>
                <th>Type</th>
                <th>Modèle</th>
                <th>Supprimé le</th>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <th>Action</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($materials)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 2rem;">La corbeille est vide.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($materials as $m): ?>
                    <tr>
                        <td><?= e($m['asset_tag'] ?: '#'.$m['id']) ?></td>
                        <td><?= e($m['category_name']) ?></td>
                        <td><?= e($m['brand'] . ' ' . $m['model']) ?></td>
                        <td><?= e( (new DateTime($m['deleted_at']))->format('d/m/Y H:i') ) ?></td>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <td>
                                <form method="post" action="restore_material.php">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="material_id" value="<?= e($m['id']) ?>">
                                    <button type="submit" class="btn btn-success" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Restaurer</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
