<?php
/**
 * Page de gestion des cours à distance - Application IPIRNET
 * Upload et téléchargement de fichiers de cours
 */

session_start();

// Vérification de l'authentification
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/db_connect.php';

$user = $_SESSION['user'];
$page_title = 'Cours à Distance - IPIRNET';
$base_path = '../';
$css_path = '../';

$success_message = '';
$error_message = '';

// Traitement de l'upload de fichier (formateurs et directeur)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'upload_cours') {
    if ($user['role'] == 'formateur' || $user['role'] == 'directeur') {
        $module_id = $_POST['module_id'] ?? '';
        $titre = trim($_POST['titre'] ?? '');
        
        if (!empty($module_id) && !empty($titre) && isset($_FILES['fichier'])) {
            $fichier = $_FILES['fichier'];
            
            // Vérifications du fichier
            $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar'];
            $max_size = 10 * 1024 * 1024; // 10 MB
            
            $file_extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
            
            if ($fichier['error'] == 0) {
                if (in_array($file_extension, $allowed_types)) {
                    if ($fichier['size'] <= $max_size) {
                        // Générer un nom de fichier unique
                        $filename = uniqid() . '_' . time() . '.' . $file_extension;
                        $upload_path = '../uploads/' . $filename;
                        
                        if (move_uploaded_file($fichier['tmp_name'], $upload_path)) {
                            // Enregistrer en base de données
                            $connection = getDBConnection();
                            $stmt = $connection->prepare("INSERT INTO cours_distance (module_id, titre, fichier, uploader_id) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("issi", $module_id, $titre, $filename, $user['id']);
                            
                            if ($stmt->execute()) {
                                $success_message = "Fichier uploadé avec succès.";
                            } else {
                                $error_message = "Erreur lors de l'enregistrement en base de données.";
                                unlink($upload_path); // Supprimer le fichier en cas d'erreur
                            }
                            
                            $stmt->close();
                            $connection->close();
                        } else {
                            $error_message = "Erreur lors de l'upload du fichier.";
                        }
                    } else {
                        $error_message = "Le fichier est trop volumineux (max: 10 MB).";
                    }
                } else {
                    $error_message = "Type de fichier non autorisé. Types acceptés: " . implode(', ', $allowed_types);
                }
            } else {
                $error_message = "Erreur lors de l'upload: " . $fichier['error'];
            }
        } else {
            $error_message = "Tous les champs sont obligatoires.";
        }
    }
}

// Traitement de la suppression de fichier (formateurs et directeur)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_cours') {
    if ($user['role'] == 'formateur' || $user['role'] == 'directeur') {
        $cours_id = $_POST['cours_id'] ?? '';
        
        if (!empty($cours_id)) {
            $connection = getDBConnection();
            
            // Récupérer les informations du cours
            $stmt = $connection->prepare("SELECT * FROM cours_distance WHERE id = ? AND uploader_id = ?");
            $stmt->bind_param("ii", $cours_id, $user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $cours = $result->fetch_assoc();
                
                // Supprimer le fichier
                $file_path = '../uploads/' . $cours['fichier'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                
                // Supprimer l'enregistrement
                $stmt = $connection->prepare("DELETE FROM cours_distance WHERE id = ?");
                $stmt->bind_param("i", $cours_id);
                
                if ($stmt->execute()) {
                    $success_message = "Cours supprimé avec succès.";
                } else {
                    $error_message = "Erreur lors de la suppression.";
                }
            } else {
                $error_message = "Cours non trouvé ou non autorisé.";
            }
            
            $stmt->close();
            $connection->close();
        }
    }
}

// Récupération des données selon le rôle
$connection = getDBConnection();

