<?php
require_once 'db.php';
require_once 'protect.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID de matériel invalide.");

// Requête pour les détails du matériel
$stmt = $pdo->prepare("
    SELECT 
        m.*, c.name AS category_name,
        a.assigned_at, a.condition_on_assign, a.note AS assignment_note,
        ag.id AS agent_id, ag.first_name, ag.last_name
    FROM materials m
    LEFT JOIN categories c ON m.category_id = c.id
    LEFT JOIN assignments a ON a.material_id = m.id AND a.returned_at IS NULL
    LEFT JOIN agents ag ON ag.id = a.agent_id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$material = $stmt->fetch();
if (!$material) die("Matériel introuvable.");

// Requête pour l'historique complet des attributions
$history_stmt = $pdo->prepare("
    SELECT a.assigned_at, a.returned_at, a.note, ag.first_name, ag.last_name, ag.id as agent_id
    FROM assignments a
    JOIN agents ag ON a.agent_id = ag.id
    WHERE a.material_id = ?
    ORDER BY a.assigned_at DESC
");
$history_stmt->execute([$id]);
$history = $history_stmt->fetchAll();

include 'header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Détails du matériel</h2>
        <a href="view_materials.php" class="btn btn-primary">Retour à la liste complète</a>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">    
    <div class="content-box">
        <h3>Informations</h3>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="edit_material.php?id=<?= e($material['id']) ?>" class="btn btn-primary">Modifier</a>
    <?php endif; ?>
        <p><strong>Type:</strong> <?= e($material['category_name']) ?></p>
        <p><strong>Étiquette (Asset Tag):</strong> <?= e($material['asset_tag'] ?? '-') ?></p>
        <p><strong>Marque:</strong> <?= e($material['brand'] ?? '-') ?></p>
        <p><strong>Modèle:</strong> <?= e($material['model'] ?? '-') ?></p>
        <p><strong>Numéro de série:</strong> <?= e($material['serial_number'] ?? '-') ?></p>
        <p><strong>Date d'achat:</strong> <?= e($material['purchase_date'] ?? '-') ?></p>
        <p><strong>Fin de garantie:</strong> <?= e($material['warranty_end'] ?? '-') ?></p>
        <p><strong>Notes:</strong><br><?= nl2br(e($material['notes'] ?? '-')) ?></p>
    </div>

<div class="content-box">
    <h3>Statut actuel</h3>

    <p>
        <strong>Statut:</strong>
        <span style="font-weight: bold; color: <?= $material['status'] === 'assigned' ? 'var(--color-danger)' : 'var(--color-accent)'; ?>;">
            <?= e(ucfirst($material['status'])) ?>
        </span>
    </p>

    <?php // Si le matériel est déjà attribué, on affiche les détails de l'agent. ?>
    <?php if ($material['status'] === 'assigned' && !empty($material['agent_id'])): ?>
        <p><strong>Attribué à : 
            <a href="view_agent.php?id=<?= e($material['agent_id']) ?>">
                <?= e($material['first_name'] . ' ' . $material['last_name']) ?>
            </a></strong>
        </p>
        <p><strong>Depuis le:</strong> <?= e((new DateTime($material['assigned_at']))->format('d/m/Y')) ?><br>
        <p>Ce matériel est actuellement attribué, veuillez enregistrer son retour avant une éventuelle réattribution</p></p>
    
    <?php // Sinon, si le matériel n'est PAS attribué ET que le rôle est autorisé, on affiche le bouton. ?>
    <?php elseif ($material['status'] !== 'assigned' && in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
        <p>Ce matériel est actuellement disponible.</p>
        <a href="assign.php?material_id=<?= e($material['id']) ?>" class="btn btn-primary" style="margin-top: 1rem;">
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
                        <?php foreach($history as $entry): ?>
                            <tr>
                                <td><a href="view_agent.php?id=<?= e($entry['agent_id']) ?>"><?= e($entry['first_name'] . ' ' . $entry['last_name']) ?></a></td>
                                <td><?= e((new DateTime($entry['assigned_at']))->format('d/m/Y')) ?></td>
                                <td><?= $entry['returned_at'] ? e((new DateTime($entry['returned_at']))->format('d/m/Y')) : '<em>En cours</em>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>