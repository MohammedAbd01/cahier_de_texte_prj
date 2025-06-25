<?php
/**
 * Script d'installation automatique - Application IPIRNET
 * Permet d'installer l'application facilement
 */

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Étape 1: Vérification des prérequis
if ($step == 1) {
    $requirements = [
        'PHP Version' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'Extension MySQLi' => extension_loaded('mysqli'),
        'Extension JSON' => extension_loaded('json'),
        'Extension FileInfo' => extension_loaded('fileinfo'),
        'Dossier uploads/ writable' => is_writable('uploads/'),
    ];
}

// Étape 2: Configuration de la base de données
if ($step == 2 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? 'root'; // pour mamp
    $db_name = $_POST['db_name'] ?? 'groupe_ipirnet';
    
    try {
        $connection = new mysqli($db_host, $db_user, $db_pass);
        
        if ($connection->connect_error) {
            throw new Exception("Erreur de connexion: " . $connection->connect_error);
        }
        
        // Créer la base de données si elle n'existe pas
        $connection->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $connection->select_db($db_name);
        
        // Lire et exécuter le script SQL
        $sql_content = file_get_contents('groupe_ipirnet.sql');
        $sql_queries = explode(';', $sql_content);
        
        foreach ($sql_queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $connection->query($query);
            }
        }
        
        // Mettre à jour le fichier de configuration
        $config_content = "<?php\n";
        $config_content .= "define('DB_HOST', '$db_host');\n";
        $config_content .= "define('DB_NAME', '$db_name');\n";
        $config_content .= "define('DB_USER', '$db_user');\n";
        $config_content .= "define('DB_PASS', '$db_pass');\n";
        $config_content .= "?>";
        
        // Créer un fichier de configuration temporaire
        file_put_contents('includes/db_config_temp.php', $config_content);
        
        $connection->close();
        $success = "Base de données configurée avec succès !";
        $step = 3;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Étape 3: Finalisation
if ($step == 3 && isset($_POST['finalize'])) {
    // Remplacer le fichier de configuration
    if (file_exists('includes/db_config_temp.php')) {
        rename('includes/db_config_temp.php', 'includes/db_connect.php');
        $success = "Installation terminée avec succès !";
        $step = 4;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Application IPIRNET</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="css/custom.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center bg-primary text-white">
                        <h2><i class="bi bi-mortarboard-fill me-2"></i>Installation IPIRNET</h2>
                        <p class="mb-0">Assistant d'installation de l'application</p>
                    </div>
                    
                    <div class="card-body">
                        <!-- Progress bar -->
                        <div class="progress mb-4">
                            <div class="progress-bar" style="width: <?php echo ($step / 4) * 100; ?>%"></div>
                        </div>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($step == 1): ?>
                            <!-- Étape 1: Vérification des prérequis -->
                            <h4><i class="bi bi-list-check me-2"></i>Étape 1: Vérification des prérequis</h4>
                            
                            <div class="list-group mb-4">
                                <?php foreach ($requirements as $req => $status): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $req; ?>
                                        <?php if ($status): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-lg"></i> OK</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Manquant</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if (array_product($requirements)): ?>
                                <div class="text-center">
                                    <a href="?step=2" class="btn btn-primary">
                                        <i class="bi bi-arrow-right me-2"></i>Étape suivante
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <strong>Attention:</strong> Certains prérequis ne sont pas satisfaits. 
                                    Veuillez les corriger avant de continuer.
                                </div>
                            <?php endif; ?>
                            
                        <?php elseif ($step == 2): ?>
                            <!-- Étape 2: Configuration de la base de données -->
                            <h4><i class="bi bi-database me-2"></i>Étape 2: Configuration de la base de données</h4>
                            
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Hôte de la base de données</label>
                                            <input type="text" class="form-control" name="db_host" value="localhost" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nom de la base de données</label>
                                            <input type="text" class="form-control" name="db_name" value="groupe_ipirnet" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Utilisateur MySQL</label>
                                            <input type="text" class="form-control" name="db_user" value="root" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Mot de passe MySQL</label>
                                            <input type="password" class="form-control" name="db_pass" placeholder="Laissez vide si aucun">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-gear me-2"></i>Configurer la base de données
                                    </button>
                                </div>
                            </form>
                            
                        <?php elseif ($step == 3): ?>
                            <!-- Étape 3: Finalisation -->
                            <h4><i class="bi bi-check-circle me-2"></i>Étape 3: Finalisation</h4>
                            
                            <div class="alert alert-info">
                                <h6>Configuration terminée !</h6>
                                <p>La base de données a été créée et configurée avec succès.</p>
                                <ul>
                                    <li>Base de données: groupe_ipirnet</li>
                                    <li>Tables créées: users, filieres, modules, cours_distance, notes</li>
                                    <li>Données de démonstration importées</li>
                                </ul>
                            </div>
                            
                            <form method="POST">
                                <div class="text-center">
                                    <button type="submit" name="finalize" class="btn btn-success">
                                        <i class="bi bi-check-lg me-2"></i>Finaliser l'installation
                                    </button>
                                </div>
                            </form>
                            
                        <?php elseif ($step == 4): ?>
                            <!-- Étape 4: Installation terminée -->
                            <h4><i class="bi bi-trophy me-2"></i>Installation terminée !</h4>
                            
                            <div class="alert alert-success">
                                <h6>Félicitations !</h6>
                                <p>L'application IPIRNET a été installée avec succès.</p>
                            </div>
                            
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Comptes de test disponibles :</h6>
                                    <ul class="mb-0">
                                        <li><strong>Directeur:</strong> directeur@ipirnet.com / password123</li>
                                        <li><strong>Formateur:</strong> formateur1@ipirnet.com / password123</li>
                                        <li><strong>Stagiaire:</strong> stagiaire1@ipirnet.com / password123</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="index.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-arrow-right me-2"></i>Accéder à l'application
                                </a>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <small class="text-muted">
                                    N'oubliez pas de supprimer le fichier install.php pour des raisons de sécurité.
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
