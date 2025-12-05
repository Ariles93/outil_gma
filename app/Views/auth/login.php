<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Connexion - Gestion Matériel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="images/icone.svg">
    <link rel="stylesheet" href="css/style.css">

</head>

<body class="login-page-body">

    <div class="login-container">
        <div class="login-branding">
            <div class="logos">
                <img src="images/logo.svg" alt="Logo" class="login-logo">
                <img src="images/icone.svg" alt="Logo" class="login-logo">
            </div>
            <h2>Gestion Matériel du Parc</h2>
            <p>Connectez-vous pour accéder à votre espace de gestion.</p>
        </div>

        <div class="login-form-wrapper">
            <main class="content-box">
                <h3>Connexion</h3>
                <?php if (!empty($error)): ?>
                    <p class="alert alert-error"><?= e($error) ?></p>
                <?php endif; ?>
                <form method="post" action="<?= url('login') ?>">
                    <label for="email">Adresse Email:</label>
                    <input id="email" type="email" name="email" required placeholder="vous@exemple.com"
                        value="<?= isset($email) ? e($email) : '' ?>" style="box-shadow: 0 2px 4px rgba(0,0,0,0.15)">

                    <label for="password">Mot de passe:</label>
                    <div class="password-wrapper">
                        <input id="password" type="password" name="password" required
                            style="box-shadow: 0 2px 4px rgba(0,0,0,0.15)">
                        <span class="password-toggle-icon">
                            <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 10.224 7.29 6 12 6c4.708 0 8.577 4.224 9.964 5.683a1.012 1.012 0 010 .639C20.577 13.776 16.71 18 12 18s-8.577-4.224-9.964-5.683z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg class="icon-eye-slash hidden" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 14.12 7.29 18 12 18c.99 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 6c4.708 0 8.577 4.224 9.964 5.683a1.012 1.012 0 010 .639c-1.143 1.44-3.345 3.9-6.071 5.492M9.879 9.879a3 3 0 104.242 4.242M9.879 9.879L6 6m12 12L6 6" />
                            </svg>
                        </span>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Se connecter</button>
                </form>
                <p style="text-align:center; margin-top: 1.5rem; font-size: 1rem;">
                    <a href="<?= url('forgot-password') ?>">Mot de passe oublié ?</a>
                </p>
            </main>
        </div>
    </div>

    <script>
        document.querySelectorAll('.password-toggle-icon').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const wrapper = toggle.closest('.password-wrapper');
                const input = wrapper.querySelector('input');
                const eyeIcon = toggle.querySelector('.icon-eye');
                const eyeSlashIcon = toggle.querySelector('.icon-eye-slash');

                if (input.type === 'password') {
                    input.type = 'text';
                    eyeIcon.classList.add('hidden');
                    eyeSlashIcon.classList.remove('hidden');
                } else {
                    input.type = 'password';
                    eyeIcon.classList.remove('hidden');
                    eyeSlashIcon.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html>