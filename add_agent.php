<?php
require_once 'db.php';
require_once 'protect.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé. Seul un administrateur peut ajouter un agent.");
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) {
        die('Token CSRF invalide');
    }
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');

    if ($first === '' || $last === '') {
        $error = "Prénom et nom sont obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO agents (first_name,last_name,email,phone,department,position,employee_id,notes)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first, $last, $_POST['email'] ?: null, $_POST['phone'] ?: null, $_POST['department'] ?: null, $_POST['position'] ?: null, $_POST['employee_id'] ?: null, $_POST['notes'] ?: null]);
            
            header('Location: view_agent.php?id=' . $pdo->lastInsertId() . '&status=created');
            exit;
        } catch (PDOException $e) {
            $error = "Erreur de base de données: " . e($e->getMessage());
        }
    }
}
include 'header.php';
?>

<h2>Ajouter un agent</h2>

<div class="form-container">
    <?php if (!empty($error)): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      
      <label for="first_name">Prénom:</label>
      <input id="first_name" name="first_name" type="text" required><br><br>
      
      <label for="last_name">Nom:</label>
      <input id="last_name" name="last_name" type="text" required><br><br>
      
      <label for="email">Email:</label>
      <input id="email" name="email" type="email" required><br><br>
      
      <label for="phone">Téléphone:</label>
      <input id="phone" name="phone" type="tel"><br><br>
      
      <label for="department">Service:</label>
      <input id="department" name="department" type="text" required><br><br>
      
      <label for="position">Poste:</label>
      <input id="position" name="position" type="text" required><br><br>
      
      <label for="employee_id">ID employé:</label>
      <input id="employee_id" name="employee_id" type="number"><br><br>
      
      <label for="notes">Notes:</label>
      <textarea id="notes" name="notes" placeholder="Lieu d'affectation, bureau ... etc"></textarea>
      
      <button type="submit" class="btn btn-primary">Créer l'agent</button>
    </form>
</div>

<?php include 'footer.php'; ?>
