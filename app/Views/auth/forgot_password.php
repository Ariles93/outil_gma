<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Gestion Matériel</title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>

<body class="login-page-body">
    <div class="login-form-wrapper">
        <div class="login-container" style="grid-template-columns: 1fr; max-width: 500px;">
            <div class="login-form-wrapper" style="width: 100%;">
                <main class="content-box">
                    <h2 style="text-align: center; border: none;">Mot de passe oublié</h2>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-error"><?= e($error) ?></div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <form action="<?= url('forgot-password/send') ?>" method="post">
                        <div style="margin-bottom: 1rem;">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Envoyer le
                            lien</button>
                    </form>

                    <div style="text-align: center; margin-top: 1.5rem;">
                        <a href="<?= url('login') ?>">Retour à la connexion</a>
                    </div>
                </main>
            </div>
        </div>
    </div>
</body>

</html>