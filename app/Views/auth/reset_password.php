<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Réinitialisation - Gestion Matériel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>

<body class="login-page-body">

    <div class="login-container">
        <div class="text-center mb-4">
            <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Nouveau mot de passe</h2>
            <p class="text-muted" style="font-size: 0.9rem;">Veuillez définir votre nouveau mot de passe.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('reset-password/update') ?>" method="post">
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <div class="form-group">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary w-full">Changer le mot de passe</button>
        </form>
    </div>

</body>

</html>