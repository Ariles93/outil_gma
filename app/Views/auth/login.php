<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Connexion - Gestion Matériel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= url('images/logo_crous.png') ?>">
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>

<body class="login-page-body">

    <div class="login-container">
        <div class="text-center mb-4">
            <img src="<?= url('images/logo_crous.png') ?>" alt="Logo" style="height: 60px; margin-bottom: 1rem;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--color-text-main); margin: 0;">Gestion Matériel
            </h1>
            <p class="text-muted" style="margin-top: 0.5rem; font-size: 0.875rem;">Connectez-vous à votre espace</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="badge badge-danger w-full mb-4"
                style="border-radius: var(--radius-sm); padding: 0.75rem; justify-content: center;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url('login') ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" required placeholder="nom@crous-versailles.fr"
                    value="<?= isset($email) ? e($email) : '' ?>">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="password-wrapper" style="position: relative;">
                    <input id="password" type="password" name="password" required placeholder="••••••••">
                    <span class="password-toggle-icon"
                        style="position: absolute; right: 10px; top: 10px; cursor: pointer; color: var(--color-text-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-eye" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-eye-slash hidden" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                            </path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full" style="padding: 0.75rem;">Se connecter</button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= url('forgot-password') ?>" style="font-size: 0.875rem; color: var(--color-primary);">Mot de
                passe oublié ?</a>
        </div>
    </div>

    <script>
        // Simple inline script for toggle to stay self-contained or rely on footer logic if included.
        // Since this page doesn't include the main footer, we include the toggle logic here.
        document.querySelectorAll('.password-toggle-icon').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const input = toggle.previousElementSibling;
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