<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe - Gestion Matériel</title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>

<body class="login-page-body">
    <div class="login-container" style="grid-template-columns: 1fr; max-width: 500px;">
        <div class="login-form-wrapper" style="width: 100%;">
            <main class="content-box">
                <h2 style="text-align: center; border: none;">Réinitialiser le mot de passe</h2>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <form action="<?= url('reset-password/update') ?>" method="post">
                    <input type="hidden" name="token" value="<?= e($token) ?>">

                    <div style="margin-bottom: 1rem;">
                        <label for="password">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Changer le mot
                        de passe</button>
                </form>
            </main>
        </div>
    </div>
</body>

</html>