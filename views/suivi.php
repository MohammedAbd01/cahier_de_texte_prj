<?php
/**
 * Page de suivi des modules - Application IPIRNET
 * Gestion des modules et saisie des notes
 */

session_start();

// Vérification de l'authentification
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/db_connect.php';

$user = $_SESSION['user'];
$page_title = 'Suivi des Modules - IPIRNET';
$base_path = '../';
$css_path = '../';

$success_message = '';
$error_message = '';

// Traitement de la saisie des notes (formateurs uniquement)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_note') {
    if ($user['role'] == 'formateur') {
        $stagiaire_id = $_POST['stagiaire_id'] ?? '';
        $module_id = $_POST['module_id'] ?? '';
        $note = $_POST['note'] ?? '';
        $controle_effectue = isset($_POST['controle_effectue']) ? 1 : 0;
        
        if (!empty($stagiaire_id) && !empty($module_id) && !empty($note)) {
            $connection = getDBConnection();
            
            // Vérifier si une note existe déjà
            $stmt = $connection->prepare("SELECT id FROM notes WHERE stagiaire_id = ? AND module_id = ? AND formateur_id = ?");
            $stmt->bind_param("iii", $stagiaire_id, $module_id, $user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Mise à jour
                $row = $result->fetch_assoc();
                $stmt = $connection->prepare("UPDATE notes SET note = ?, controle_effectue = ?, date_evaluation = NOW() WHERE id = ?");
                $stmt->bind_param("dii", $note, $controle_effectue, $row['id']);
            } else {
                // Insertion
                $stmt = $connection->prepare("INSERT INTO notes (stagiaire_id, module_id, note, controle_effectue, formateur_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iidii", $stagiaire_id, $module_id, $note, $controle_effectue, $user['id']);
            }
            
            if ($stmt->execute()) {
                $success_message = "Note enregistrée avec succès.";
            } else {
                $error_message = "Erreur lors de l'enregistrement de la note.";
            }
            
            $stmt->close();
            $connection->close();
        } else {
            $error_message = "Tous les champs obligatoires doivent être remplis.";
        }
    }
}

// Récupération des données selon le rôle
$connection = getDBConnection();

