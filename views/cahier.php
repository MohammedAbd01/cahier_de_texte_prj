<?php
/**
 * Page de gestion du cahier de texte - Application IPIRNET
 * Permet de créer et gérer les filières, modules et séquences
 */

session_start();

// Vérification de l'authentification et des droits
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'directeur') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/db_connect.php';

$page_title = 'Gestion du Cahier de Texte - IPIRNET';
$base_path = '../';
$css_path = '../';

// Traitement des formulaires
$success_message = '';
$error_message = '';

// Ajout d'une nouvelle filière
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_filiere') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (!empty($nom)) {
        $connection = getDBConnection();
        $stmt = $connection->prepare("INSERT INTO filieres (nom, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $nom, $description);
        
        if ($stmt->execute()) {
            $success_message = "Filière ajoutée avec succès.";
        } else {
            $error_message = "Erreur lors de l'ajout de la filière.";
        }
        
        $stmt->close();
        $connection->close();
    } else {
        $error_message = "Le nom de la filière est obligatoire.";
    }
}

// Ajout d'un nouveau module
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_module') {
    $filiere_id = $_POST['filiere_id'] ?? '';
    $titre = trim($_POST['titre'] ?? '');
    $sequence = $_POST['sequence'] ?? '';
    $duree_heures = $_POST['duree_heures'] ?? '';
    $opo = trim($_POST['opo'] ?? '');
    $controle = isset($_POST['controle']) ? 1 : 0;
    $formateur_id = !empty($_POST['formateur_id']) ? $_POST['formateur_id'] : null;
    
    if (!empty($filiere_id) && !empty($titre) && !empty($sequence) && !empty($duree_heures) && !empty($opo)) {
        $connection = getDBConnection();
        $stmt = $connection->prepare("INSERT INTO modules (filiere_id, titre, sequence, duree_heures, opo, controle, formateur_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiisii", $filiere_id, $titre, $sequence, $duree_heures, $opo, $controle, $formateur_id);
        
        if ($stmt->execute()) {
            $success_message = "Module ajouté avec succès.";
        } else {
            $error_message = "Erreur lors de l'ajout du module.";
        }
        
        $stmt->close();
        $connection->close();
    } else {
        $error_message = "Tous les champs obligatoires doivent être remplis.";
    }
}

// Génération du document cahier de texte
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'generate_document') {
    $filiere_id = $_POST['filiere_id'] ?? '';
    
    if (!empty($filiere_id)) {
        // Redirection vers la page de génération
        header("Location: cahier.php?generate=1&filiere_id=" . $filiere_id);
        exit();
    }
}

// Récupération des données
$connection = getDBConnection();

// Toutes les filières
$filieres = getAllFilieres();

// Tous les formateurs
$formateurs = getAllFormateurs();

// Filière sélectionnée pour affichage
$selected_filiere_id = $_GET['filiere_id'] ?? ($_POST['filiere_id'] ?? '');
$selected_filiere = null;
$modules = [];

if (!empty($selected_filiere_id)) {
    foreach ($filieres as $f) {
        if ($f['id'] == $selected_filiere_id) {
            $selected_filiere = $f;
            break;
        }
    }
    $modules = getModulesByFiliere($selected_filiere_id);
}

$connection->close();

