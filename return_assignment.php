<?php
require_once 'db.php';
require_once 'protect.php';

if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}
if (!check_csrf($_POST['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
    exit;
}
$assignment_id = (int)($_POST['assignment_id'] ?? 0);
if ($assignment_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID invalide.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT material_id, agent_id FROM assignments WHERE id = ? AND returned_at IS NULL");
    $stmt->execute([$assignment_id]);
    $assignment = $stmt->fetch();

    if (!$assignment) {
        throw new Exception('Affectation introuvable ou matériel déjà retourné.');
    }
    $material_id = $assignment['material_id'];
    $agent_id = $assignment['agent_id'];

    $upd = $pdo->prepare("UPDATE assignments SET returned_at = CURDATE() WHERE id = ?");
    $upd->execute([$assignment_id]);

    $upd2 = $pdo->prepare("UPDATE materials SET status = 'available' WHERE id = ?");
    $upd2->execute([$material_id]);

    $pdo->commit();

    // --- DÉBUT LOG ---
    $stmt_agent = $pdo->prepare("SELECT first_name, last_name FROM agents WHERE id = ?");
    $stmt_agent->execute([$agent_id]);
    $agent = $stmt_agent->fetch();
    $agent_name = $agent['first_name'] . ' ' . $agent['last_name'];

    $stmt_mat = $pdo->prepare("SELECT c.name, m.brand FROM materials m JOIN categories c ON m.category_id = c.id WHERE m.id = ?");
    $stmt_mat->execute([$material_id]);
    $mat_info = $stmt_mat->fetch();
    $mat_name = $mat_info['name'] . ' ' . $mat_info['brand'];
            
    log_action($pdo, "Retour du matériel \"{$mat_name}\" par l'agent \"{$agent_name}\"");
    // --- FIN LOG ---    

    // On renvoie une réponse de succès au format JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'assignment_id' => $assignment_id]);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . e($e->getMessage())]);
    exit;
}