<?php
/**
 * Page d'accueil - Application IPIRNET
 * Dashboard principal selon le rôle de l'utilisateur
 */

session_start();

// Vérification de l'authentification
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require_once 'includes/db_connect.php';

$user = $_SESSION['user'];
$page_title = 'Accueil - IPIRNET';

// Récupération des statistiques selon le rôle
$stats = [];

if ($user['role'] == 'directeur') {
    // Statistiques pour le directeur
    $connection = getDBConnection();
    
    // Nombre total de filières
    $result = $connection->query("SELECT COUNT(*) as total FROM filieres");
    $stats['filieres'] = $result->fetch_assoc()['total'];
    
    // Nombre total de modules
    $result = $connection->query("SELECT COUNT(*) as total FROM modules");
    $stats['modules'] = $result->fetch_assoc()['total'];
    
    // Nombre total d'utilisateurs
    $result = $connection->query("SELECT COUNT(*) as total FROM users");
    $stats['utilisateurs'] = $result->fetch_assoc()['total'];
    
    // Nombre de cours à distance
    $result = $connection->query("SELECT COUNT(*) as total FROM cours_distance");
    $stats['cours_distance'] = $result->fetch_assoc()['total'];
    
    // Modules récents
    $result = $connection->query("
        SELECT m.titre, f.nom as filiere, m.duree_heures, m.created_at,
               u.nom as formateur_nom, u.prenom as formateur_prenom
        FROM modules m 
        JOIN filieres f ON m.filiere_id = f.id 
        LEFT JOIN users u ON m.formateur_id = u.id
        ORDER BY m.created_at DESC 
        LIMIT 5
    ");
    $recent_modules = $result->fetch_all(MYSQLI_ASSOC);
    
    $connection->close();
    
} elseif ($user['role'] == 'formateur') {
    // Statistiques pour le formateur
    $connection = getDBConnection();
    
    // Mes modules
    $result = $connection->query("SELECT COUNT(*) as total FROM modules WHERE formateur_id = " . $user['id']);
    $stats['mes_modules'] = $result->fetch_assoc()['total'];
    
    // Mes cours à distance
    $result = $connection->query("SELECT COUNT(*) as total FROM cours_distance WHERE uploader_id = " . $user['id']);
    $stats['mes_cours'] = $result->fetch_assoc()['total'];
    
    // Stagiaires total
    $result = $connection->query("SELECT COUNT(*) as total FROM users WHERE role = 'stagiaire'");
    $stats['stagiaires'] = $result->fetch_assoc()['total'];
    
    // Mes modules avec détails
    $result = $connection->query("
        SELECT m.*, f.nom as filiere
        FROM modules m 
        JOIN filieres f ON m.filiere_id = f.id 
        WHERE m.formateur_id = " . $user['id'] . "
        ORDER BY m.sequence
    ");
    $mes_modules = $result->fetch_all(MYSQLI_ASSOC);
    
    $connection->close();
    
} else { // stagiaire
    // Statistiques pour le stagiaire
    $connection = getDBConnection();
    
    // Modules disponibles
    $result = $connection->query("SELECT COUNT(*) as total FROM modules");
    $stats['modules_disponibles'] = $result->fetch_assoc()['total'];
    
    // Cours à distance disponibles
    $result = $connection->query("SELECT COUNT(*) as total FROM cours_distance");
    $stats['cours_disponibles'] = $result->fetch_assoc()['total'];
    
    // Mes notes
    $result = $connection->query("SELECT COUNT(*) as total FROM notes WHERE stagiaire_id = " . $user['id']);
    $stats['mes_notes'] = $result->fetch_assoc()['total'];
    
    // Cours récents
    $result = $connection->query("
        SELECT cd.*, m.titre as module_titre, f.nom as filiere
        FROM cours_distance cd
        JOIN modules m ON cd.module_id = m.id
        JOIN filieres f ON m.filiere_id = f.id
        ORDER BY cd.date_upload DESC
        LIMIT 5
    ");
    $cours_recents = $result->fetch_all(MYSQLI_ASSOC);
    
    $connection->close();
}

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-2">Bienvenue, <?php echo htmlspecialchars($user['prenom']); ?> !</h1>
                <p class="text-muted mb-0">
                    <span class="badge bg-primary me-2"><?php echo ucfirst($user['role']); ?></span>
                    Tableau de bord - <?php echo date('d/m/Y'); ?>
                </p>
            </div>
            <div class="text-end">
                <i class="bi bi-mortarboard-fill text-primary" style="font-size: 3rem;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <?php if ($user['role'] == 'directeur'): ?>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-collection-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo $stats['filieres']; ?></h3>
                    <p>Filières</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-book-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo $stats['modules']; ?></h3>
                    <p>Modules</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo $stats['utilisateurs']; ?></h3>
                    <p>Utilisateurs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-cloud-download-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo $stats['cours_distance']; ?></h3>
                    <p>Cours à Distance</p>
                </div>
            </div>
        </div>
    <?php elseif ($user['role'] == 'formateur'): ?>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-book-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo $stats['mes_modules']; ?></h3>
                    <p>Mes Modules</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-cloud-upload-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo $stats['mes_cours']; ?></h3>
                    <p>Mes Cours</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo $stats['stagiaires']; ?></h3>
                    <p>Stagiaires</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-book-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo $stats['modules_disponibles']; ?></h3>
                    <p>Modules Disponibles</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-download" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo $stats['cours_disponibles']; ?></h3>
                    <p>Cours Disponibles</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo $stats['mes_notes']; ?></h3>
                    <p>Mes Notes</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Actions rapides -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-lightning-fill me-2"></i>Actions Rapides</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($user['role'] == 'directeur'): ?>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="views/cahier.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-journal-text d-block mb-2" style="font-size: 1.5rem;"></i>
                                Gérer le Cahier de Texte
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="views/suivi.php" class="btn btn-outline-success w-100">
                                <i class="bi bi-graph-up d-block mb-2" style="font-size: 1.5rem;"></i>
                                Suivi des Modules
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="views/distance.php" class="btn btn-outline-info w-100">
                                <i class="bi bi-cloud-arrow-up d-block mb-2" style="font-size: 1.5rem;"></i>
                                Cours à Distance
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button class="btn btn-outline-warning w-100" onclick="window.print()">
                                <i class="bi bi-printer d-block mb-2" style="font-size: 1.5rem;"></i>
                                Imprimer Dashboard
                            </button>
                        </div>
                    <?php elseif ($user['role'] == 'formateur'): ?>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="views/suivi.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-clipboard-check d-block mb-2" style="font-size: 1.5rem;"></i>
                                Gérer Mes Modules
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="views/distance.php" class="btn btn-outline-success w-100">
                                <i class="bi bi-cloud-upload d-block mb-2" style="font-size: 1.5rem;"></i>
                                Uploader des Cours
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="views/suivi.php?action=notes" class="btn btn-outline-info w-100">
                                <i class="bi bi-pencil-square d-block mb-2" style="font-size: 1.5rem;"></i>
                                Saisir des Notes
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="col-md-6 col-sm-6 mb-3">
                            <a href="views/distance.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-download d-block mb-2" style="font-size: 1.5rem;"></i>
                                Télécharger mes Cours
                            </a>
                        </div>
                        <div class="col-md-6 col-sm-6 mb-3">
                            <a href="views/suivi.php" class="btn btn-outline-success w-100">
                                <i class="bi bi-clipboard-data d-block mb-2" style="font-size: 1.5rem;"></i>
                                Consulter mes Notes
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contenu spécifique selon le rôle -->
<?php if ($user['role'] == 'directeur' && !empty($recent_modules)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-clock-history me-2"></i>Modules Récents</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Filière</th>
                                <th>Formateur</th>
                                <th>Durée</th>
                                <th>Créé le</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_modules as $module): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($module['titre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($module['filiere']); ?></td>
                                <td>
                                    <?php if ($module['formateur_nom']): ?>
                                        <?php echo htmlspecialchars($module['formateur_prenom'] . ' ' . $module['formateur_nom']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Non assigné</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $module['duree_heures']; ?>h</td>
                                <td><?php echo date('d/m/Y', strtotime($module['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($user['role'] == 'formateur' && !empty($mes_modules)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-person-workspace me-2"></i>Mes Modules Assignés</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($mes_modules as $module): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card module-card <?php echo $module['controle'] ? 'controle' : 'sans-controle'; ?>">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($module['titre']); ?></h6>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($module['filiere']); ?><br>
                                        Séquence <?php echo $module['sequence']; ?> - <?php echo $module['duree_heures']; ?>h
                                    </small>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge <?php echo $module['controle'] ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                        <?php echo $module['controle'] ? 'Avec contrôle' : 'Sans contrôle'; ?>
                                    </span>
                                    <a href="views/suivi.php?module=<?php echo $module['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        Gérer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($user['role'] == 'stagiaire' && !empty($cours_recents)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-download me-2"></i>Cours Récents Disponibles</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($cours_recents as $cours): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($cours['titre']); ?></h6>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($cours['module_titre']); ?><br>
                                        <?php echo htmlspecialchars($cours['filiere']); ?>
                                    </small>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($cours['date_upload'])); ?>
                                    </small>
                                    <a href="uploads/<?php echo htmlspecialchars($cours['fichier']); ?>" 
                                       class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bi bi-download me-1"></i>Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
