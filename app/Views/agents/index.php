<?php include __DIR__ . '/../partials/header.php'; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error"><?= e($_SESSION['error_message']) ?></div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success_message']) ?></div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Rechercher un agent</h2>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="<?= url('agents/create') ?>" class="btn btn-primary">+ Nouvel agent</a>
    <?php endif; ?>
</div>

<div class="form-container" style="margin-bottom: 2rem;">
    <form method="get" action="<?= url('agents') ?>">
        <input name="q" placeholder="Rechercher par prénom ou nom..." type="text" value="<?= e($search ?? '') ?>">
        <button type="submit" class="btn btn-primary">Rechercher</button>
        <a href="<?= url('agents') ?>" class="btn btn-secondary">Réinitialiser</a>
        <a href="<?= url('exports/agents') ?>" class="btn btn-secondary">Exporter CSV</a>
    </form>
</div>

<?php if (!empty($search)): ?>
    <h3>Résultats de la recherche pour "<?= e($search) ?>"</h3>
<?php else: ?>
    <h3>Derniers agents ajoutés</h3>
<?php endif; ?>

<?php if (empty($agents)): ?>
    <p>Aucun agent trouvé.</p>
<?php else: ?>
    <div class="agent-results-grid">
        <?php foreach ($agents as $agent): ?>
            <div class="agent-card">
                <div class="agent-card-header">
                    <a href="<?= url('agents/view?id=' . $agent['id']) ?>"
                        class="agent-name"><?= e($agent['first_name'] . ' ' . $agent['last_name']) ?></a>
                    <div class="agent-position"><?= e($agent['position'] ?? 'Poste non défini') ?></div>
                </div>
                <div class="agent-card-body">
                    <div class="agent-details">
                        <span><strong>Département :</strong> <?= e($agent['department'] ?? '-') ?></span>
                        <span><strong>Email :</strong> <?= e($agent['email'] ?? '-') ?></span>
                        <span><strong>Téléphone :</strong> <?= e($agent['phone'] ?? '-') ?></span>
                        <span><strong>Matricule :</strong> <?= e($agent['employee_id'] ?? '-') ?></span>
                    </div>

                    <?php
                    $materials = $agent['materials'] ?? [];
                    ?>

                    <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire']) && count($materials) > 0): ?>
                        <a href="<?= url('agents/view?id=' . $agent['id']) ?>" class="btn btn-primary"
                            style="padding: 0.4rem 0.8rem; font-size: 0.8rem; text-align: center;">Enregistrer un retour</a>
                    <?php endif; ?>
                    <?php if (count($materials) == 0): ?>
                        <a href="<?= url('agents/view?id=' . $agent['id']) ?>" class="btn btn-primary"
                            style="padding: 0.4rem 0.8rem; font-size: 0.8rem; text-align: center;">Voir les détails</a>
                    <?php endif; ?>
                    <div class="agent-materials">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h4>Matériel Actif (<?= count($materials) ?>)</h4>
                        </div>
                        <?php if (empty($materials)): ?>
                            <p class="no-material">Aucun matériel actuellement attribué.</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($materials as $m): ?>
                                    <li><?= e($m['category_name'] . ' ' . $m['brand'] . ' ' . $m['model']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                <a href="<?= url('agents?page=' . $i . '&q=' . urlencode($search ?? '')) ?>"
                    class="<?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>