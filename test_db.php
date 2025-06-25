<?php
/**
 * Script de test de connexion - Application IPIRNET
 * À supprimer en production
 */

echo "<h2>Test de Connexion à la Base de Données</h2>";

try {
    require_once 'includes/db_connect.php';
    
    echo "<div style='color: green;'>✓ Connexion réussie à la base de données</div>";
    
    // Test de quelques requêtes
    $connection = getDBConnection();
    
    // Test table users
    $result = $connection->query("SELECT COUNT(*) as count FROM users");
    $count = $result->fetch_assoc()['count'];
    echo "<div>✓ Table users: {$count} utilisateurs</div>";
    
    // Test table filieres
    $result = $connection->query("SELECT COUNT(*) as count FROM filieres");
    $count = $result->fetch_assoc()['count'];
    echo "<div>✓ Table filieres: {$count} filières</div>";
    
    // Test table modules
    $result = $connection->query("SELECT COUNT(*) as count FROM modules");
    $count = $result->fetch_assoc()['count'];
    echo "<div>✓ Table modules: {$count} modules</div>";
    
    $connection->close();
    
    echo "<br><div style='color: blue;'>✓ Tous les tests sont réussis !</div>";
    echo "<br><a href='index.php'>→ Aller à l'application</a>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Erreur: " . $e->getMessage() . "</div>";
    echo "<br><div>Vérifiez votre configuration dans includes/db_connect.php</div>";
}
?>
