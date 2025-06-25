<?php
/**
 * Script de vérification post-installation - Application IPIRNET
 * Vérifie que l'installation est correcte et fonctionnelle
 */

session_start();

// Vérifier si l'utilisateur est connecté comme directeur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'directeur') {
    ?>
    <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 20px; border-radius: 5px;">
        <h3>Accès Restreint</h3>
        <p>Cette page de vérification n'est accessible qu'aux directeurs.</p>
        <a href="login.php" style="color: #721c24;">→ Se connecter</a>
    </div>
    <?php
    exit();
}

$errors = [];
$warnings = [];
$success = [];

// Vérification 1: Connexion à la base de données
try {
    require_once 'includes/db_connect.php';
    $connection = getDBConnection();
    $success[] = "✓ Connexion à la base de données réussie";
    
    // Vérification des tables
    $tables = ['users', 'filieres', 'modules', 'cours_distance', 'notes'];
    foreach ($tables as $table) {
        $result = $connection->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            $success[] = "✓ Table $table présente";
        } else {
            $errors[] = "✗ Table $table manquante";
        }
    }
    
    $connection->close();
} catch (Exception $e) {
    $errors[] = "✗ Erreur de connexion à la base de données: " . $e->getMessage();
}

// Vérification 2: Permissions des dossiers
$directories = [
    'uploads/' => 'Dossier des fichiers uploadés',
    'css/' => 'Dossier des styles CSS',
    'includes/' => 'Dossier des fichiers d\'inclusion',
    'views/' => 'Dossier des vues'
];

foreach ($directories as $dir => $description) {
    if (file_exists($dir)) {
        if (is_writable($dir)) {
            $success[] = "✓ $description ($dir) - Accessible en écriture";
        } else {
            $warnings[] = "⚠ $description ($dir) - Pas d'accès en écriture";
        }
    } else {
        $errors[] = "✗ $description ($dir) - Dossier manquant";
    }
}

// Vérification 3: Fichiers critiques
$critical_files = [
    'includes/db_connect.php' => 'Fichier de connexion DB',
    'includes/header.php' => 'En-tête commun',
    'includes/footer.php' => 'Pied de page commun',
    'css/custom.css' => 'Styles personnalisés',
    'login.php' => 'Page de connexion',
    'index.php' => 'Page d\'accueil'
];

foreach ($critical_files as $file => $description) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            $success[] = "✓ $description ($file) - Accessible en lecture";
        } else {
            $warnings[] = "⚠ $description ($file) - Pas d'accès en lecture";
        }
    } else {
        $errors[] = "✗ $description ($file) - Fichier manquant";
    }
}

// Vérification 4: Configuration PHP
$php_requirements = [
    'version' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'mysqli' => extension_loaded('mysqli'),
    'json' => extension_loaded('json'),
    'fileinfo' => extension_loaded('fileinfo'),
    'session' => extension_loaded('session')
];

foreach ($php_requirements as $req => $status) {
    if ($status) {
        $success[] = "✓ PHP $req - OK";
    } else {
        $errors[] = "✗ PHP $req - Manquant";
    }
}

// Vérification 5: Données de test
try {
    $connection = getDBConnection();
    
    // Compter les utilisateurs
    $result = $connection->query("SELECT COUNT(*) as count FROM users");
    $user_count = $result->fetch_assoc()['count'];
    
    if ($user_count >= 5) {
        $success[] = "✓ Données de test - $user_count utilisateurs présents";
    } else {
        $warnings[] = "⚠ Données de test - Seulement $user_count utilisateurs";
    }
    
    // Compter les modules
    $result = $connection->query("SELECT COUNT(*) as count FROM modules");
    $module_count = $result->fetch_assoc()['count'];
    
    if ($module_count >= 6) {
        $success[] = "✓ Données de test - $module_count modules présents";
    } else {
        $warnings[] = "⚠ Données de test - Seulement $module_count modules";
    }
    
    $connection->close();
} catch (Exception $e) {
    $errors[] = "✗ Erreur lors de la vérification des données: " . $e->getMessage();
}

// Vérification 6: Sécurité
$security_files = [
    '.htaccess' => 'Protection du dossier principal',
    'uploads/.htaccess' => 'Protection du dossier uploads'
];