if ($user['role'] == 'directeur') {
    // Le directeur voit tous les modules
    $query = "
        SELECT m.*, f.nom as filiere_nom, 
               u.nom as formateur_nom, u.prenom as formateur_prenom,
               COUNT(n.id) as nb_notes
        FROM modules m 
        JOIN filieres f ON m.filiere_id = f.id 
        LEFT JOIN users u ON m.formateur_id = u.id 
        LEFT JOIN notes n ON m.id = n.module_id
        GROUP BY m.id
        ORDER BY f.nom, m.sequence
    ";
    $result = $connection->query($query);
    $modules = $result->fetch_all(MYSQLI_ASSOC);
    
} elseif ($user['role'] == 'formateur') {
    // Le formateur voit ses modules assignés
    $stmt = $connection->prepare("
        SELECT m.*, f.nom as filiere_nom,
               COUNT(n.id) as nb_notes
        FROM modules m 
        JOIN filieres f ON m.filiere_id = f.id 
        LEFT JOIN notes n ON m.id = n.module_id AND n.formateur_id = ?
        WHERE m.formateur_id = ?
        GROUP BY m.id
        ORDER BY f.nom, m.sequence
    ");
    $stmt->bind_param("ii", $user['id'], $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $modules = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Récupérer tous les stagiaires pour la saisie des notes
    $stagiaires_result = $connection->query("SELECT * FROM users WHERE role = 'stagiaire' ORDER BY nom, prenom");
    $stagiaires = $stagiaires_result->fetch_all(MYSQLI_ASSOC);
    
} else {
    // Les stagiaires voient leurs notes
    $stmt = $connection->prepare("
        SELECT m.*, f.nom as filiere_nom,
               u.nom as formateur_nom, u.prenom as formateur_prenom,
               n.note, n.controle_effectue, n.date_evaluation
        FROM modules m 
        JOIN filieres f ON m.filiere_id = f.id 
        LEFT JOIN users u ON m.formateur_id = u.id 
        LEFT JOIN notes n ON m.id = n.module_id AND n.stagiaire_id = ?
        ORDER BY f.nom, m.sequence
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $modules = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
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
                        Suivi des Modules
                    <?php elseif ($user['role'] == 'formateur'): ?>
                        Mes Modules
                    <?php else: ?>
                        Mes Notes et Évaluations
                    <?php endif; ?>
                </h1>
                <p class="text-muted mb-0">
                    <?php if ($user['role'] == 'directeur'): ?>
                        Vue d'ensemble de tous les modules et leur progression
                    <?php elseif ($user['role'] == 'formateur'): ?>
                        Gestion de vos modules assignés et saisie des notes
                    <?php else: ?>
                        Consultez vos notes et résultats d'évaluation
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($user['role'] == 'formateur'): ?>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                    <i class="bi bi-plus-circle me-2"></i>Saisir une Note
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
    <?php if ($user['role'] == 'directeur'): ?>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-book-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo count($modules); ?></h3>
                    <p>Total Modules</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2">
                        <?php echo count(array_filter($modules, function($m) { return $m['controle']; })); ?>
                    </h3>
                    <p>Avec Contrôle</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-data" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2">
                        <?php echo array_sum(array_column($modules, 'nb_notes')); ?>
                    </h3>
                    <p>Notes Saisies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-clock-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2">
                        <?php echo array_sum(array_column($modules, 'duree_heures')); ?>
                    </h3>
                    <p>Total Heures</p>
                </div>
            </div>
        </div>
    <?php elseif ($user['role'] == 'formateur'): ?>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-book-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo count($modules); ?></h3>
                    <p>Mes Modules</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-data" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2">
                        <?php echo array_sum(array_column($modules, 'nb_notes')); ?>
                    </h3>
                    <p>Notes Saisies</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2"><?php echo isset($stagiaires) ? count($stagiaires) : 0; ?></h3>
                    <p>Stagiaires</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="col-md-6 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check-fill" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2">
                        <?php echo count(array_filter($modules, function($m) { return !is_null($m['note']); })); ?>
                    </h3>
                    <p>Notes Reçues</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up" style="font-size: 2rem; opacity: 0.8;"></i>
                    <h3 class="mt-2">
                        <?php 
                        $notes = array_filter(array_column($modules, 'note'), function($n) { return !is_null($n); });
                        echo count($notes) > 0 ? number_format(array_sum($notes) / count($notes), 1) : '0.0';
                        ?>
                    </h3>
                    <p>Moyenne</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Liste des modules -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>
                    <i class="bi bi-list-ul me-2"></i>
                    <?php if ($user['role'] == 'directeur'): ?>
                        Tous les Modules
                    <?php elseif ($user['role'] == 'formateur'): ?>
                        Modules Assignés
                    <?php else: ?>
                        Mes Évaluations
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($modules)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Séq.</th>
                                    <th>Module</th>
                                    <th>Filière</th>
                                    <th>Durée</th>
                                    <?php if ($user['role'] == 'directeur'): ?>
                                        <th>Formateur</th>
                                        <th>Notes</th>
                                    <?php elseif ($user['role'] == 'formateur'): ?>
                                        <th>Contrôle</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    <?php else: ?>
                                        <th>Formateur</th>
                                        <th>Note</th>
                                        <th>Évalué le</th>
                                    <?php endif; ?>
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
                                        <br>
                                        <button class="btn btn-sm btn-outline-info mt-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#opoModal<?php echo $module['id']; ?>">
                                            <i class="bi bi-eye me-1"></i>Voir OPO
                                        </button>
                                    </td>
                                    <td><?php echo htmlspecialchars($module['filiere_nom']); ?></td>
                                    <td><?php echo $module['duree_heures']; ?>h</td>
                                    
                                    <?php if ($user['role'] == 'directeur'): ?>
                                        <td>
                                            <?php if ($module['formateur_nom']): ?>
                                                <?php echo htmlspecialchars($module['formateur_prenom'] . ' ' . $module['formateur_nom']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Non assigné</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $module['nb_notes']; ?></span>
                                        </td>
                                    <?php elseif ($user['role'] == 'formateur'): ?>
                                        <td>
                                            <?php if ($module['controle']): ?>
                                                <span class="badge bg-success">Avec contrôle</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Sans contrôle</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $module['nb_notes']; ?></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="openNoteModal(<?php echo $module['id']; ?>, '<?php echo htmlspecialchars($module['titre'], ENT_QUOTES); ?>')">
                                                <i class="bi bi-plus-circle me-1"></i>Note
                                            </button>
                                        </td>
                                    <?php else: ?>
                                        <td>
                                            <?php if ($module['formateur_nom']): ?>
                                                <?php echo htmlspecialchars($module['formateur_prenom'] . ' ' . $module['formateur_nom']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Non assigné</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($module['note'] !== null): ?>
                                                <span class="badge <?php echo $module['note'] >= 10 ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo number_format($module['note'], 1); ?>/20
                                                </span>
                                                <?php if ($module['controle_effectue']): ?>
                                                    <i class="bi bi-check-circle text-success ms-1" title="Contrôle effectué"></i>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">Pas de note</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($module['date_evaluation']): ?>
                                                <?php echo date('d/m/Y', strtotime($module['date_evaluation'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">Aucun module trouvé</h5>
                        <p class="text-muted">
                            <?php if ($user['role'] == 'formateur'): ?>
                                Aucun module ne vous est actuellement assigné.
                            <?php else: ?>
                                Aucun module disponible pour le moment.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
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
                <p class="text-muted"><?php echo htmlspecialchars($module['filiere_nom']); ?> - Séquence <?php echo $module['sequence']; ?></p>
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

<!-- Modal Saisie Note (Formateurs uniquement) -->
<?php if ($user['role'] == 'formateur'): ?>
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Saisir une Note
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_note">
                    
                    <div class="mb-3">
                        <label for="module_id" class="form-label">Module *</label>
                        <select class="form-select" id="module_id" name="module_id" required>
                            <option value="">-- Choisir un module --</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?php echo $module['id']; ?>">
                                    Séq. <?php echo $module['sequence']; ?> - <?php echo htmlspecialchars($module['titre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stagiaire_id" class="form-label">Stagiaire *</label>
                        <select class="form-select" id="stagiaire_id" name="stagiaire_id" required>
                            <option value="">-- Choisir un stagiaire --</option>
                            <?php if (isset($stagiaires)): ?>
                                <?php foreach ($stagiaires as $stagiaire): ?>
                                    <option value="<?php echo $stagiaire['id']; ?>">
                                        <?php echo htmlspecialchars($stagiaire['prenom'] . ' ' . $stagiaire['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="note" class="form-label">Note (sur 20) *</label>
                        <input type="number" class="form-control" id="note" name="note" 
                               min="0" max="20" step="0.1" required placeholder="15.5">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="controle_effectue" name="controle_effectue" value="1">
                            <label class="form-check-label" for="controle_effectue">
                                <strong>Contrôle effectué</strong>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Enregistrer la Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openNoteModal(moduleId, moduleTitle) {
    document.getElementById('module_id').value = moduleId;
    const modal = new bootstrap.Modal(document.getElementById('addNoteModal'));
    modal.show();
}
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
