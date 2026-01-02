<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Attributions</h2>
    <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
        <a href="<?= url('assignments/create') ?>" class="btn btn-primary">
            + Nouvelle attribution
        </a>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <form method="get" action="<?= url('assignments') ?>">
        <div class="d-flex gap-4 items-center flex-wrap">
            <div style="flex-grow: 1;">
                <input name="q" placeholder="Rechercher (Matériel, Agent, Série...)" type="search"
                    value="<?= e($search ?? '') ?>" class="w-full">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Rechercher</button>
                <a href="<?= url('assignments') ?>" class="btn btn-secondary">Réinitialiser</a>
            </div>
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <a href="<?= url('assignments') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'category_name', 'order' => ($sortColumn === 'category_name' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>"
                            class="text-muted" style="color: inherit;">
                            Matériel <?= $sortColumn === 'category_name' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="<?= url('assignments') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'last_name', 'order' => ($sortColumn === 'last_name' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>"
                            class="text-muted" style="color: inherit;">
                            Agent <?= $sortColumn === 'last_name' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="<?= url('assignments') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'assigned_at', 'order' => ($sortColumn === 'assigned_at' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>"
                            class="text-muted" style="color: inherit;">
                            Date <?= $sortColumn === 'assigned_at' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>État</th>
                    <th>Retour</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="6" class="text-center p-4 text-muted">
                            Aucune attribution trouvée.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 500;">
                                    <?= e($assignment['category_name'] . ' ' . $assignment['brand'] . ' ' . $assignment['model']) ?>
                                </div>
                                <div class="text-muted text-sm" style="font-size: 0.75rem;">
                                    <?= e($assignment['asset_tag'] ?: 'S/N: ' . $assignment['serial_number']) ?>
                                </div>
                            </td>
                            <td>
                                <a href="<?= url('agents/view?id=' . $assignment['agent_id']) ?>"
                                    style="font-weight: 500; color: var(--color-primary);">
                                    <?= e($assignment['first_name'] . ' ' . $assignment['last_name']) ?>
                                </a>
                            </td>
                            <td><?= e((new DateTime($assignment['assigned_at']))->format('d/m/Y')) ?></td>
                            <td><?= e($assignment['condition_on_assign']) ?></td>
                            <td>
                                <?php if ($assignment['returned_at']): ?>
                                    <span
                                        class="text-muted"><?= e((new DateTime($assignment['returned_at']))->format('d/m/Y')) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success">En cours</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <a href="<?= url('assignments/pdf?id=' . $assignment['id']) ?>" class="btn btn-sm btn-secondary"
                                    target="_blank">PDF</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
        <div class="d-flex justify-center gap-2 p-4 border-t" style="border-top: 1px solid var(--color-border);">
            <?php $queryParams = $_GET; ?>
            <?php if ($pagination['current_page'] > 1): ?>
                <?php $queryParams['page'] = $pagination['current_page'] - 1; ?>
                <a href="<?= url('assignments') ?>?<?= http_build_query($queryParams) ?>"
                    class="btn btn-sm btn-secondary">Précédent</a>
            <?php endif; ?>

            <span class="btn btn-sm btn-secondary" style="background:none; border:none; cursor:default;">
                Page <?= $pagination['current_page'] ?> / <?= $pagination['last_page'] ?>
            </span>

            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                <?php $queryParams['page'] = $pagination['current_page'] + 1; ?>
                <a href="<?= url('assignments') ?>?<?= http_build_query($queryParams) ?>"
                    class="btn btn-sm btn-secondary">Suivant</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>