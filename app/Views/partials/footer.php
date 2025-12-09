<?php
// app/Views/partials/footer.php
?>
</main>
<footer>
    <small>© <?= date('Y') ?> - Gestion du Matériel du Parc du Crous de Versailles. Tous droits réservés. | <a
            href="<?= url('cgu') ?>" style="color: inherit; text-decoration: underline;">Mentions Légales &
            CGU</a></small>
</footer>

<script>
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    const navToggle = document.querySelector('.nav-toggle');
    const navContainer = document.querySelector('.main-nav-container');

    if (navToggle) {
        navToggle.addEventListener('click', () => {
            navToggle.classList.toggle('is-active');
            navContainer.classList.toggle('is-active');
        });
    }

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

    document.addEventListener('DOMContentLoaded', function () {
        // Optional: Manual overrides if needed, but HTMX handles navigation now.
    });
</script>
</body>

</html>