<?php
// logout.php
session_start(); // On doit démarrer la session pour y accéder

// On détruit toutes les variables de session
$_SESSION = [];

// On détruit la session elle-même
session_destroy();

// On redirige l'utilisateur vers la page de connexion
header('Location: login.php?status=logout');
exit;
?>