if ($user['role'] == 'directeur') {
    // Le directeur voit tous les cours
    $query = "
        SELECT cd.*, m.titre as module_titre, f.nom as filiere_nom,
               u.nom as uploader_nom, u.prenom as uploader_prenom
        FROM cours_distance cd
        JOIN modules m ON cd.module_id = m.id
        JOIN filieres f ON m.filiere_id = f.id
        JOIN users u ON cd.uploader_id = u.id
        ORDER BY cd.date_upload DESC
    ";
    $result = $connection->query($query);
    $cours_list = $result->fetch_all(MYSQLI_ASSOC);
    
    // Tous les modules pour l'upload
    $modules_query = "
        SELECT m.*, f.nom as filiere_nom
        FROM modules m
        JOIN filieres f ON m.filiere_id = f.id
        ORDER BY f.nom, m.sequence
    ";
    $modules_result = $connection->query($modules_query);
    $modules = $modules_result->fetch_all(MYSQLI_ASSOC);
    
} elseif ($user['role'] == 'formateur') {
    // Le formateur voit ses cours uploadés
    $stmt = $connection->prepare("
        SELECT cd.*, m.titre as module_titre, f.nom as filiere_nom
        FROM cours_distance cd
        JOIN modules m ON cd.module_id = m.id
        JOIN filieres f ON m.filiere_id = f.id
        WHERE cd.uploader_id = ?
        ORDER BY cd.date_upload DESC
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $cours_list = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Ses modules pour l'upload
    $stmt = $connection->prepare("
        SELECT m.*, f.nom as filiere_nom
        FROM modules m
        JOIN filieres f ON m.filiere_id = f.id
        WHERE m.formateur_id = ?
        ORDER BY f.nom, m.sequence
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $modules = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
} else {
    // Les stagiaires voient tous les cours disponibles
    $query = "
        SELECT cd.*, m.titre as module_titre, f.nom as filiere_nom,
               u.nom as uploader_nom, u.prenom as uploader_prenom
        FROM cours_distance cd
        JOIN modules m ON cd.module_id = m.id
        JOIN filieres f ON m.filiere_id = f.id
        JOIN users u ON cd.uploader_id = u.id
        ORDER BY cd.date_upload DESC
    ";
    $result = $connection->query($query);
    $cours_list = $result->fetch_all(MYSQLI_ASSOC);
}

$connection->close();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-2">
                    <?php if ($user['role'] == 'directeur'): ?>
                        Gestion des Cours à Distance
                    <?php elseif ($user['role'] == 'formateur'): ?>
                        Mes Cours à Distance
                    <?php else: ?>
                        Cours Disponibles
                    <?php endif; ?>
                </h1>
                <p class="text-muted mb-0">
                    <?php if ($user['role'] == 'directeur'): ?>
                        Vue d'ensemble de tous les cours à distance
                    <?php elseif ($user['role'] == 'formateur'): ?>
                        Gérez vos fichiers de cours pour l'enseignement à distance
                    <?php else: ?>
                        Téléchargez et consultez les cours mis à disposition
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($user['role'] == 'formateur' || $user['role'] == 'directeur'): ?>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-upload me-2"></i>Uploader un Cours
                </button>
            </div>
            <?php endif; ?>
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

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <i class="bi bi-file-earmark-arrow-up" style="font-size: 2rem; opacity: 0.8;"></i>
                <h3 class="mt-2"><?php echo count($cours_list); ?></h3>
                <p>
                    <?php if ($user['role'] == 'formateur'): ?>
                        Mes Cours
                    <?php else: ?>
                        Cours Disponibles
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    
    <?php if ($user['role'] == 'formateur' || $user['role'] == 'directeur'): ?>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <i class="bi bi-book-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                <h3 class="mt-2"><?php echo isset($modules) ? count($modules) : 0; ?></h3>
                <p>Modules Assignés</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <i class="bi bi-calendar-week" style="font-size: 2rem; opacity: 0.8;"></i>
                <h3 class="mt-2">
                    <?php 
                    $today_count = 0;
                    foreach ($cours_list as $cours) {
                        if (date('Y-m-d', strtotime($cours['date_upload'])) == date('Y-m-d')) {
                            $today_count++;
                        }
                    }
                    echo $today_count;
                    ?>
                </h3>
                <p>Uploadés Aujourd'hui</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <i class="bi bi-hdd" style="font-size: 2rem; opacity: 0.8;"></i>
                <h3 class="mt-2">
                    <?php 
                    $total_size = 0;
                    foreach ($cours_list as $cours) {
                        $file_path = '../uploads/' . $cours['fichier'];
                        if (file_exists($file_path)) {
                            $total_size += filesize($file_path);
                        }
                    }
                    echo round($total_size / (1024*1024), 1);
                    ?>
                </h3>
                <p>MB Utilisés</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Liste des cours -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>
                    <i class="bi bi-collection me-2"></i>
                    <?php if ($user['role'] == 'stagiaire'): ?>
                        Cours Disponibles au Téléchargement
                    <?php else: ?>
                        Cours Uploadés
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($cours_list)): ?>
                    <div class="row">
                        <?php foreach ($cours_list as $cours): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="card-title mb-0"><?php echo htmlspecialchars($cours['titre']); ?></h6>
                                        <span class="badge bg-primary">
                                            <?php echo strtoupper(pathinfo($cours['fichier'], PATHINFO_EXTENSION)); ?>
                                        </span>
                                    </div>
                                    
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="bi bi-book me-1"></i>
                                            <?php echo htmlspecialchars($cours['module_titre']); ?>
                                            <br>
                                            <i class="bi bi-collection me-1"></i>
                                            <?php echo htmlspecialchars($cours['filiere_nom']); ?>
                                        </small>
                                    </p>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            Uploadé le <?php echo date('d/m/Y à H:i', strtotime($cours['date_upload'])); ?>
                                        </small>
                                        <?php if ($user['role'] != 'stagiaire'): ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-person me-1"></i>
                                            par <?php echo htmlspecialchars($cours['uploader_prenom'] . ' ' . $cours['uploader_nom']); ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="../uploads/<?php echo htmlspecialchars($cours['fichier']); ?>" 
                                           class="btn btn-primary btn-sm" target="_blank">
                                            <i class="bi bi-download me-1"></i>Télécharger
                                        </a>
                                        
                                        <?php if (($user['role'] == 'formateur' && $cours['uploader_id'] == $user['id']) || $user['role'] == 'directeur'): ?>
                                        <form method="POST" action="" style="display: inline;" 
                                              onsubmit="return confirm('Supprimer ce cours ?')">
                                            <input type="hidden" name="action" value="delete_cours">
                                            <input type="hidden" name="cours_id" value="<?php echo $cours['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-light">
                                    <small class="text-muted">
                                        Taille: 
                                        <?php 
                                        $file_path = '../uploads/' . $cours['fichier'];
                                        if (file_exists($file_path)) {
                                            $size = filesize($file_path);
                                            if ($size > 1024*1024) {
                                                echo round($size / (1024*1024), 1) . ' MB';
                                            } else {
                                                echo round($size / 1024, 1) . ' KB';
                                            }
                                        } else {
                                            echo 'Fichier manquant';
                                        }
                                        ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination si nécessaire -->
                    <?php if (count($cours_list) > 12): ?>
                    <nav aria-label="Navigation des cours">
                        <ul class="pagination justify-content-center">
                            <li class="page-item"><a class="page-link" href="#">Précédent</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">Suivant</a></li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-cloud-slash text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">Aucun cours disponible</h5>
                        <p class="text-muted">
                            <?php if ($user['role'] == 'formateur'): ?>
                                Vous n'avez pas encore uploadé de cours.
                                <br>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="bi bi-cloud-upload me-2"></i>Uploader votre premier cours
                                </button>
                            <?php elseif ($user['role'] == 'directeur'): ?>
                                Aucun cours n'a été uploadé pour le moment.
                            <?php else: ?>
                                Aucun cours n'est disponible au téléchargement pour le moment.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($user['role'] == 'stagiaire' && !empty($cours_list)): ?>
<!-- Instructions pour les stagiaires -->
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-info">
            <h6><i class="bi bi-info-circle me-2"></i>Instructions pour les Stagiaires</h6>
            <ul class="mb-0">
                <li>Cliquez sur "Télécharger" pour obtenir le fichier de cours</li>
                <li>Les fichiers PDF peuvent être consultés directement dans votre navigateur</li>
                <li>Assurez-vous d'avoir les logiciels appropriés pour ouvrir les autres formats</li>
                <li>Contactez votre formateur en cas de problème d'accès</li>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Upload (Formateurs et Directeur) -->
<?php if ($user['role'] == 'formateur' || $user['role'] == 'directeur'): ?>
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-cloud-upload me-2"></i>Uploader un Cours
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="upload_cours">
                    
                    <div class="mb-3">
                        <label for="module_id" class="form-label">Module *</label>
                        <select class="form-select" id="module_id" name="module_id" required>
                            <option value="">-- Choisir un module --</option>
                            <?php if (isset($modules)): ?>
                                <?php foreach ($modules as $module): ?>
                                    <option value="<?php echo $module['id']; ?>">
                                        <?php echo htmlspecialchars($module['filiere_nom']); ?> - 
                                        Séq. <?php echo $module['sequence']; ?> - 
                                        <?php echo htmlspecialchars($module['titre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="titre" class="form-label">Titre du Cours *</label>
                        <input type="text" class="form-control" id="titre" name="titre" required 
                               placeholder="Ex: Cours 1 - Introduction aux concepts">
                    </div>
                    
                    <div class="mb-3">
                        <label for="fichier" class="form-label">Fichier *</label>
                        <input type="file" class="form-control" id="fichier" name="fichier" required>
                        <div class="form-text">
                            Types acceptés: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP, RAR<br>
                            Taille maximale: 10 MB
                        </div>
                    </div>
                    
                    <!-- Zone de drag & drop -->
                    <div class="file-upload-zone" id="dropZone">
                        <i class="bi bi-cloud-upload" style="font-size: 3rem; color: var(--ipirnet-light-blue);"></i>
                        <h6 class="mt-3 mb-2">Glissez-déposez votre fichier ici</h6>
                        <p class="text-muted mb-0">ou cliquez sur "Parcourir" ci-dessus</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-2"></i>Uploader le Cours
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Drag & Drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fichier');
    
    if (dropZone && fileInput) {
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                
                // Mettre à jour l'affichage
                const fileName = files[0].name;
                dropZone.innerHTML = `
                    <i class="bi bi-file-earmark-check" style="font-size: 3rem; color: var(--ipirnet-blue);"></i>
                    <h6 class="mt-3 mb-2">Fichier sélectionné</h6>
                    <p class="text-muted mb-0">${fileName}</p>
                `;
            }
        });
        
        dropZone.addEventListener('click', function() {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                dropZone.innerHTML = `
                    <i class="bi bi-file-earmark-check" style="font-size: 3rem; color: var(--ipirnet-blue);"></i>
                    <h6 class="mt-3 mb-2">Fichier sélectionné</h6>
                    <p class="text-muted mb-0">${fileName}</p>
                `;
            }
        });
    }
});
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
