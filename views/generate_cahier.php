<?php
/**
 * Génération du document cahier de texte - Application IPIRNET
 * Génère un document HTML imprimable du cahier de texte
 */

if (!isset($generate_filiere) || !isset($generate_modules)) {
    header('Location: cahier.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cahier de Texte - <?php echo htmlspecialchars($generate_filiere['nom']); ?></title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --ipirnet-blue: #0066CC;
            --ipirnet-dark-blue: #004499;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        
        .header-logo {
            font-size: 2rem;
            font-weight: bold;
            color: var(--ipirnet-blue);
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .document-title {
            background: linear-gradient(135deg, var(--ipirnet-blue), var(--ipirnet-dark-blue));
            color: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }
        
        .module-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            break-inside: avoid;
        }
        
        .module-header {
            background-color: var(--ipirnet-blue);
            color: white;
            padding: 1rem;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .module-content {
            padding: 1.5rem;
        }
        
        .opo-list {
            background-color: #f8f9fa;
            border-left: 4px solid var(--ipirnet-blue);
            padding: 1rem;
            margin: 1rem 0;
        }
        
        .controle-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .avec-controle {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .sans-controle {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .summary-table {
            margin-top: 2rem;
        }
        
        .footer-info {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid var(--ipirnet-blue);
            color: #6c757d;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .module-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            
            .document-title {
                background: var(--ipirnet-blue) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .module-header {
                background: var(--ipirnet-blue) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header-logo">
            <i class="bi bi-mortarboard-fill me-2"></i>
            GROUPE IPIRNET
        </div>
        
        <!-- Actions (cachées à l'impression) -->
        <div class="no-print mb-4 text-center">
            <button onclick="window.print()" class="btn btn-primary me-2">
                <i class="bi bi-printer me-2"></i>Imprimer
            </button>
            <a href="cahier.php?filiere_id=<?php echo $generate_filiere['id']; ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Retour
            </a>
        </div>
        
        <!-- Titre du document -->
        <div class="document-title">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="h2 mb-2">CAHIER DE TEXTE</h1>
                    <h2 class="h4 mb-3"><?php echo htmlspecialchars($generate_filiere['nom']); ?></h2>
                    <?php if (!empty($generate_filiere['description'])): ?>
                        <p class="mb-0 opacity-75"><?php echo htmlspecialchars($generate_filiere['description']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-white text-dark p-3 rounded">
                        <div class="h5 mb-1"><?php echo count($generate_modules); ?></div>
                        <div>Modules</div>
                        <hr class="my-2">
                        <div class="h5 mb-1">
                            <?php 
                            $total_heures = 0;
                            foreach ($generate_modules as $module) {
                                $total_heures += $module['duree_heures'];
                            }
                            echo $total_heures;
                            ?>
                        </div>
                        <div>Heures Total</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informations générales -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="bi bi-info-circle me-2"></i>Informations du Document
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Date de génération :</strong> <?php echo date('d/m/Y H:i'); ?></li>
                            <li><strong>Généré par :</strong> <?php echo htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']); ?></li>
                            <li><strong>Statut :</strong> Document officiel</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-success">
                            <i class="bi bi-graph-up me-2"></i>Statistiques
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Modules avec contrôle :</strong> 
                                <?php echo count(array_filter($generate_modules, function($m) { return $m['controle']; })); ?>
                            </li>
                            <li><strong>Modules sans contrôle :</strong> 
                                <?php echo count(array_filter($generate_modules, function($m) { return !$m['controle']; })); ?>
                            </li>
                            <li><strong>Durée moyenne :</strong> 
                                <?php echo $total_heures > 0 ? round($total_heures / count($generate_modules), 1) : 0; ?>h par module
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modules détaillés -->
        <h3 class="mb-4">
            <i class="bi bi-list-ol me-2"></i>Modules et Séquences Pédagogiques
        </h3>
        
        <?php foreach ($generate_modules as $index => $module): ?>
        <div class="module-card">
            <div class="module-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">
                            <span class="badge bg-light text-dark me-2">Séquence <?php echo $module['sequence']; ?></span>
                            <?php echo htmlspecialchars($module['titre']); ?>
                        </h4>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="h5 mb-0"><?php echo $module['duree_heures']; ?> heures</div>
                    </div>
                </div>
            </div>
            
            <div class="module-content">
                <!-- Informations du module -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6><i class="bi bi-person me-2"></i>Formateur Assigné</h6>
                        <p class="mb-0">
                            <?php if ($module['formateur_nom']): ?>
                                <?php echo htmlspecialchars($module['formateur_prenom'] . ' ' . $module['formateur_nom']); ?>
                            <?php else: ?>
                                <span class="text-muted">À définir</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-clipboard-check me-2"></i>Modalité de Contrôle</h6>
                        <span class="controle-badge <?php echo $module['controle'] ? 'avec-controle' : 'sans-controle'; ?>">
                            <?php if ($module['controle']): ?>
                                <i class="bi bi-check-circle me-1"></i>Avec contrôle en fin d'OPO
                            <?php else: ?>
                                <i class="bi bi-info-circle me-1"></i>Sans contrôle
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Objectifs Pédagogiques Opérationnels -->
                <h6><i class="bi bi-target me-2"></i>Objectifs Pédagogiques Opérationnels (OPO)</h6>
                <div class="opo-list">
                    <?php 
                    $opo_lines = explode("\n", trim($module['opo']));
                    foreach ($opo_lines as $line) {
                        if (!empty(trim($line))) {
                            echo '<div class="mb-2"><i class="bi bi-arrow-right me-2 text-primary"></i>' . htmlspecialchars(trim($line)) . '</div>';
                        }
                    }
                    ?>
                </div>
                
                <!-- Espace pour les notes du formateur -->
                <div class="mt-4 p-3 border rounded bg-light">
                    <h6 class="text-muted"><i class="bi bi-pencil me-2"></i>Notes du Formateur</h6>
                    <div style="min-height: 80px; border-bottom: 1px dotted #ccc; margin-bottom: 10px;"></div>
                    <div style="min-height: 40px; border-bottom: 1px dotted #ccc;"></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Tableau récapitulatif -->
        <div class="summary-table">
            <h3 class="mb-4">
                <i class="bi bi-table me-2"></i>Tableau Récapitulatif
            </h3>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th>Séquence</th>
                            <th>Module</th>
                            <th>Durée (h)</th>
                            <th>Contrôle</th>
                            <th>Formateur</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($generate_modules as $module): ?>
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?php echo $module['sequence']; ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($module['titre']); ?></td>
                            <td class="text-center"><?php echo $module['duree_heures']; ?>h</td>
                            <td class="text-center">
                                <?php if ($module['controle']): ?>
                                    <i class="bi bi-check-circle text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-dash-circle text-warning"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($module['formateur_nom']): ?>
                                    <?php echo htmlspecialchars($module['formateur_prenom'] . ' ' . $module['formateur_nom']); ?>
                                <?php else: ?>
                                    <span class="text-muted">À définir</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div style="width: 50px; height: 20px; border: 1px solid #ccc;"></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th colspan="2">TOTAL</th>
                            <th class="text-center"><?php echo $total_heures; ?>h</th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Signatures -->
        <div class="row mt-5">
            <div class="col-md-4">
                <div class="text-center p-3 border rounded">
                    <div class="fw-bold mb-3">Directeur du Centre</div>
                    <div style="height: 80px; border-bottom: 1px solid #ccc;"></div>
                    <small class="text-muted">Signature et cachet</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-3 border rounded">
                    <div class="fw-bold mb-3">Coordinateur Pédagogique</div>
                    <div style="height: 80px; border-bottom: 1px solid #ccc;"></div>
                    <small class="text-muted">Signature et date</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-3 border rounded">
                    <div class="fw-bold mb-3">Responsable Formation</div>
                    <div style="height: 80px; border-bottom: 1px solid #ccc;"></div>
                    <small class="text-muted">Signature et date</small>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer-info">
            <div class="row">
                <div class="col-md-8">
                    <p class="mb-1">
                        <strong>Groupe IPIRNET</strong> - Centre de Formation Professionnelle
                    </p>
                    <p class="mb-1">
                        Technicien Spécialisé en Développement Informatique
                    </p>
                    <p class="mb-0">
                        Document généré automatiquement le <?php echo date('d/m/Y à H:i'); ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <p class="mb-1">Page 1 sur 1</p>
                    <p class="mb-0">Version 1.0</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
