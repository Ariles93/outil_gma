<?php
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') die('Method');
if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF');
$assignment_id = (int)($_POST['assignment_id'] ?? 0);
if ($assignment_id <= 0) die('Invalid id');

try {
    $pdo->beginTransaction();
    $st = $pdo->prepare("SELECT material_id, agent_id FROM assignments WHERE id = ? AND returned_at IS NULL FOR UPDATE");
    $st->execute([$assignment_id]);
    $row = $st->fetch();
    if (!$row) throw new Exception('Affectation introuvable ou déjà retournée');
    $material_id = $row['material_id'];
    $agent_id = $row['agent_id'];
    $upd = $pdo->prepare("UPDATE assignments SET returned_at = CURDATE() WHERE id = ?");
    $upd->execute([$assignment_id]);
    $upd2 = $pdo->prepare("UPDATE materials SET status = 'available' WHERE id = ?");
    $upd2->execute([$material_id]);
    $pdo->commit();
    header('Location: view_agent.php?id='.$agent_id);
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die('Erreur: '.e($e->getMessage()));
}

