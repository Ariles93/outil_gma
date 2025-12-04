<?php include __DIR__ . '/../partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Détails du matériel</h2>
    <div>
        <a href="<?= url('materials') ?>" class="btn btn-secondary">Retour à la liste</a>
        <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
            <a href="<?= url('materials/edit?id=' . $material['id']) ?>" class="btn btn-primary">Modifier</a>
        <?php endif; ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
    <div class="content-box">
        <h3>Informations</h3>
        <p><strong>Type:</strong> <?= e($material['category_name']) ?></p>
        <p><strong>Étiquette (Asset Tag):</strong> <?= e($material['asset_tag'] ?? '-') ?></p>
        <p><strong>Marque:</strong> <?= e($material['brand'] ?? '-') ?></p>
        <p><strong>Modèle:</strong> <?= e($material['model'] ?? '-') ?></p>
        <p><strong>Numéro de série:</strong> <?= e($material['serial_number'] ?? '-') ?></p>
        <p><strong>Numéro d'inventaire:</strong> <?= e($material['inventory_number'] ?? '-') ?></p>
        <p><strong>Date d'achat:</strong>
            <?= e($material['purchase_date'] ? (new DateTime($material['purchase_date']))->format('d/m/Y') : '-') ?></p>
        <p><strong>Fin de garantie:</strong>
            <?= e($material['warranty_expiry'] ? (new DateTime($material['warranty_expiry']))->format('d/m/Y') : '-') ?>
        </p>
        <p><strong>Coût:</strong> <?= e($material['cost'] ?? '-') ?> €</p>
        <p><strong>Notes:</strong><br><?= nl2br(e($material['notes'] ?? '-')) ?></p>
    </div>

    <div class="content-box">
        <h3>Statut actuel</h3>

        <p>
            <strong>Statut:</strong>
            <?php
            $statusColor = 'var(--color-accent)';
            if ($material['status'] === 'assigned')
                $statusColor = 'var(--color-danger)';
            if ($material['status'] === 'maintenance')
                $statusColor = 'var(--color-warning)';
            if ($material['status'] === 'broken')
                $statusColor = 'var(--color-danger)';
            ?>
            <span style="font-weight: bold; color: <?= $statusColor ?>;">
                <?php
                $statusLabels = [
                    'available' => 'Disponible',
                    'assigned' => 'Attribué',
                    'maintenance' => 'En maintenance',
                    'broken' => 'Hors service',
                ];
                echo e($statusLabels[$material['status']] ?? ucfirst($material['status']));
                ?>
            </span>
        </p>

        <?php // Si le matériel est déjà attribué, on affiche les détails de l'agent. ?>
        <?php if ($material['status'] === 'assigned' && !empty($material['agent_id'])): ?>
            <p><strong>Attribué à :
                    <a href="<?= url('agents/view?id=' . $material['agent_id']) ?>">
                        <?= e($material['first_name'] . ' ' . $material['last_name']) ?>
                    </a></strong>
            </p>
            <p><strong>Depuis le:</strong> <?= e((new DateTime($material['assigned_at']))->format('d/m/Y')) ?><br>
            <p>Ce matériel est actuellement attribué, veuillez enregistrer son retour avant une éventuelle réattribution</p>
            </p>

            <?php // Sinon, si le matériel n'est PAS attribué ET que le rôle est autorisé, on affiche le bouton. ?>
        <?php elseif ($material['status'] !== 'assigned' && in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
            <p>Ce matériel est actuellement disponible.</p>
            <a href="<?= url('assignments/create?material_id=' . $material['id']) ?>" class="btn btn-primary"
                style="margin-top: 1rem;">
                Attribuer ce matériel
            </a>
        <?php endif; ?>
    </div>

    <div class="content-box">
        <h3>Historique des attributions</h3>
        <?php if (empty($history)): ?>
            <p>Ce matériel n'a jamais été attribué.</p>
        <?php else: ?>
            <div class="table-container" style="box-shadow: none; border: none; background: none; backdrop-filter: none;">
                <table>
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
                                <td><a
                                        href="<?= url('agents/view?id=' . $entry['agent_id']) ?>"><?= e($entry['first_name'] . ' ' . $entry['last_name']) ?></a>
                                </td>
                                <td><?= e((new DateTime($entry['assigned_at']))->format('d/m/Y')) ?></td>
                                <td><?= $entry['returned_at'] ? e((new DateTime($entry['returned_at']))->format('d/m/Y')) : '<em>En cours</em>' ?>
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