<?php
require_once 'db.php';
require_once 'protect.php';

// Seul un administrateur peut modifier un agent
if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}

$agent_id = (int)($_GET['id'] ?? 0);
if ($agent_id <= 0) die('Agent invalide.');

$error = '';
$success = false;

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF invalide');

    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');

    if ($first === '' || $last === '') {
        $error = "Le prénom et le nom sont des champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE agents SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    department = ?,
                    position = ?,
                    employee_id = ?,
                    notes = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $first,
                $last,
                $_POST['email'] ?: null,
                $_POST['phone'] ?: null,
                $_POST['department'] ?: null,
                $_POST['position'] ?: null,
                $_POST['employee_id'] ?: null,
                $_POST['notes'] ?: null,
                $agent_id
            ]);
            $success = true;
        } catch (PDOException $e) {
            $error = 'Erreur de base de données : ' . e($e->getMessage());
        }
    }
}

// Récupérer les informations actuelles de l'agent pour le formulaire
$stmt = $pdo->prepare("SELECT * FROM agents WHERE id = ?");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();
if (!$agent) die('Agent introuvable.');

include 'header.php';
?>

<h2>Modifier l'agent : <?= e($agent['first_name'] . ' ' . $agent['last_name']) ?></h2>

<div class="form-container">
    <?php if ($success): ?>
        <p class="alert alert-success">Agent mis à jour avec succès ! <a href="view_agent.php?id=<?= e($agent_id) ?>">Retour à la fiche de l'agent</a></p>
    <?php elseif (!empty($error)): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      
        <label for="first_name">Prénom:</label>
        <input id="first_name" name="first_name" type="text" required value="<?= e($agent['first_name']) ?>">
      
        <label for="last_name">Nom:</label>
        <input id="last_name" name="last_name" type="text" required value="<?= e($agent['last_name']) ?>">
      
        <label for="email">Email:</label>
        <input id="email" name="email" type="email" value="<?= e($agent['email']) ?>">
      
        <label for="phone">Téléphone:</label>
        <input id="phone" name="phone" type="tel" value="<?= e($agent['phone']) ?>">
      
        <label for="department">Département:</label>
        <input id="department" name="department" type="text" value="<?= e($agent['department']) ?>">
      
        <label for="position">Poste:</label>
        <input id="position" name="position" type="text" value="<?= e($agent['position']) ?>">
      
        <label for="employee_id">ID employé:</label>
        <input id="employee_id" name="employee_id" type="text" value="<?= e($agent['employee_id']) ?>">
      
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"><?= e($agent['notes']) ?></textarea>
      
        <button type="submit" class="btn btn-primary">Mettre à jour l'agent</button>
    </form>
</div>

<?php include 'footer.php'; ?>