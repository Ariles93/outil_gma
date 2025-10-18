<?php
require_once 'db.php';
require_once 'protect.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}

$user_id = (int)($_GET['id'] ?? 0);
if ($user_id <= 0) die('Utilisateur invalide.');

$error = '';
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF invalide');

    // Mise à jour des infos principales
    $email = trim($_POST['email'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $role = $_POST['role'] ?? '';

    $update = $pdo->prepare("UPDATE users SET email = ?, first_name = ?, role = ? WHERE id = ?");
    $update->execute([$email, $first_name, $role, $user_id]);
    $success = "Informations mises à jour.";

    // Réinitialisation du mot de passe (si demandé)
    $new_password = $_POST['new_password'] ?? '';
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_pass = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_pass->execute([$hashed_password, $user_id]);
        $success .= " Le mot de passe a été réinitialisé.";
    }
}

// Récupérer les infos de l'utilisateur pour pré-remplir le formulaire
$stmt = $pdo->prepare("SELECT email, first_name, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) die('Utilisateur introuvable.');

include 'header.php';
?>

<h2>Modifier l'utilisateur : <?= e($user['first_name']) ?></h2>

<div class="form-container">
    <?php if ($success): ?>
        <p class="alert alert-success"><?= e($success) ?> <a href="manage_users.php">Retour à la liste</a></p>
    <?php elseif (!empty($error)): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <h3>Informations</h3>
        
        <label for="first_name">Nom et Prénom:</label>
        <input id="first_name" type="text" name="first_name" required value="<?= e($user['first_name']) ?>">
        
        <label for="email">Email:</label>
        <input id="email" type="email" name="email" required value="<?= e($user['email']) ?>">
        
        <label for="role">Rôle:</label>
        <select id="role" name="role">
            <option value="viewer" <?= ($user['role'] === 'viewer') ? 'selected' : '' ?>>Viewer</option>
            <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
            <option value="gestionnaire" <?= ($user['role'] === 'gestionnaire') ? 'selected' : '' ?>>Gestionnaire</option>
        </select>
        
        <hr style="border: none; border-top: 1px solid var(--color-border); margin: 2rem 0;">
        
        <h3>Réinitialiser le mot de passe</h3>
        <p>Remplissez ce champ uniquement si vous voulez changer le mot de passe.</p>
        <label for="new_password">Nouveau mot de passe:</label>
        <div class="password-wrapper">
            <input id="new_password" type="password" name="new_password">
            <span class="password-toggle-icon">
                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 10.224 7.29 6 12 6c4.708 0 8.577 4.224 9.964 5.683a1.012 1.012 0 010 .639C20.577 13.776 16.71 18 12 18s-8.577-4.224-9.964-5.683z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <svg class="icon-eye-slash hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 14.12 7.29 18 12 18c.99 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 6c4.708 0 8.577 4.224 9.964 5.683a1.012 1.012 0 010 .639c-1.143 1.44-3.345 3.9-6.071 5.492M9.879 9.879a3 3 0 104.242 4.242M9.879 9.879L6 6m12 12L6 6" /></svg>
            </span>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
<?php include 'footer.php'; ?>
