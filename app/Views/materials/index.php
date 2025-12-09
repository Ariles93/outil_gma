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
    <form method="get" action="<?= url('materials') ?>" id="filter-form">
        <!-- Main Search Row -->
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-bottom: 1rem;">
            <input name="q" placeholder="Rechercher (Tag, Modèle...)" type="text"
                value="<?= e($filters['search'] ?? '') ?>" style="min-width: 300px; flex-grow: 1; padding: 0.5rem;">

            <button type="submit" class="btn btn-primary">Rechercher / Filtrer</button>
            <a href="<?= url('materials') ?>" class="btn btn-secondary">Réinitialiser</a>
            <a href="<?= url('exports/materials') ?>?<?= http_build_query($_GET) ?>" class="btn btn-secondary"
                target="_blank">Exporter CSV</a>
        </div>

        <!-- Advanced Filters Row -->
        <details>
            <summary style="cursor: pointer; margin-bottom: 1rem; color: var(--primary-color);">Filtres Avancés
            </summary>
            <div
                style="display: flex; gap: 1rem; flex-wrap: wrap; padding: 1rem; background: rgba(255,255,255,0.5); border-radius: 8px;">

                <!-- Category Filter -->
                <div style="display: flex; flex-direction: column;">
                    <label for="category_id" style="font-size: 0.8rem; margin-bottom: 4px;">Catégorie</label>
                    <select name="category_id" id="category_id" style="padding: 0.4rem;">
                        <option value="">Toutes</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($filters['category_id']) && $filters['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div style="display: flex; flex-direction: column;">
                    <label for="status" style="font-size: 0.8rem; margin-bottom: 4px;">Statut</label>
                    <select name="status" id="status" style="padding: 0.4rem;">
                        <option value="">Tous</option>
                        <option value="available" <?= (isset($filters['status']) && $filters['status'] === 'available') ? 'selected' : '' ?>>Disponible</option>
                        <option value="assigned" <?= (isset($filters['status']) && $filters['status'] === 'assigned') ? 'selected' : '' ?>>Attribué</option>
                        <option value="maintenance" <?= (isset($filters['status']) && $filters['status'] === 'maintenance') ? 'selected' : '' ?>>En maintenance</option>
                        <option value="broken" <?= (isset($filters['status']) && $filters['status'] === 'broken') ? 'selected' : '' ?>>Hors service</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div style="display: flex; flex-direction: column;">
                    <label for="date_min" style="font-size: 0.8rem; margin-bottom: 4px;">Acheté après le</label>
                    <input type="date" name="date_min" id="date_min" value="<?= e($filters['date_min'] ?? '') ?>">
                </div>

                <div style="display: flex; flex-direction: column;">
                    <label for="date_max" style="font-size: 0.8rem; margin-bottom: 4px;">Acheté avant le</label>
                    <input type="date" name="date_max" id="date_max" value="<?= e($filters['date_max'] ?? '') ?>">
                </div>

            </div>
        </details>
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
        <tbody id="materials-table-body" hx-get="<?= url('materials') ?>?<?= http_build_query($_GET) ?>"
            hx-trigger="every 10s" hx-select="#materials-table-body" hx-swap="outerHTML">
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