foreach ($security_files as $file => $description) {
    if (file_exists($file)) {
        $success[] = "✓ Sécurité - $description";
    } else {
        $warnings[] = "⚠ Sécurité - $description manquant";
    }
}

// Vérification 7: Bootstrap et ressources externes
$external_resources = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'
];

foreach ($external_resources as $resource) {
    $headers = @get_headers($resource);
    if ($headers && strpos($headers[0], '200') !== false) {
        $success[] = "✓ Ressource externe accessible - " . basename($resource);
    } else {
        $warnings[] = "⚠ Ressource externe non accessible - " . basename($resource);
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Post-Installation - IPIRNET</title>
    
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
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header text-center bg-primary text-white">
                        <h2><i class="bi bi-shield-check me-2"></i>Vérification Post-Installation</h2>
                        <p class="mb-0">Diagnostic de l'application IPIRNET</p>
                    </div>
                    
                    <div class="card-body">
                        <!-- Résumé -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3><?php echo count($success); ?></h3>
                                        <p class="mb-0">Vérifications Réussies</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <h3><?php echo count($warnings); ?></h3>
                                        <p class="mb-0">Avertissements</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h3><?php echo count($errors); ?></h3>
                                        <p class="mb-0">Erreurs Critiques</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Erreurs Critiques -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>Erreurs Critiques</h5>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Avertissements -->
                        <?php if (!empty($warnings)): ?>
                            <div class="alert alert-warning">
                                <h5><i class="bi bi-exclamation-triangle me-2"></i>Avertissements</h5>
                                <ul class="mb-0">
                                    <?php foreach ($warnings as $warning): ?>
                                        <li><?php echo htmlspecialchars($warning); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Succès -->
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <h5><i class="bi bi-check-circle-fill me-2"></i>Vérifications Réussies</h5>
                                <div class="row">
                                    <?php foreach ($success as $i => $item): ?>
                                        <?php if ($i % 2 == 0): ?>
                                        <div class="col-md-6">
                                            <ul class="mb-0">
                                        <?php endif; ?>
                                                <li><?php echo htmlspecialchars($item); ?></li>
                                        <?php if ($i % 2 == 1 || $i == count($success) - 1): ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Status Global -->
                        <div class="text-center mt-4">
                            <?php if (empty($errors)): ?>
                                <div class="alert alert-success">
                                    <h4><i class="bi bi-trophy me-2"></i>Installation Réussie !</h4>
                                    <p>L'application IPIRNET est correctement installée et configurée.</p>
                                    <?php if (!empty($warnings)): ?>
                                        <small>Quelques avertissements sont présents mais ne compromettent pas le fonctionnement.</small>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <h4><i class="bi bi-x-circle me-2"></i>Installation Incomplète</h4>
                                    <p>Des erreurs critiques empêchent le bon fonctionnement de l'application.</p>
                                    <small>Veuillez corriger les erreurs ci-dessus avant de continuer.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Actions -->
                        <div class="text-center mt-4">
                            <div class="btn-group" role="group">
                                <a href="index.php" class="btn btn-primary">
                                    <i class="bi bi-house-fill me-2"></i>Accueil
                                </a>
                                <a href="test_db.php" class="btn btn-outline-primary">
                                    <i class="bi bi-database-check me-2"></i>Test DB
                                </a>
                                <button onclick="window.location.reload()" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Actualiser
                                </button>
                            </div>
                        </div>
                        
                        <!-- Informations Système -->
                        <div class="mt-5">
                            <h5><i class="bi bi-info-circle me-2"></i>Informations Système</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td><strong>Version PHP</strong></td>
                                            <td><?php echo PHP_VERSION; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Serveur Web</strong></td>
                                            <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Système d'Exploitation</strong></td>
                                            <td><?php echo PHP_OS; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Limite Mémoire</strong></td>
                                            <td><?php echo ini_get('memory_limit'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Limite Upload</strong></td>
                                            <td><?php echo ini_get('upload_max_filesize'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date de Vérification</strong></td>
                                            <td><?php echo date('d/m/Y H:i:s'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <small class="text-muted">
                                <i class="bi bi-shield-lock me-1"></i>
                                Supprimez ce fichier (check_installation.php) en production pour des raisons de sécurité.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
