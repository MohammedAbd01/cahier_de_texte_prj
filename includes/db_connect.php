<?php
/**
 * Fichier de connexion à la base de données MySQL
 * Application de gestion de projet IPIRNET
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'groupe_ipirnet');
define('DB_USER', 'root');  // Modifier selon votre configuration XAMPP
define('DB_PASS', 'root');      // Modifier selon votre configuration XAMPP

// Fonction de connexion à la base de données
function getDBConnection() {
    try {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Vérification de la connexion
        if ($connection->connect_error) {
            throw new Exception("Erreur de connexion à la base de données: " . $connection->connect_error);
        }
        
        // Configuration de l'encodage
        $connection->set_charset("utf8mb4");
        
        return $connection;
    } catch (Exception $e) {
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}

// Fonction pour exécuter une requête sécurisée
function executeQuery($query, $params = [], $types = '') {
    $connection = getDBConnection();
    
    if (!empty($params)) {
        $stmt = $connection->prepare($query);
        if ($stmt === false) {
            die("Erreur de préparation de la requête: " . $connection->error);
        }
        
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        $result = $connection->query($query);
    }
    
    $connection->close();
    return $result;
}

// Fonction pour récupérer un utilisateur par email
function getUserByEmail($email) {
    $query = "SELECT * FROM users WHERE email = ?";
    $result = executeQuery($query, [$email], 's');
    return $result ? $result->fetch_assoc() : null;
}

// Fonction pour vérifier l'authentification
function verifyLogin($email, $password) {
    $user = getUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Fonction pour récupérer toutes les filières
function getAllFilieres() {
    $query = "SELECT * FROM filieres ORDER BY nom";
    $result = executeQuery($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Fonction pour récupérer les modules d'une filière
function getModulesByFiliere($filiere_id) {
    $query = "SELECT m.*, u.nom as formateur_nom, u.prenom as formateur_prenom 
              FROM modules m 
              LEFT JOIN users u ON m.formateur_id = u.id 
              WHERE m.filiere_id = ? 
              ORDER BY m.sequence";
    $result = executeQuery($query, [$filiere_id], 'i');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Fonction pour récupérer tous les formateurs
function getAllFormateurs() {
    $query = "SELECT * FROM users WHERE role = 'formateur' ORDER BY nom, prenom";
    $result = executeQuery($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Fonction pour récupérer tous les stagiaires
function getAllStagiaires() {
    $query = "SELECT * FROM users WHERE role = 'stagiaire' ORDER BY nom, prenom";
    $result = executeQuery($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Fonction pour vérifier les droits d'accès
function checkUserRole($required_roles) {
    if (!isset($_SESSION['user'])) {
        header('Location: ' . (strpos($_SERVER['REQUEST_URI'], '/views/') ? '../' : '') . 'login.php');
        exit();
    }
    
    if (!in_array($_SESSION['user']['role'], $required_roles)) {
        $_SESSION['error_message'] = "Accès non autorisé pour votre rôle.";
        header('Location: ' . (strpos($_SERVER['REQUEST_URI'], '/views/') ? '../' : '') . 'index.php');
        exit();
    }
}

// Fonction pour créer un utilisateur
function createUser($email, $password, $role, $nom, $prenom) {
    $connection = getDBConnection();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $connection->prepare("INSERT INTO users (email, password, role, nom, prenom) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $hashed_password, $role, $nom, $prenom);
    
    $result = $stmt->execute();
    $user_id = $connection->insert_id;
    
    $stmt->close();
    $connection->close();
    
    return $result ? $user_id : false;
}

// Fonction pour récupérer les statistiques générales
function getGeneralStats() {
    $connection = getDBConnection();
    $stats = [];
    
    // Nombre total d'utilisateurs par rôle
    $result = $connection->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    while ($row = $result->fetch_assoc()) {
        $stats['users'][$row['role']] = $row['count'];
    }
    
    // Nombre total de filières
    $result = $connection->query("SELECT COUNT(*) as count FROM filieres");
    $stats['filieres'] = $result->fetch_assoc()['count'];
    
    // Nombre total de modules
    $result = $connection->query("SELECT COUNT(*) as count FROM modules");
    $stats['modules'] = $result->fetch_assoc()['count'];
    
    // Nombre total de cours à distance
    $result = $connection->query("SELECT COUNT(*) as count FROM cours_distance");
    $stats['cours_distance'] = $result->fetch_assoc()['count'];
    
    // Nombre total de notes
    $result = $connection->query("SELECT COUNT(*) as count FROM notes");
    $stats['notes'] = $result->fetch_assoc()['count'];
    
    $connection->close();
    return $stats;
}

// Fonction pour logger les actions importantes
function logAction($user_id, $action, $details = '') {
    $connection = getDBConnection();
    
    $stmt = $connection->prepare("INSERT INTO action_logs (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $user_id, $action, $details);
    $stmt->execute();
    $stmt->close();
    $connection->close();
}

// Fonction pour nettoyer les données d'entrée
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