// Génération du document si demandé
if (isset($_GET['generate']) && $_GET['generate'] == '1' && !empty($_GET['filiere_id'])) {
    $generate_filiere_id = $_GET['filiere_id'];
    $generate_modules = getModulesByFiliere($generate_filiere_id);
    $generate_filiere = null;
    
    foreach ($filieres as $f) {
        if ($f['id'] == $generate_filiere_id) {
            $generate_filiere = $f;
            break;
        }
    }
    
    // Générer le document HTML
    include 'generate_cahier.php';
    exit();
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-2">Gestion du Cahier de Texte</h1>
                <p class="text-muted mb-0">
                    Créer et gérer les filières, modules et séquences pédagogiques
                </p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFiliereModal">
                    <i class="bi bi-plus-circle me-2"></i>Nouvelle Filière
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Sélection de la filière -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-collection me-2"></i>Sélectionner une Filière</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-8">
                            <select name="filiere_id" class="form-select" required onchange="this.form.submit()">
                                <option value="">-- Choisir une filière --</option>
                                <?php foreach ($filieres as $filiere): ?>
                                    <option value="<?php echo $filiere['id']; ?>" 
                                            <?php echo ($selected_filiere_id == $filiere['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($filiere['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <?php if ($selected_filiere): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="action" value="generate_document">
                                    <input type="hidden" name="filiere_id" value="<?php echo $selected_filiere['id']; ?>">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-file-earmark-text me-2"></i>Générer Document
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($selected_filiere): ?>
<!-- Informations de la filière sélectionnée -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <?php echo htmlspecialchars($selected_filiere['nom']); ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-2">
                            <strong>Description :</strong> 
                            <?php echo !empty($selected_filiere['description']) ? htmlspecialchars($selected_filiere['description']) : 'Aucune description'; ?>
                        </p>
                        <p class="mb-0">
                            <strong>Nombre de modules :</strong> 
                            <span class="badge bg-primary"><?php echo count($modules); ?></span>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter un Module
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des modules -->
<?php if (!empty($modules)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-list-ol me-2"></i>Modules et Séquences</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Séq.</th>
                                <th>Titre du Module</th>
                                <th>Durée (h)</th>
                                <th>OPO</th>
                                <th>Contrôle</th>
                                <th>Formateur</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $module): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $module['sequence']; ?></span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($module['titre']); ?></strong>
                                </td>
                                <td><?php echo $module['duree_heures']; ?>h</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#opoModal<?php echo $module['id']; ?>">
                                        <i class="bi bi-eye me-1"></i>Voir OPO
                                    </button>
                                </td>
                                <td>
                                    <?php if ($module['controle']): ?>
                                        <span class="badge bg-success">Avec contrôle</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Sans contrôle</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($module['formateur_nom']): ?>
                                        <?php echo htmlspecialchars($module['formateur_prenom'] . ' ' . $module['formateur_nom']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Non assigné</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" title="Supprimer" 
                                                onclick="if(confirm('Supprimer ce module ?')) { /* TODO: Ajax delete */ }">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour afficher les OPO -->
<?php foreach ($modules as $module): ?>
<div class="modal fade" id="opoModal<?php echo $module['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-target me-2"></i>
                    Objectifs Pédagogiques Opérationnels
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6><strong><?php echo htmlspecialchars($module['titre']); ?></strong></h6>
                <hr>
                <pre class="bg-light p-3 rounded"><?php echo htmlspecialchars($module['opo']); ?></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php else: ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>
            Aucun module défini pour cette filière. 
            <button class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                <i class="bi bi-plus-circle me-2"></i>Ajouter le premier module
            </button>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Modal Ajout Filière -->
<div class="modal fade" id="addFiliereModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Nouvelle Filière
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_filiere">
                    
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom de la filière *</label>
                        <input type="text" class="form-control" id="nom" name="nom" required 
                               placeholder="Ex: Technicien Spécialisé en Développement Informatique">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Description détaillée de la filière..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Créer la Filière
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajout Module -->
<?php if ($selected_filiere): ?>
<div class="modal fade" id="addModuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Nouveau Module
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_module">
                    <input type="hidden" name="filiere_id" value="<?php echo $selected_filiere['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="titre" class="form-label">Titre du Module *</label>
                                <input type="text" class="form-control" id="titre" name="titre" required 
                                       placeholder="Ex: Introduction à la Programmation">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="sequence" class="form-label">Séquence *</label>
                                <input type="number" class="form-control" id="sequence" name="sequence" required 
                                       min="1" value="<?php echo count($modules) + 1; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="duree_heures" class="form-label">Durée (heures) *</label>
                                <input type="number" class="form-control" id="duree_heures" name="duree_heures" required 
                                       min="1" placeholder="40">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="formateur_id" class="form-label">Formateur assigné</label>
                                <select class="form-select" name="formateur_id">
                                    <option value="">-- Aucun formateur --</option>
                                    <?php foreach ($formateurs as $formateur): ?>
                                        <option value="<?php echo $formateur['id']; ?>">
                                            <?php echo htmlspecialchars($formateur['prenom'] . ' ' . $formateur['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="opo" class="form-label">Objectifs Pédagogiques Opérationnels (OPO) *</label>
                        <textarea class="form-control" id="opo" name="opo" rows="5" required 
                                  placeholder="OPO1: Premier objectif pédagogique&#10;OPO2: Deuxième objectif pédagogique&#10;..."></textarea>
                        <div class="form-text">
                            Listez les objectifs pédagogiques (ex: OPO1, OPO2, etc.)
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="controle" name="controle" value="1">
                            <label class="form-check-label" for="controle">
                                <strong>Avec contrôle en fin d'OPO</strong>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Créer le Module
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
