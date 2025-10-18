<?php
// protect.php
// Ce fichier doit être inclus au tout début de chaque page sécurisée.

// On vérifie si l'utilisateur est connecté. 
// La session a déjà été démarrée par db.php qui est inclus après.
if (!isset($_SESSION['user_id'])) {
    // On le redirige vers la page de connexion.
    header('Location: login.php');
    exit; // On arrête l'exécution du script.
}
?>
