<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'IPIRNET - Gestion de Projet'; ?></title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="<?php echo isset($css_path) ? $css_path : ''; ?>css/custom.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php">
                <i class="bi bi-mortarboard-fill me-2"></i>
                GROUPE IPIRNET
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php">
                                <i class="bi bi-house-fill me-1"></i>Accueil
                            </a>
                        </li>
                        
                        <?php if ($_SESSION['user']['role'] == 'directeur'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/cahier.php">
                                    <i class="bi bi-journal-text me-1"></i>Cahier de Texte
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/suivi.php">
                                    <i class="bi bi-graph-up me-1"></i>Suivi des Modules
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['user']['role'] == 'formateur'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/suivi.php">
                                    <i class="bi bi-clipboard-check me-1"></i>Mes Modules
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/distance.php">
                                    <i class="bi bi-cloud-upload me-1"></i>Cours à Distance
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['user']['role'] == 'stagiaire'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/distance.php">
                                    <i class="bi bi-download me-1"></i>Mes Cours
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']); ?>
                                <span class="badge bg-light text-primary ms-1">
                                    <?php echo ucfirst($_SESSION['user']['role']); ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo isset($base_path) ? $base_path : ''; ?>logout.php">
                                    <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo isset($base_path) ? $base_path : ''; ?>login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Connexion
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
