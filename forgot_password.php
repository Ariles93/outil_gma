<?php
require_once 'db.php';

$message = '';
$error = '';
$show_link_for_testing = false;
$test_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expires_at = date('Y-m-d H:i:s', time() + 3600); // Valide 1 heure

            $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
            $update->execute([$token_hash, $expires_at, $user['id']]);

            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
            $show_link_for_testing = true;
            $test_link = $reset_link;
        }
        $message = "Si un compte est associé à cette adresse, un lien de réinitialisation a été envoyé.";
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale-1">
<title>Mot de passe oublié - Gestion Matériel</title>

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
    <h2>Mot de passe oublié</h2>
    <p style="text-align: center; color: var(--color-text-secondary); margin-bottom: 1.5rem;">Entrez votre email pour recevoir les instructions.</p>

    <?php if ($message): ?><p class="alert alert-success"><?= e($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="alert alert-error"><?= e($error) ?></p><?php endif; ?>

    <?php if ($show_link_for_testing): ?>
        <div style="padding:10px; border:1px solid var(--color-warning); border-radius: var(--radius); margin: 15px 0; background-color: #FFFBEB;">
            <p style="margin:0; font-weight: 500;"><strong>Pour le test :</strong></p>
            <p style="margin:0; font-size: 0.9rem;">Le lien de réinitialisation est :<br>
            <a href="<?= e($test_link) ?>"><?= e($test_link) ?></a></p>
        </div>
    <?php endif; ?>

    <form method="post">
        <label for="email">Email:</label>
        <input id="email" type="email" name="email" required>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Envoyer le lien</button>
    </form>
    <p style="text-align:center; margin-top: 1.5rem;"><a href="login.php">Retour à la connexion</a></p>
</main>
</body>
</html>
