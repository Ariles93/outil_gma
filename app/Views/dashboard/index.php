<?php include __DIR__ . '/../partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Tableau de bord</h2>
    <span>Connecté en tant que: <strong><?= e($_SESSION['user_name'] ?? 'Utilisateur') ?></strong></span>
</div>
<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-value"><?= e($stats['total'] ?? 0) ?></div>
        <div class="stat-label">Matériels au total</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-success"><?= e($stats['available'] ?? 0) ?></div>
        <div class="stat-label">Disponibles</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-danger"><?= e($stats['assigned'] ?? 0) ?></div>
        <div class="stat-label">Attribués</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= e($total_agents ?? 0) ?></div>
        <div class="stat-label">Agents</div>
    </div>
</div>
<div class="dashboard-panel">
    <h3>Dernières attributions</h3>
    <?php if (empty($recent_assignments)): ?>
        <p>Aucune attribution récente.</p>
    <?php else: ?>
        <ul class="activity-list">
            <?php foreach ($recent_assignments as $assignment): ?>
                <li>
                    <strong
                        style="text-transform: capitalize;"><?= e($assignment['category_name'] . ' ' . $assignment['brand']) . ' (' . e($assignment['condition_on_assign']) . ')' ?></strong>
                    <span>attribué à <?= e($assignment['first_name'] . ' ' . $assignment['last_name']) ?></span>
                    <time><?= e((new DateTime($assignment['assigned_at']))->format('d/m/Y')) ?></time>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div><br>
<div class="dashboard-panel">
    <h3>Derniers retours</h3>
    <?php if (empty($recent_returns)): ?>
        <p>Aucun retour récent.</p>
    <?php else: ?>
        <ul class="activity-list">
            <?php foreach ($recent_returns as $return): ?>
                <li>
                    <strong><?= e($return['category_name'] . ' ' . $return['brand']) ?></strong>
                    <span>retourné par <?= e($return['first_name'] . ' ' . $return['last_name']) . ' le' ?></span>
                    <time><?= e((new DateTime($return['returned_at']))->format('d/m/Y')) ?></time>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>