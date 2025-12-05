<?php include __DIR__ . '/../partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Liste du Matériel</h2>
    <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
        <a href="<?= url('materials/create') ?>" class="btn btn-primary">Ajouter un matériel</a>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error"><?= e($_SESSION['error_message']) ?></div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success_message']) ?></div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="form-container" style="margin-bottom: 1.5rem;">
    <form method="get" action="<?= url('materials') ?>">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <input name="q" placeholder="Rechercher..." type="text" value="<?= e($search ?? '') ?>"
                style="min-width: 300px; flex-grow: 1;">
            <button type="submit" class="btn btn-primary">Rechercher</button>
            <a href="<?= url('materials') ?>" class="btn btn-secondary">Réinitialiser</a>
            <a href="<?= url('exports/materials') ?>?<?= http_build_query($_GET) ?>" class="btn btn-secondary"
                target="_blank">Exporter CSV</a>
        </div>
    </form>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>
                    <a
                        href="<?= url('materials') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'id', 'order' => ($sortColumn === 'id' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">
                        Étiquette <?= $sortColumn === 'id' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>
                    <a
                        href="<?= url('materials') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'category_name', 'order' => ($sortColumn === 'category_name' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">
                        Type <?= $sortColumn === 'category_name' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>
                    <a
                        href="<?= url('materials') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'model', 'order' => ($sortColumn === 'model' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">
                        Modèle <?= $sortColumn === 'model' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>
                    <a
                        href="<?= url('materials') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'status', 'order' => ($sortColumn === 'status' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">
                        Statut <?= $sortColumn === 'status' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>
                    <a
                        href="<?= url('materials') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'agent_name', 'order' => ($sortColumn === 'agent_name' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">
                        Agent (si attribué)
                        <?= $sortColumn === 'agent_name' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($materials)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 2rem;">Aucun matériel trouvé.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($materials as $material): ?>
                    <tr>
                        <td>
                            <a href="<?= url('materials/view?id=' . $material['id']) ?>">
                                <strong><?= e($material['asset_tag'] ?: '#' . $material['id']) ?></strong>
                            </a>
                        </td>
                        <td><?= e($material['category_name']) ?></td>
                        <td><?= e($material['brand'] . ' ' . $material['model']) ?></td>
                        <td>
                            <?php
                            $statusLabels = [
                                'available' => '<span class="badge badge-success">Disponible</span>',
                                'assigned' => '<span class="badge badge-danger">Attribué</span>',
                                'maintenance' => '<span class="badge badge-warning">En maintenance</span>',
                                'broken' => '<span class="badge badge-danger">Hors service</span>',
                            ];
                            echo $statusLabels[$material['status']] ?? $material['status'];
                            ?>
                        </td>
                        <td>
                            <?php if ($material['status'] === 'assigned' && $material['agent_id']): ?>
                                <a
                                    href="<?= url('agents/view?id=' . $material['agent_id']) ?>"><?= e($material['last_name'] . ' ' . $material['first_name']) ?></a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
                                <a href="<?= url('materials/edit?id=' . $material['id']) ?>"
                                    class="btn btn-sm btn-primary">Modifier</a>
                            <?php endif; ?>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <form method="post" action="<?= url('materials/delete') ?>" style="display:inline;"
                                    onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce matériel ?');">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= e($material['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
        <div class="pagination">
            <?php
            $queryParams = $_GET;
            ?>
            <?php if ($pagination['current_page'] > 1): ?>
                <?php $queryParams['page'] = $pagination['current_page'] - 1; ?>
                <a href="<?= url('materials') ?>?<?= http_build_query($queryParams) ?>">&laquo; Précédent</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                <?php $queryParams['page'] = $i; ?>
                <a href="<?= url('materials') ?>?<?= http_build_query($queryParams) ?>"
                    class="<?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                <?php $queryParams['page'] = $pagination['current_page'] + 1; ?>
                <a href="<?= url('materials') ?>?<?= http_build_query($queryParams) ?>">Suivant &raquo;</a>
            <?php endif; ?>
        </div><br />
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>