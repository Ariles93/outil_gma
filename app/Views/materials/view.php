<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Détails du matériel</h2>
    <div class="d-flex gap-2">
        <a href="<?= url('materials') ?>" class="btn btn-secondary">&larr; Retour à la liste</a>
        <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
            <a href="<?= url('materials/edit?id=' . $material['id']) ?>" class="btn btn-primary">Modifier</a>
        <?php endif; ?>
    </div>
</div>

<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
    <!-- Main Info -->
    <div class="card">
        <h3 class="mb-4">Informations</h3>
        <dl style="display: grid; grid-template-columns: auto 1fr; gap: 0.75rem 1.5rem; align-items: baseline;">
            <dt class="text-muted" style="font-weight: 500;">Type:</dt>
            <dd style="font-weight: 600;"><?= e($material['category_name']) ?></dd>

            <dt class="text-muted" style="font-weight: 500;">Étiquette:</dt>
            <dd><?= e($material['asset_tag'] ?? '-') ?></dd>

            <dt class="text-muted" style="font-weight: 500;">Marque:</dt>
            <dd><?= e($material['brand'] ?? '-') ?></dd>

            <dt class="text-muted" style="font-weight: 500;">Modèle:</dt>
            <dd><?= e($material['model'] ?? '-') ?></dd>

            <dt class="text-muted" style="font-weight: 500;">N° Série:</dt>
            <dd class="text-mono"><?= e($material['serial_number'] ?? '-') ?></dd>

            <dt class="text-muted" style="font-weight: 500;">Inventaire:</dt>
            <dd class="text-mono"><?= e($material['inventory_number'] ?? '-') ?></dd>

            <dt class="text-muted" style="font-weight: 500;">Acheté le:</dt>
            <dd><?= e($material['purchase_date'] ? (new DateTime($material['purchase_date']))->format('d/m/Y') : '-') ?>
            </dd>

            <dt class="text-muted" style="font-weight: 500;">Garantie fin:</dt>
            <dd><?= e($material['warranty_expiry'] ? (new DateTime($material['warranty_expiry']))->format('d/m/Y') : '-') ?>
            </dd>

            <dt class="text-muted" style="font-weight: 500;">Coût:</dt>
            <dd><?= e($material['cost'] ?? '-') ?> €</dd>

            <dt class="text-muted" style="font-weight: 500;">Notes:</dt>
            <dd><?= nl2br(e($material['notes'] ?? '-')) ?></dd>
        </dl>
    </div>

    <!-- Status & Assignment -->
    <div class="d-flex flex-col gap-4">
        <div class="card">
            <h3 class="mb-4">Statut actuel</h3>

            <?php
            $statusClass = match ($material['status']) {
                'available' => 'badge-success',
                'assigned' => 'badge-danger',
                'maintenance' => 'badge-warning',
                'broken' => 'badge-danger',
                default => 'badge-neutral'
            };
            $statusLabel = match ($material['status']) {
                'available' => 'Disponible',
                'assigned' => 'Attribué',
                'maintenance' => 'En maintenance',
                'broken' => 'Hors service',
                default => ucfirst($material['status'])
            };
            ?>
            <div style="font-size: 1.25rem; margin-bottom: 1rem;">
                <span class="badge <?= $statusClass ?>"
                    style="font-size: 1rem; padding: 0.5rem 1rem;"><?= $statusLabel ?></span>
            </div>

            <?php if ($material['status'] === 'assigned' && !empty($material['agent_id'])): ?>
                <div style="background-color: var(--color-bg); padding: 1rem; border-radius: var(--radius-md);">
                    <p class="mb-2 text-muted">Actuellement attribué à :</p>
                    <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 0.5rem;">
                        <a href="<?= url('agents/view?id=' . $material['agent_id']) ?>">
                            <?= e($material['first_name'] . ' ' . $material['last_name']) ?>
                        </a>
                    </div>
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                        Depuis le: <?= e((new DateTime($material['assigned_at']))->format('d/m/Y')) ?>
                    </p>
                </div>
                <div class="alert badge-neutral mt-4" style="display: block;">
                    Ce matériel est actuellement attribué. Veuillez enregistrer son retour avant une éventuelle
                    réattribution.
                </div>
            <?php elseif ($material['status'] !== 'assigned' && in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
                <p class="text-success mb-4">Ce matériel est prêt à être attribué.</p>
                <a href="<?= url('assignments/create?material_id=' . $material['id']) ?>" class="btn btn-primary w-full">
                    Attribuer ce matériel
                </a>
            <?php endif; ?>
        </div>

        <!-- Maybe adding an image placeholder or QR code later -->
    </div>

    <!-- History -->
    <div class="card" style="grid-column: 1 / -1;">
        <h3>Historique des attributions</h3>
        <?php if (empty($history)): ?>
            <p class="text-muted">Ce matériel n'a jamais été attribué.</p>
        <?php else: ?>
            <div class="table-responsive" style="box-shadow: none; border: none;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Attribué le</th>
                            <th>Retourné le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $entry): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('agents/view?id=' . $entry['agent_id']) ?>" style="font-weight: 500;">
                                        <?= e($entry['first_name'] . ' ' . $entry['last_name']) ?>
                                    </a>
                                </td>
                                <td><?= e((new DateTime($entry['assigned_at']))->format('d/m/Y')) ?></td>
                                <td>
                                    <?php if ($entry['returned_at']): ?>
                                        <?= e((new DateTime($entry['returned_at']))->format('d/m/Y')) ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning">En cours</span>
                                    <?php endif; ?>
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