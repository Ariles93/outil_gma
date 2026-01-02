<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestion Matériel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= url('images/logo_crous.png') ?>">
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
    <script src="https://unpkg.com/htmx.org@1.9.10"
        integrity="sha384-D1Kt99CQMDuVetoL1lrYwg5t+9QdHe7NLX/SoJYkXDFfX37iInKRy5xLSi8nO7UC"
        crossorigin="anonymous"></script>
</head>

<body>

    <div class="app-layout">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="<?= url('/') ?>" class="brand-wrapper">
                    <img src="<?= url('images/logo_crous.png') ?>" alt="Logo">
                    <span>G-STOCK</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section-label">Menu Principal</div>

                <a href="<?= url('/') ?>"
                    class="nav-item <?= ($_SERVER['REQUEST_URI'] == url('/') || $_SERVER['REQUEST_URI'] == url('/dashboard')) ? 'active' : '' ?>">
                    Dashboard
                </a>

                <a href="<?= url('materials') ?>"
                    class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'materials') !== false ? 'active' : '' ?>">
                    Matériel
                </a>

                <a href="<?= url('agents') ?>"
                    class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'agents') !== false ? 'active' : '' ?>">
                    Agents
                </a>

                <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
                    <div class="nav-section-label">Gestion</div>
                    <a href="<?= url('assignments') ?>"
                        class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'assignments') !== false ? 'active' : '' ?>">
                        Attributions
                    </a>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <div class="nav-section-label">Administration</div>
                    <a href="<?= url('users') ?>"
                        class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'users') !== false ? 'active' : '' ?>">
                        Utilisateurs
                    </a>
                    <a href="<?= url('logs') ?>"
                        class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'logs') !== false ? 'active' : '' ?>">
                        Logs
                    </a>
                <?php endif; ?>

                <div class="nav-section-label">Autre</div>
                <a href="<?= url('trash') ?>"
                    class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'trash') !== false ? 'active' : '' ?>">
                    Corbeille
                </a>
            </nav>

            <div class="sidebar-footer">
                <div style="font-size: 0.75rem; color: #94A3B8; margin-bottom: 0.25rem;">&copy; <?= date('Y') ?> Crous
                    Versailles</div>
                <a href="<?= url('cgu') ?>"
                    style="font-size: 0.75rem; color: #64748B; text-decoration: underline;">Mentions Légales / BO</a>
            </div>
        </aside>

        <!-- PROPER MAIN CONTENT WRAPPER -->
        <div class="main-content">
            <!-- TOPBAR -->
            <header class="topbar">
                <div class="d-flex items-center gap-4">
                    <button class="toggle-sidebar-btn" aria-label="Toggle Sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <h1 class="topbar-title">
                        <?php
                        // Simple logic to show a title based on URL, purely visual
                        if (strpos($_SERVER['REQUEST_URI'], 'materials') !== false)
                            echo 'Inventaire Matériel';
                        elseif (strpos($_SERVER['REQUEST_URI'], 'agents') !== false)
                            echo 'Gestion Agents';
                        elseif (strpos($_SERVER['REQUEST_URI'], 'assignments') !== false)
                            echo 'Attributions';
                        elseif (strpos($_SERVER['REQUEST_URI'], 'users') !== false)
                            echo 'Utilisateurs';
                        else
                            echo 'Tableau de bord';
                        ?>
                    </h1>
                </div>

                <div class="user-menu">
                    <div class="text-right" style="line-height: 1.2;">
                        <div style="font-weight: 600;"><?= e($_SESSION['user_name'] ?? 'Invité') ?></div>
                        <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: capitalize;">
                            <?= e($_SESSION['user_role'] ?? 'Visiteur') ?>
                        </div>
                    </div>
                    <div class="user-avatar">
                        <?= substr(strtoupper($_SESSION['user_name'] ?? 'U'), 0, 1) ?>
                    </div>
                    <a href="<?= url('logout') ?>" title="Déconnexion"
                        style="color: var(--color-text-muted); margin-left: 0.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </a>
                </div>
            </header>

            <!-- PAGE CONTENT START -->
            <main class="page-wrapper">