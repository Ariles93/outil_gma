<?php
require_once 'db.php';
require_once 'protect.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF invalide');

    $email = trim($_POST['email'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'viewer';

    if (empty($email) || empty($first_name) || empty($password) || empty($role)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } elseif ($role !== 'admin' && $role !== 'viewer' && $role !== 'gestionnaire') {
        $error = "Le rôle n'est pas valide.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Cette adresse email est déjà utilisée.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("INSERT INTO users (email, first_name, password, role) VALUES (?, ?, ?, ?)");
                $insert->execute([$email, $first_name, $hashed_password, $role]);
                $success = true;
            }
        } catch (PDOException $e) {
            $error = "Erreur de base de données : " . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<h2>Ajouter un utilisateur</h2>

<div class="form-container">
    <?php if ($success): ?>
        <p class="alert alert-success">Utilisateur créé avec succès ! <a href="manage_users.php">Retour à la liste</a></p>
    <?php elseif (!empty($error)): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        
        <label for="first_name">Nom et Prénom:</label>
        <input id="first_name" name="first_name" type="text" required value="<?= e($_POST['first_name'] ?? '') ?>">
        
        <label for="email">Email:</label>
        <input id="email" type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
        
        <label for="password">Mot de passe:</label>
        <div class="password-wrapper">
            <input id="password" type="password" name="password" required>
            <span class="password-toggle-icon">
                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 10.224 7.29 6 12 6c4.708 0 8.577 4.224 9.964 5.683a1.012 1.012 0 010 .639C20.577 13.776 16.71 18 12 18s-8.577-4.224-9.964-5.683z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <svg class="icon-eye-slash hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 14.12 7.29 18 12 18c.99 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 6c4.708 0 8.577 4.224 9.964 5.683a1.012 1.012 0 010 .639c-1.143 1.44-3.345 3.9-6.071 5.492M9.879 9.879a3 3 0 104.242 4.242M9.879 9.879L6 6m12 12L6 6" /></svg>
            </span>
        </div>
        
        <label for="role">Rôle:</label>
        <select id="role" name="role">
            <option value="viewer" <?= (($_POST['role'] ?? '') === 'viewer') ? 'selected' : '' ?>>Viewer</option>
            <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
            <option value="gestionnaire" <?= (($_POST['role'] ?? '') === 'gestionnaire') ? 'selected' : '' ?>>Gestionnaire</option>
        </select>
        
        <button type="submit" class="btn btn-success">Créer l'utilisateur</button>
    </form>
</div>

<?php include 'footer.php'; ?>
