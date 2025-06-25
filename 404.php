<?php
/**
 * Page d'erreur 404 - Application IPIRNET
 */
$page_title = 'Page non trouvée - IPIRNET';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="css/custom.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 text-center">
                <div class="mb-4">
                    <i class="bi bi-mortarboard-fill text-primary" style="font-size: 4rem;"></i>
                    <h1 class="text-primary mt-3">GROUPE IPIRNET</h1>
                </div>
                
                <div class="card">
                    <div class="card-body p-5">
                        <h1 class="display-1 text-muted">404</h1>
                        <h3 class="mb-3">Page non trouvée</h3>
                        <p class="text-muted mb-4">
                            La page que vous recherchez n'existe pas ou a été déplacée.
                        </p>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="index.php" class="btn btn-primary">
                                <i class="bi bi-house-fill me-2"></i>Retour à l'accueil
                            </a>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <small class="text-muted">
                        &copy; <?php echo date('Y'); ?> Groupe IPIRNET - Tous droits réservés
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
