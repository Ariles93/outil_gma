<?php include __DIR__ . '/../partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Attributions</h2>
    <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
        <a href="<?= url('assignments/create') ?>" class="btn btn-primary">Nouvelle attribution</a>
    <?php endif; ?>
</div>

<div class="form-container" style="margin-bottom: 1.5rem;">
    <form method="get" action="<?= url('assignments') ?>">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <input name="q" placeholder="Rechercher..." type="text" value="<?= e($search ?? '') ?>"
                style="min-width: 300px; flex-grow: 1;">
            <button type="submit" class="btn btn-primary">Rechercher</button>
            <a href="<?= url('assignments') ?>" class="btn btn-secondary">Réinitialiser</a>
        </div>
    </form>
</div>

<div class="content-box">
    <table class="data-table">
        <thead>
            <tr>
                <th>
                    <a
                        href="<?= url('assignments') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'category_name', 'order' => ($sortColumn === 'category_name' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">
                        Matériel <?= $sortColumn === 'category_name' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>
                    <a
                        href="<?= url('assignments') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'last_name', 'order' => ($sortColumn === 'last_name' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">
                        Agent <?= $sortColumn === 'last_name' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>
                    <a
                        href="<?= url('assignments') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'assigned_at', 'order' => ($sortColumn === 'assigned_at' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">
                        Date d'attribution
                        <?= $sortColumn === 'assigned_at' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>
                    <a
                        href="<?= url('assignments') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'condition_on_assign', 'order' => ($sortColumn === 'condition_on_assign' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">
                        État <?= $sortColumn === 'condition_on_assign' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>
                    <a
                        href="<?= url('assignments') ?>?<?= http_build_query(array_merge($_GET, ['sort' => 'returned_at', 'order' => ($sortColumn === 'returned_at' && $sortOrder === 'asc') ? 'desc' : 'asc'])) ?>">
                        Retourné le <?= $sortColumn === 'returned_at' ? ($sortOrder === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($assignments)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 2rem;">Aucune attribution trouvée.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td>
                            <?= e($assignment['category_name'] . ' ' . $assignment['brand'] . ' ' . $assignment['model']) ?>
                            <br>
                            <small
                                class="text-muted"><?= e($assignment['asset_tag'] ?: 'S/N: ' . $assignment['serial_number']) ?></small>
                        </td>
                        <td>
                            <a href="<?= url('agents/view?id=' . $assignment['agent_id']) ?>">
                                <?= e($assignment['first_name'] . ' ' . $assignment['last_name']) ?>
                            </a>
                        </td>
                        <td><?= e((new DateTime($assignment['assigned_at']))->format('d/m/Y')) ?></td>
                        <td><?= e($assignment['condition_on_assign']) ?></td>
                        <td>
                            <?php if ($assignment['returned_at']): ?>
                                <?= e((new DateTime($assignment['returned_at']))->format('d/m/Y')) ?>
                            <?php else: ?>
                                <span class="badge badge-success">En cours</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$assignment['returned_at'] && in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
                                <form method="post" action="<?= url('assignments/return') ?>" style="display:inline;">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="assignment_id" value="<?= e($assignment['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-primary"
                                        onclick="return confirm('Confirmer le retour ?')">Retourner</button>
                                </form>
                            <?php endif; ?>
                            <a href="<?= url('assignments/pdf?id=' . $assignment['id']) ?>" class="btn btn-sm btn-secondary"
                                target="_blank">PDF</a>
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
                <a href="<?= url('assignments') ?>?<?= http_build_query($queryParams) ?>">&laquo; Précédent</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                <?php $queryParams['page'] = $i; ?>
                <a href="<?= url('assignments') ?>?<?= http_build_query($queryParams) ?>"
                    class="<?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                <?php $queryParams['page'] = $pagination['current_page'] + 1; ?>
                <a href="<?= url('assignments') ?>?<?= http_build_query($queryParams) ?>">Suivant &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>