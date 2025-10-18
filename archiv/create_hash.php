<?php
// create_hash.php

// Mettez ici le mot de passe que vous voulez utiliser
$motDePasseEnClair = 'admin123';

// On génère le hash sécurisé
$hash = password_hash($motDePasseEnClair, PASSWORD_DEFAULT);

// On affiche le résultat
echo "Mot de passe en clair : " . htmlspecialchars($motDePasseEnClair) . "<br>";
echo "Copiez cette ligne pour votre base de données :<br>";
echo '<textarea rows="3" style="width: 100%; margin-top: 10px;">' . htmlspecialchars($hash) . '</textarea>';
?>
