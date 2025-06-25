-- Données supplémentaires pour la démonstration - Application IPIRNET
-- À exécuter après l'installation de base

USE groupe_ipirnet;

-- Ajout de données supplémentaires pour les tests

-- Insertion de modules supplémentaires pour la filière Développement Informatique
INSERT INTO modules (filiere_id, titre, sequence, duree_heures, opo, controle, formateur_id) VALUES
(1, 'Base de Données Avancées', 5, 70, 'OPO1: Concevoir des modèles de données complexes\nOPO2: Optimiser les requêtes SQL\nOPO3: Administrer une base de données\nOPO4: Implémenter la sécurité des données', 1, 3),
(1, 'Frameworks de Développement', 6, 90, 'OPO1: Maîtriser un framework PHP moderne\nOPO2: Développer une API REST\nOPO3: Implémenter l\'authentification JWT\nOPO4: Gérer les tests unitaires', 1, 2),
(1, 'Déploiement et DevOps', 7, 50, 'OPO1: Configurer un serveur de production\nOPO2: Automatiser le déploiement\nOPO3: Monitorer les applications\nOPO4: Gérer les sauvegardes', 1, 3),
(1, 'Projet de Fin d\'Études', 8, 120, 'OPO1: Analyser un besoin client\nOPO2: Concevoir une solution complète\nOPO3: Développer l\'application\nOPO4: Présenter le projet', 1, 2);

-- Insertion de modules pour la filière Réseaux Informatiques
INSERT INTO modules (filiere_id, titre, sequence, duree_heures, opo, controle, formateur_id) VALUES
(2, 'Sécurité des Réseaux', 2, 60, 'OPO1: Identifier les vulnérabilités réseau\nOPO2: Configurer des pare-feux\nOPO3: Mettre en place un VPN\nOPO4: Gérer les certificats SSL', 1, 3),
(2, 'Supervision et Monitoring', 3, 45, 'OPO1: Installer des outils de supervision\nOPO2: Configurer des alertes\nOPO3: Analyser les performances réseau', 0, 3),
(2, 'Virtualisation et Cloud', 4, 55, 'OPO1: Créer des machines virtuelles\nOPO2: Gérer l\'infrastructure cloud\nOPO3: Automatiser le provisioning', 1, 2);

-- Insertion de modules pour la filière Maintenance Informatique
INSERT INTO modules (filiere_id, titre, sequence, duree_heures, opo, controle, formateur_id) VALUES
(3, 'Maintenance Préventive', 2, 35, 'OPO1: Planifier la maintenance\nOPO2: Nettoyer les composants\nOPO3: Mettre à jour les pilotes\nOPO4: Documenter les interventions', 0, 2),
(3, 'Récupération de Données', 3, 40, 'OPO1: Utiliser des outils de récupération\nOPO2: Réparer des secteurs défaillants\nOPO3: Sauvegarder les données critiques', 1, 3),
(3, 'Support Utilisateur', 4, 30, 'OPO1: Gérer un helpdesk\nOPO2: Former les utilisateurs\nOPO3: Documenter les procédures', 0, 2);

-- Insertion de notes pour la démonstration
INSERT INTO notes (stagiaire_id, module_id, note, controle_effectue, formateur_id) VALUES
-- Notes pour stagiaire1 (Pierre BERNARD)
(4, 1, 15.5, 1, 2),  -- Introduction à la Programmation
(4, 2, 14.0, 1, 2),  -- Développement Web Frontend
(4, 3, 16.5, 1, 3),  -- Développement Web Backend
(4, 4, 13.0, 0, 2),  -- Gestion de Projet Informatique
(4, 9, 17.0, 1, 2),  -- Frameworks de Développement

-- Notes pour stagiaire2 (Sophie PETIT)
(5, 1, 12.5, 1, 2),  -- Introduction à la Programmation
(5, 2, 16.0, 1, 2),  -- Développement Web Frontend
(5, 3, 15.0, 1, 3),  -- Développement Web Backend
(5, 4, 14.5, 0, 2),  -- Gestion de Projet Informatique
(5, 5, 13.5, 1, 3);  -- Administration des Réseaux

