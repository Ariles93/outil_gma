<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--color-text-main); margin: 0;">Liste du Matériel</h2>
    <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
        <a href="<?= url('materials/create') ?>" class="btn btn-primary">
            + Nouveau
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="badge badge-danger mb-4"
        style="display: block; width: 100%; border-radius: var(--radius-md); padding: 1rem;">
        <?= e($_SESSION['error_message']) ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="badge badge-success mb-4"
        style="display: block; width: 100%; border-radius: var(--radius-md); padding: 1rem;">
        <?= e($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="card">
    <form method="get" action="<?= url('materials') ?>" id="filter-form">
        <div class="d-flex gap-4 items-center mb-4" style="flex-wrap: wrap;">
            <div style="flex-grow: 1; min-width: 250px;">
                <input name="q" placeholder="Rechercher par tag, modèle, marque..." type="search"
                    value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="<?= url('materials') ?>" class="btn btn-secondary">Reset</a>
                <a href="<?= url('exports/materials') ?>?<?= http_build_query($_GET) ?>" class="btn btn-secondary"
                    target="_blank">Export CSV</a>
            </div>
        </div>

        <!-- Collapsible Advanced Filters -->
        <details <?= (isset($filters['category_id']) || isset($filters['status']) || isset($filters['date_min'])) ? 'open' : '' ?>>
            <summary class="text-muted"
                style="cursor: pointer; font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem; user-select: none;">
                Filtres avancés
            </summary>

            <div class="d-flex gap-4"
                style="flex-wrap: wrap; background-color: #F8FAFC; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                    <label for="category_id">Catégorie</label>
                    <select name="category_id" id="category_id">
                        <option value="">Toutes</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($filters['category_id']) && $filters['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                    <label for="status">Statut</label>
                    <select name="status" id="status">
                        <option value="">Tous</option>
                        <option value="available" <?= (isset($filters['status']) && $filters['status'] === 'available') ? 'selected' : '' ?>>Disponible</option>
                        <option value="assigned" <?= (isset($filters['status']) && $filters['status'] === 'assigned') ? 'selected' : '' ?>>Attribué</option>
                        <option value="maintenance" <?= (isset($filters['status']) && $filters['status'] === 'maintenance') ? 'selected' : '' ?>>Maintenance</option>
                        <option value="broken" <?= (isset($filters['status']) && $filters['status'] === 'broken') ? 'selected' : '' ?>>Hors service</option>
                    </select>
                </div>

                <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                    <label for="date_min">Achat (Après)</label>
                    <input type="date" name="date_min" id="date_min" value="<?= e($filters['date_min'] ?? '') ?>">
                </div>

                <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                    <label for="date_max">Achat (Avant)</label>
                    <input type="date" name="date_max" id="date_max" value="<?= e($filters['date_max'] ?? '') ?>">
                </div>
            </div>
        </details>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><a
                            href="<?= url('materials') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'id', 'order' => ($sortColumn === 'id' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">Asset
                            Tag</a></th>
                    <th><a
                            href="<?= url('materials') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'category_name', 'order' => ($sortColumn === 'category_name' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">Catégorie</a>
                    </th>
                    <th><a
                            href="<?= url('materials') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'model', 'order' => ($sortColumn === 'model' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">Modèle</a>
                    </th>
                    <th><a
                            href="<?= url('materials') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'status', 'order' => ($sortColumn === 'status' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">Statut</a>
                    </th>
                    <th><a
                            href="<?= url('materials') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'agent_name', 'order' => ($sortColumn === 'agent_name' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">Assigné
                            à</a></th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="materials-table-body" hx-get="<?= url('materials') ?>?<?= http_build_query($_GET) ?>"
                hx-trigger="every 15s" hx-select="#materials-table-body" hx-swap="outerHTML">
                <?php if (empty($materials)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 3rem;">
                            <div class="text-muted">Aucun matériel trouvé.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($materials as $material): ?>
                        <tr>
                            <td style="font-weight: 600;">
                                <a href="<?= url('materials/view?id=' . $material['id']) ?>"
                                    style="color: var(--color-primary);">
                                    <?= e($material['asset_tag'] ?: '#' . $material['id']) ?>
                                </a>
                            </td>
                            <td><?= e($material['category_name']) ?></td>
                            <td>
                                <div><?= e($material['brand'] . ' ' . $material['model']) ?></div>
                            </td>
                            <td>
                                <?php
                                $statusClass = match ($material['status']) {
                                    'available' => 'badge-success',
                                    'assigned' => 'badge-neutral', /* Neutral for assigned is clearer in enterprise context than danger */
                                    'maintenance' => 'badge-warning',
                                    'broken' => 'badge-danger',
                                    default => 'badge-neutral'
                                };
                                $statusLabel = match ($material['status']) {
                                    'available' => 'Disponible',
                                    'assigned' => 'Attribué',
                                    'maintenance' => 'En maintenance',
                                    'broken' => 'Hors service',
                                    default => $material['status']
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                            </td>
                            <td>
                                <?php if ($material['status'] === 'assigned' && $material['agent_id']): ?>
                                    <a href="<?= url('agents/view?id=' . $material['agent_id']) ?>" style="font-weight: 500;">
                                        <?= e($material['last_name'] . ' ' . $material['first_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <a href="<?= url('materials/view?id=' . $material['id']) ?>"
                                    class="btn btn-sm btn-secondary">Voir</a>
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    <form method="post" action="<?= url('materials/delete') ?>"
                                        style="display:inline-block; margin-left: 0.5rem;"
                                        onsubmit="return confirm('Supprimer ce matériel ?');">
                                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= e($material['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">×</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
    <div class="d-flex justify-between items-center mt-4">
        <div class="text-muted" style="font-size: 0.875rem;">
            Affichage page <?= $pagination['current_page'] ?> sur <?= $pagination['last_page'] ?>
        </div>
        <div class="d-flex gap-2">
            <?php $queryParams = $_GET; ?>
            <?php if ($pagination['current_page'] > 1): ?>
                <?php $queryParams['page'] = $pagination['current_page'] - 1; ?>
                <a href="<?= url('materials') ?>?<?= http_build_query($queryParams) ?>"
                    class="btn btn-sm btn-secondary">Précédent</a>
            <?php endif; ?>

            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                <?php $queryParams['page'] = $pagination['current_page'] + 1; ?>
                <a href="<?= url('materials') ?>?<?= http_build_query($queryParams) ?>"
                    class="btn btn-sm btn-secondary">Suivant</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>