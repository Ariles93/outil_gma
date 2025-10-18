<?php
require_once 'db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = false;
$user = null;

if (empty($token)) {
    $error = "Token manquant. Veuillez utiliser le lien envoyé par email.";
} else {
    $token_hash = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token_hash]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Ce lien est invalide ou a expiré. Veuillez faire une nouvelle demande.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || empty($password_confirm)) {
        $error = "Veuillez remplir les deux champs de mot de passe.";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $new_password_hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $update->execute([$new_password_hash, $user['id']]);
        $success = true;
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale-1">
<title>Réinitialiser le mot de passe - Gestion Matériel</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="icon" type="image/svg+xml" href="images/icone.svg">
<style>
    body { display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 2rem; box-sizing: border-box;}
    main { max-width: 400px; width: 100%; }
    h2 { text-align: center; }
</style>
</head>
<body>
<main class="content-box">
    <h2>Réinitialiser votre mot de passe</h2>
    
    <?php if ($success): ?>
        <p class="alert alert-success">Votre mot de passe a été mis à jour avec succès !</p>
        <a href="login.php" class="btn btn-primary" style="width: 100%; text-align: center;">Se connecter</a>
    <?php elseif ($error): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
        <a href="forgot_password.php" class="btn btn-secondary" style="width: 100%; text-align: center;">Faire une nouvelle demande</a>
    <?php else: ?>
        <form method="post">
            <label for="password">Nouveau mot de passe:</label>
            <input id="password" type="password" name="password" required>
            <label for="password_confirm">Confirmer le mot de passe:</label>
            <input id="password_confirm" type="password" name="password_confirm" required>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Mettre à jour</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