-- Insertion de quelques cours à distance pour la démonstration
-- Note: Les fichiers physiques doivent être ajoutés manuellement dans le dossier uploads/
INSERT INTO cours_distance (module_id, titre, fichier, uploader_id) VALUES
(1, 'Cours 1 - Variables et Types de Données', 'demo_cours_variables.pdf', 2),
(1, 'Exercices Pratiques - Programmation de Base', 'demo_exercices_prog.pdf', 2),
(2, 'Présentation - HTML5 et CSS3', 'demo_html_css.pdf', 2),
(2, 'TP - Création d\'une Page Web', 'demo_tp_webpage.pdf', 2),
(3, 'Cours - Introduction à PHP', 'demo_php_intro.pdf', 3),
(3, 'Guide - Configuration XAMPP', 'demo_xampp_guide.pdf', 3),
(5, 'Manuel - Configuration Routeur', 'demo_routeur_config.pdf', 3),
(6, 'Guide - Réparation PC', 'demo_reparation_pc.pdf', 2);

-- Ajout d'utilisateurs supplémentaires pour les tests
INSERT INTO users (email, password, role, nom, prenom) VALUES
('formateur3@ipirnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'formateur', 'GARCIA', 'Carlos'),
('stagiaire3@ipirnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'stagiaire', 'MOREAU', 'Julie'),
('stagiaire4@ipirnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'stagiaire', 'SIMON', 'Thomas'),
('stagiaire5@ipirnet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'stagiaire', 'LAURENT', 'Emma');

-- Assignation de modules au nouveau formateur
UPDATE modules SET formateur_id = 8 WHERE id IN (8, 11, 13); -- Carlos GARCIA

-- Ajout de notes supplémentaires
INSERT INTO notes (stagiaire_id, module_id, note, controle_effectue, formateur_id) VALUES
-- Notes pour les nouveaux stagiaires
(9, 1, 11.0, 1, 2),   -- Julie MOREAU
(9, 2, 13.5, 1, 2),
(10, 1, 18.0, 1, 2),  -- Thomas SIMON
(10, 2, 17.5, 1, 2),
(10, 3, 16.0, 1, 3),
(11, 1, 14.5, 1, 2),  -- Emma LAURENT
(11, 2, 15.5, 1, 2);

-- Mise à jour des descriptions de filières
UPDATE filieres SET description = 'Formation complète en développement d\'applications web et mobiles, couvrant les technologies frontend et backend modernes.' WHERE id = 1;
UPDATE filieres SET description = 'Formation spécialisée en administration, sécurisation et maintenance des infrastructures réseau d\'entreprise.' WHERE id = 2;
UPDATE filieres SET description = 'Formation pratique en diagnostic, réparation et maintenance des équipements informatiques et systèmes.' WHERE id = 3;

-- Insertion d'une filière supplémentaire
INSERT INTO filieres (nom, description) VALUES
('Technicien en Cybersécurité', 'Formation spécialisée en sécurité informatique, protection des données et gestion des incidents de sécurité.');

-- Modules pour la nouvelle filière Cybersécurité
INSERT INTO modules (filiere_id, titre, sequence, duree_heures, opo, controle, formateur_id) VALUES
(4, 'Fondamentaux de la Cybersécurité', 1, 50, 'OPO1: Comprendre les enjeux de la cybersécurité\nOPO2: Identifier les menaces courantes\nOPO3: Appliquer les bonnes pratiques de sécurité', 1, 3),
(4, 'Tests d\'Intrusion', 2, 65, 'OPO1: Utiliser les outils de pentest\nOPO2: Identifier les vulnérabilités\nOPO3: Rédiger un rapport de sécurité', 1, 8),
(4, 'Gestion des Incidents', 3, 40, 'OPO1: Détecter les incidents de sécurité\nOPO2: Réagir aux attaques\nOPO3: Documenter et analyser les incidents', 1, 3),
(4, 'Cryptographie Appliquée', 4, 45, 'OPO1: Comprendre les algorithmes de chiffrement\nOPO2: Implémenter des solutions de chiffrement\nOPO3: Gérer les clés cryptographiques', 1, 8);

-- Statistiques finales
SELECT 
    'Filières' as Type, COUNT(*) as Total FROM filieres
UNION ALL
SELECT 
    'Modules' as Type, COUNT(*) as Total FROM modules  
UNION ALL
SELECT 
    'Utilisateurs' as Type, COUNT(*) as Total FROM users
UNION ALL
SELECT 
    'Cours à Distance' as Type, COUNT(*) as Total FROM cours_distance
UNION ALL
SELECT 
    'Notes' as Type, COUNT(*) as Total FROM notes;
