<?php
/**
 * Page de connexion - Application IPIRNET
 * Authentification pour directeur, formateur et stagiaire
 */

session_start();

// Redirection si déjà connecté
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

require_once 'includes/db_connect.php';

$error_message = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = 'Veuillez remplir tous les champs.';
    } else {
        $user = verifyLogin($email, $password);
        if ($user) {
            $_SESSION['user'] = $user;
            $_SESSION['success_message'] = 'Connexion réussie ! Bienvenue ' . $user['prenom'] . '.';
            
            // Redirection selon le rôle
            switch ($user['role']) {
                case 'directeur':
                    header('Location: index.php');
                    break;
                case 'formateur':
                    header('Location: views/suivi.php');
                    break;
                case 'stagiaire':
                    header('Location: views/distance.php');
                    break;
                default:
                    header('Location: index.php');
            }
            exit();
        } else {
            $error_message = 'Email ou mot de passe incorrect.';
        }
    }
}

$page_title = 'Connexion - IPIRNET';
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
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-mortarboard-fill text-primary" style="font-size: 3rem;"></i>
                <h2>GROUPE IPIRNET</h2>
                <p>Connexion à la plateforme de gestion</p>
            </div>
            
            <div class="card-body p-4">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-1"></i>
                            Adresse email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               required placeholder="votre.email@ipirnet.com">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i>
                            Mot de passe
                        </label>
                        <input type="password" class="form-control" id="password" name="password" 
                               required placeholder="Votre mot de passe">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Se connecter
                    </button>
                </form>
                
                <!-- Comptes de démonstration -->
                <div class="mt-4">
                    <hr>
                    <h6 class="text-muted mb-3">Comptes de démonstration :</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body p-2">
                                    <small class="fw-bold text-primary">Directeur :</small><br>
                                    <small class="text-muted">directeur@ipirnet.com / password123</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body p-2">
                                    <small class="fw-bold text-success">Formateur :</small><br>
                                    <small class="text-muted">formateur1@ipirnet.com / password123</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body p-2">
                                    <small class="fw-bold text-info">Stagiaire :</small><br>
                                    <small class="text-muted">stagiaire1@ipirnet.com / password123</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer text-center text-muted p-3">
                <small>
                    &copy; <?php echo date('Y'); ?> Groupe IPIRNET - Tous droits réservés
                </small>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-fill demo credentials
        document.addEventListener('DOMContentLoaded', function() {
            const demoCards = document.querySelectorAll('.card.bg-light');
            demoCards.forEach(function(card) {
                card.style.cursor = 'pointer';
                card.addEventListener('click', function() {
                    const text = this.textContent;
                    const emailMatch = text.match(/(\S+@\S+)/);
                    if (emailMatch) {
                        document.getElementById('email').value = emailMatch[1];
                        document.getElementById('password').value = 'password123';
                    }
                });
            });
        });
    </script>
</body>
</html>
