<?php
require_once 'db.php';
require_once 'protect.php';
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gestion Matériel</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="images/logo_crous.png">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="glass-header">
    <div class="logo">
            <a href="index.php">
                <img src="images/logo_crous.png" alt="Logo">
                <span>Outil de Gestion du Matériels et d'agents</span>
            </a>
    </div>
    <div class="header-container">
        <div class="main-nav-container">
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Dashboard</a>
                <a href="search.php" class="nav-link">Agents</a>
                <a href="view_materials.php" class="nav-link">Matériel</a>
                
                <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
                    <a href="assign.php"class="nav-link">Attribution</a>
                <?php endif; ?>

                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="view_logs.php" class="nav-link">Journal d'audit</a>
                    <a href="manage_users.php" class="nav-link">Utilisateurs DSI</a>
                <?php endif;?>
                <a href="trash.php" class="nav-link">Corbeille</a>
                <a href="logout.php" class="logout-link" title="Se déconnecter">   
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="26" height="26" role="img" aria-label="Se déconnecter">
                        <style>
                            .ring { fill: none; stroke: currentColor; stroke-width: 7; stroke-linecap: round; stroke-linejoin: round; }
                            .bar  { fill: none; stroke: currentColor; stroke-width: 8; stroke-linecap: round; }
                        </style>
                            <!-- cercle -->
                        <path class="ring" d="M32 6 a26 26 0 1 1 0 52 a26 26 0 0 1 0-52"/>
                            <!-- barre verticale -->
                        <path class="bar" d="M32 14 L32 34"/>
                    </svg>
                </a>
            </nav>
        </div>
        <div class="header-actions">
            <button class="nav-toggle" aria-label="Ouvrir le menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>
<main>