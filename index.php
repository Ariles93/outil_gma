<?php
require_once 'db.php';
require_once 'protect.php';

// --- Récupération des statistiques ---

// 1. Compter les matériels par statut
$stats_query = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM materials WHERE deleted_at IS NULL) as total,
        (SELECT COUNT(*) FROM materials WHERE status = 'available' AND deleted_at IS NULL) as available,
        (SELECT COUNT(*) FROM materials WHERE status = 'assigned' AND deleted_at IS NULL) as assigned
");
$stats = $stats_query->fetch();

// 2. Compter le nombre total d'agents
$total_agents = $pdo->query("SELECT COUNT(*) FROM agents")->fetchColumn();


// 3. Récupérer les 5 dernières attributions
$recent_assignments_query = $pdo->query("
    SELECT a.assigned_at, ag.first_name, ag.last_name, c.name as category_name, m.brand, m.model, a.condition_on_assign
    FROM assignments a
    JOIN agents ag ON a.agent_id = ag.id
    JOIN materials m ON a.material_id = m.id
    JOIN categories c ON m.category_id = c.id
    ORDER BY a.assigned_at DESC, a.id DESC
    LIMIT 5
");
$recent_assignments = $recent_assignments_query->fetchAll();

// 4. Récupérer les 5 derniers retours
$recent_returns_query = $pdo->query("
    SELECT a.returned_at, ag.first_name, ag.last_name, c.name as category_name, m.brand, m.model
    FROM assignments a
    JOIN agents ag ON a.agent_id = ag.id
    JOIN materials m ON a.material_id = m.id
    JOIN categories c ON m.category_id = c.id
    WHERE a.returned_at IS NOT NULL
    ORDER BY a.returned_at DESC, a.id DESC
    LIMIT 5
");
$recent_returns = $recent_returns_query->fetchAll();

include 'header.php';
?>

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
                <strong style="text-transform: capitalize;"><?= e($assignment['category_name'] . ' ' . $assignment['brand']). ' ('.e($assignment['condition_on_assign']) . ')' ?></strong>
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
                    <span>retourné par <?= e($return['first_name'] . ' ' . $return['last_name']) .' le'?></span>
                    <time><?= e((new DateTime($return['returned_at']))->format('d/m/Y')) ?></time>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>