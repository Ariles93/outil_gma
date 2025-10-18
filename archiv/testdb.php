<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gestion_materiel;charset=utf8mb4', 'monuser', 'monpass');
    echo "Connexion OK !";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
