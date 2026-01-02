<?php include __DIR__ . '/../partials/header.php'; ?>

<!-- Stats Grid -->
<div class="dashboard-grid">
    <div class="card stat-card">
        <div class="stat-label">Total Matériels</div>
        <div class="stat-value" style="color: var(--color-primary);"><?= e($stats['total'] ?? 0) ?></div>
        <div class="text-muted" style="font-size: 0.85rem;">Inventaire global</div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Disponibles</div>
        <div class="stat-value" style="color: var(--color-success);"><?= e($stats['available'] ?? 0) ?></div>
        <div class="text-muted" style="font-size: 0.85rem;">Prêts à être attribués</div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Attribués</div>
        <div class="stat-value" style="color: var(--color-danger);"><?= e($stats['assigned'] ?? 0) ?></div>
        <div class="text-muted" style="font-size: 0.85rem;">En possession des agents</div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Agents</div>
        <div class="stat-value"><?= e($total_agents ?? 0) ?></div>
        <div class="text-muted" style="font-size: 0.85rem;">Utilisateurs enregistrés</div>
    </div>
</div>

<!-- Charts Section -->
<div class="d-flex" style="gap: 1.5rem; flex-wrap: wrap; margin-bottom: 2rem;">
    <div class="card" style="flex: 1; min-width: 350px;">
        <h3>Répartition par catégorie</h3>
        <div style="height: 300px; position: relative;">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
    <div class="card" style="flex: 1; min-width: 350px;">
        <h3>Historique (12 mois)</h3>
        <div style="height: 300px; position: relative;">
            <canvas id="historyChart"></canvas>
        </div>
    </div>
</div>

<!-- Scripts for Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Global defaults for charts to match theme
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748B';
    Chart.defaults.scale.grid.color = '#F1F5F9';

    function initCharts() {
        // Data for Categories
        const catLabels = <?= json_encode(array_column($category_stats, 'name')) ?>;
        const catData = <?= json_encode(array_column($category_stats, 'count')) ?>;
        const catCtx = document.getElementById('categoryChart');

        if (Chart.getChart(catCtx)) Chart.getChart(catCtx).destroy();

        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#64748B'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                },
                cutout: '70%'
            }
        });

        // Data for History
        const histLabels = <?= json_encode($history_labels) ?>;
        const histAssignData = <?= json_encode($history_assignments) ?>;
        const histReturnData = <?= json_encode($history_returns) ?>;
        const histCtx = document.getElementById('historyChart');

        if (Chart.getChart(histCtx)) Chart.getChart(histCtx).destroy();

        new Chart(histCtx, {
            type: 'line',
            data: {
                labels: histLabels,
                datasets: [
                    {
                        label: 'Attributions',
                        data: histAssignData,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#3B82F6',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Retours',
                        data: histReturnData,
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#EF4444',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', align: 'end', labels: { usePointStyle: true } } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initCharts);
    document.addEventListener('htmx:afterSwap', function (event) {
        if (document.getElementById('categoryChart')) initCharts();
    });
</script>

<!-- Recent Activity -->
<div class="d-flex" style="gap: 1.5rem; flex-wrap: wrap;">
    <div class="card" style="flex: 1; min-width: 400px;">
        <h3>Dernières attributions</h3>
        <?php if (empty($recent_assignments)): ?>
            <p class="text-muted">Aucune attribution récente.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <?php foreach ($recent_assignments as $assignment): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 500;">
                                        <?= e($assignment['category_name'] . ' ' . $assignment['brand']) ?></div>
                                    <div class="text-muted" style="font-size: 0.8rem;">
                                        <?= e($assignment['condition_on_assign']) ?></div>
                                </td>
                                <td>
                                    Attribué à
                                    <strong><?= e($assignment['first_name'] . ' ' . $assignment['last_name']) ?></strong>
                                </td>
                                <td class="text-right">
                                    <span
                                        class="badge badge-neutral"><?= e((new DateTime($assignment['assigned_at']))->format('d/m/Y')) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card" style="flex: 1; min-width: 400px;">
        <h3>Derniers retours</h3>
        <?php if (empty($recent_returns)): ?>
            <p class="text-muted">Aucun retour récent.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <?php foreach ($recent_returns as $return): ?>
                            <tr>
                                <td>
                                    <strong><?= e($return['category_name'] . ' ' . $return['brand']) ?></strong>
                                </td>
                                <td>
                                    Retourné par <strong><?= e($return['first_name'] . ' ' . $return['last_name']) ?></strong>
                                </td>
                                <td class="text-right">
                                    <span
                                        class="badge badge-neutral"><?= e((new DateTime($return['returned_at']))->format('d/m/Y')) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>