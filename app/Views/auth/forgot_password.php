<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mot de passe oublié - Gestion Matériel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>

<body class="login-page-body">

    <div class="login-container">
        <div class="text-center mb-4">
            <img src="<?= url('images/logo_crous.png') ?>" alt="Logo" style="height: 60px; margin-bottom: 1rem;">
            <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Récupération</h2>
            <p class="text-muted" style="font-size: 0.9rem;">Entrez votre email pour recevoir un lien de
                réinitialisation.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('forgot-password/send') ?>" method="post">
            <div class="form-group">
                <label for="email">Adresse Email</label>
                <input type="email" id="email" name="email" required placeholder="vous@exemple.com">
            </div>

            <button type="submit" class="btn btn-primary w-full">Envoyer le lien</button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= url('login') ?>" class="text-muted" style="font-size: 0.9rem;">Retour à la connexion</a>
        </div>
    </div>

</body>

</html>