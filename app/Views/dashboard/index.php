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

<div style="display: flex; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap;">
    <div class="dashboard-panel" style="flex: 1; min-width: 300px;">
        <h3>Répartition du matériel par catégorie</h3>
        <canvas id="categoryChart"></canvas>
    </div>
    <div class="dashboard-panel" style="flex: 1; min-width: 300px;">
        <h3>Évolution des Attributions et Retours (12 mois)</h3>
        <canvas id="historyChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function initCharts() {
        // Data for Categories
        const catLabels = <?= json_encode(array_column($category_stats, 'name')) ?>;
        const catData = <?= json_encode(array_column($category_stats, 'count')) ?>;

        // Destroy existing chart if it exists
        const catCtx = document.getElementById('categoryChart');
        if (Chart.getChart(catCtx)) {
            Chart.getChart(catCtx).destroy();
        }

        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Data for History
        const histLabels = <?= json_encode($history_labels) ?>;
        const histAssignData = <?= json_encode($history_assignments) ?>;
        const histReturnData = <?= json_encode($history_returns) ?>;

        // Destroy existing chart if it exists
        const histCtx = document.getElementById('historyChart');
        if (Chart.getChart(histCtx)) {
            Chart.getChart(histCtx).destroy();
        }

        new Chart(histCtx, {
            type: 'line',
            data: {
                labels: histLabels,
                datasets: [
                    {
                        label: 'Attributions',
                        data: histAssignData,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Retours',
                        data: histReturnData,
                        borderColor: '#e74a3b',
                        backgroundColor: 'rgba(231, 74, 59, 0.1)',
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                }
            }
        });
    }

    // Initialize on first load
    document.addEventListener('DOMContentLoaded', initCharts);

    // Re-initialize after HTMX content swap
    document.addEventListener('htmx:afterSwap', function (event) {
        if (document.getElementById('categoryChart')) {
            initCharts();
        }
    });
</script>
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