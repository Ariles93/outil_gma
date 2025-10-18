<?php
// footer.php
?>
</main>
<footer>
  <small>© <?= date('Y') ?> - Gestion du Matériel du Parc du Crous de Versailles. Tous droits réservés.</small>
</footer>

<script>

  // --- SCRIPT AJOUTÉ POUR FORCER LA RÉACTUALISATION ---
window.addEventListener('pageshow', function(event) {
  // event.persisted est vrai si la page est chargée depuis le cache "précédent/suivant"
  if (event.persisted) {
    window.location.reload();
  }
});  
// Script pour le menu de navigation responsive
const navToggle = document.querySelector('.nav-toggle');
const navContainer = document.querySelector('.main-nav-container');

/*if (navToggle && mainNavContainer) {*/
navToggle.addEventListener('click', () => {
    // Ajoute ou retire la classe 'is-active' pour afficher/cacher le menu
    navToggle.classList.toggle('is-active');
    // Ajoute ou retire la classe 'is-active' pour animer le bouton
    navContainer.classList.toggle('is-active');
});

// --- SCRIPT MANQUANT POUR LA VISIBILITÉ DU MOT DE PASSE (AJOUTÉ ICI) ---
document.querySelectorAll('.password-toggle-icon').forEach(toggle => {
  toggle.addEventListener('click', () => {
    // On trouve les éléments importants : le conteneur, le champ et les deux icônes
    const wrapper = toggle.closest('.password-wrapper');
    const input = wrapper.querySelector('input');
    const eyeIcon = toggle.querySelector('.icon-eye');
    const eyeSlashIcon = toggle.querySelector('.icon-eye-slash');

    // On vérifie le type actuel du champ
    if (input.type === 'password') {
      // S'il est caché, on le passe en texte
      input.type = 'text';
      // On cache l'œil et on montre l'œil barré
      eyeIcon.classList.add('hidden');
      eyeSlashIcon.classList.remove('hidden');
    } else {
      // S'il est visible, on le repasse en password
      input.type = 'password';
      // On montre l'œil et on cache l'œil barré
      eyeIcon.classList.remove('hidden');
      eyeSlashIcon.classList.add('hidden');
    }
  });
});
// --- NOUVEAU SCRIPT POUR LES TRANSITIONS DE PAGE ---
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionne tous les liens qui ne sont pas des liens externes ou des ancres
    const internalLinks = document.querySelectorAll('a[href]:not([target="_blank"]):not([href^="#"])');

    internalLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            const url = this.href;

            // Ne pas intercepter les clics spéciaux (Ctrl+clic, etc.) 
            // ou les liens qui téléchargent des fichiers
            if (event.metaKey || event.ctrlKey || url.includes('.pdf')) {
                return;
            }

            // Empêche la navigation immédiate
            event.preventDefault();

            // Ajoute la classe pour démarrer l'animation de fondu
            document.body.classList.add('fade-out');

            // Attend la fin de l'animation avant de changer de page
            setTimeout(() => {
                window.location.href = url;
            }, 300); // 300ms, doit correspondre à la durée de la transition CSS
        });
    });
});
</script>
</body>
</html>