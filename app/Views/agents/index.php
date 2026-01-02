<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Rechercher un agent</h2>
    <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
        <a href="<?= url('agents/create') ?>" class="btn btn-primary">
            + Nouvel agent
        </a>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <form method="get" action="<?= url('agents') ?>">
        <div class="d-flex gap-4 items-center flex-wrap">
            <div style="flex-grow: 1;">
                <input name="q" placeholder="Rechercher par prénom, nom, matricule..." type="search"
                    value="<?= e($search ?? '') ?>" class="w-full">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Rechercher</button>
                <a href="<?= url('agents') ?>" class="btn btn-secondary">Réinitialiser</a>
                <a href="<?= url('exports/agents') ?>" class="btn btn-secondary">Export CSV</a>
            </div>
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Poste</th>
                    <th>Département</th>
                    <th>Contact</th>
                    <th>Matériel</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($agents)): ?>
                    <tr>
                        <td colspan="6" class="text-center p-4 text-muted">
                            <?php if (!empty($search)): ?>
                                Aucun résultat pour "<?= e($search) ?>"
                            <?php else: ?>
                                Aucun agent enregistré.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($agents as $agent): ?>
                        <tr>
                            <td>
                                <div class="d-flex items-center gap-2">
                                    <?php
                                    $initials = substr($agent['first_name'], 0, 1) . substr($agent['last_name'], 0, 1);
                                    ?>
                                    <div
                                        style="width: 32px; height: 32px; background: #F1F5F9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #64748B; font-size: 0.75rem;">
                                        <?= strtoupper($initials) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 500;">
                                            <a href="<?= url('agents/view?id=' . $agent['id']) ?>"
                                                style="color: var(--color-text-main);">
                                                <?= e($agent['first_name'] . ' ' . $agent['last_name']) ?>
                                            </a>
                                        </div>
                                        <?php if (!empty($agent['employee_id'])): ?>
                                            <div class="text-xs text-muted">Mat: <?= e($agent['employee_id']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($agent['position'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($agent['department'])): ?>
                                    <span class="badge badge-neutral"><?= e($agent['department']) ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <?php if (!empty($agent['email'])): ?>
                                        <a href="mailto:<?= e($agent['email']) ?>"
                                            style="color: var(--color-primary);"><?= e($agent['email']) ?></a>
                                    <?php endif; ?>
                                    <?php if (!empty($agent['phone'])): ?>
                                        <div class="text-muted text-xs"><?= e($agent['phone']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php $materialCount = isset($agent['materials']) ? count($agent['materials']) : 0; ?>
                                <?php if ($materialCount > 0): ?>
                                    <span class="badge badge-neutral"><?= $materialCount ?> équipement(s)</span>
                                <?php else: ?>
                                    <span class="text-muted text-sm">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <a href="<?= url('agents/view?id=' . $agent['id']) ?>" class="btn btn-sm btn-secondary">Voir
                                    dossier</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
        <div class="d-flex justify-center gap-2 p-4 border-t" style="border-top: 1px solid var(--color-border);">
            <?php if ($pagination['current_page'] > 1): ?>
                <a href="<?= url('agents?page=' . ($pagination['current_page'] - 1) . '&q=' . urlencode($search ?? '')) ?>"
                    class="btn btn-sm btn-secondary">Précédent</a>
            <?php endif; ?>

            <span class="btn btn-sm btn-secondary" style="background: none; border: none; cursor: default;">
                Page <?= $pagination['current_page'] ?> / <?= $pagination['last_page'] ?>
            </span>

            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                <a href="<?= url('agents?page=' . ($pagination['current_page'] + 1) . '&q=' . urlencode($search ?? '')) ?>"
                    class="btn btn-sm btn-secondary">Suivant</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>