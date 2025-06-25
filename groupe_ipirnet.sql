-- Base de données pour l'application de gestion de projet IPIRNET
-- Création de la base de données
CREATE DATABASE IF NOT EXISTS groupe_ipirnet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE groupe_ipirnet;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('directeur', 'formateur', 'stagiaire') NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des filières
CREATE TABLE filieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des modules
CREATE TABLE modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filiere_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    sequence INT NOT NULL,
    duree_heures INT NOT NULL,
    opo TEXT NOT NULL,
    controle BOOLEAN DEFAULT FALSE,
    formateur_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE CASCADE,
    FOREIGN KEY (formateur_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table des cours à distance
CREATE TABLE cours_distance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    fichier VARCHAR(255) NOT NULL,
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploader_id INT NOT NULL,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    FOREIGN KEY (uploader_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des notes des stagiaires
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stagiaire_id INT NOT NULL,
    module_id INT NOT NULL,
    note DECIMAL(4,2),
    controle_effectue BOOLEAN DEFAULT FALSE,
    date_evaluation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    formateur_id INT NOT NULL,
    FOREIGN KEY (stagiaire_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    FOREIGN KEY (formateur_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Données initiales
-- Insertion des utilisateurs par défaut
INSERT INTO users (email, password, role, nom, prenom) VALUES
('directeur@ipirnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'directeur', 'ADMIN', 'Directeur'),
('formateur1@ipirnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'formateur', 'MARTIN', 'Jean'),
('formateur2@ipirnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'formateur', 'DURAND', 'Marie'),
('stagiaire1@ipirnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'stagiaire', 'BERNARD', 'Pierre'),
('stagiaire2@ipirnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'stagiaire', 'PETIT', 'Sophie');

-- Insertion des filières
INSERT INTO filieres (nom, description) VALUES
('Technicien Spécialisé en Développement Informatique', 'Formation complète en développement informatique'),
('Technicien en Réseaux Informatiques', 'Formation spécialisée en réseaux et systèmes'),
('Technicien en Maintenance Informatique', 'Formation en maintenance et support technique');

-- Insertion des modules exemples
INSERT INTO modules (filiere_id, titre, sequence, duree_heures, opo, controle, formateur_id) VALUES
(1, 'Introduction à la Programmation', 1, 40, 'OPO1: Maîtriser les concepts de base de la programmation\nOPO2: Comprendre les structures de données fondamentales', 1, 2),
(1, 'Développement Web Frontend', 2, 60, 'OPO1: Créer des interfaces web responsives\nOPO2: Maîtriser HTML5, CSS3 et JavaScript', 1, 2),
(1, 'Développement Web Backend', 3, 80, 'OPO1: Développer des applications serveur\nOPO2: Gérer les bases de données\nOPO3: Sécuriser les applications web', 1, 3),
(1, 'Gestion de Projet Informatique', 4, 30, 'OPO1: Appliquer les méthodologies agiles\nOPO2: Planifier et suivre un projet', 0, 2),
(2, 'Administration des Réseaux', 1, 50, 'OPO1: Configurer les équipements réseau\nOPO2: Dépanner les problèmes de connectivité', 1, 3),
(3, 'Diagnostic et Réparation', 1, 45, 'OPO1: Identifier les pannes matérielles\nOPO2: Effectuer les réparations nécessaires', 1, 2);

-- Mot de passe par défaut pour tous les comptes : "password123"
-- Hash généré avec password_hash('password123', PASSWORD_DEFAULT)
