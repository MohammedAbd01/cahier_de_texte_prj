# Application de Gestion de Projet - Groupe IPIRNET

## Description
Application web de gestion de projet pour un centre de formation, développée pour le module "Technicien Spécialisé en Développement Informatique" du Groupe IPIRNET.

L'application permet la gestion complète d'un cahier de texte, le suivi des modules de formation, et l'enseignement à distance.

## Fonctionnalités Principales

### 🎯 Authentification Multi-Rôles
- **Directeur** : Gestion complète de l'application
- **Formateur** : Gestion des modules assignés et saisie des notes
- **Stagiaire** : Consultation des cours et téléchargement des contenus

### 📚 Gestion du Cahier de Texte
- Création et gestion des filières de formation
- Définition des modules avec leurs séquences
- Gestion des Objectifs Pédagogiques Opérationnels (OPO)
- Génération de documents PDF du cahier de texte
- Attribution des formateurs aux modules

### 📊 Suivi et Contrôle
- Tableau de bord personnalisé selon le rôle
- Suivi de la progression des modules
- Saisie et consultation des notes
- Statistiques détaillées
- Contrôles en fin d'OPO

### 🌐 Enseignement à Distance
- Upload de fichiers de cours (PDF, DOC, PPT, etc.)
- Interface de téléchargement pour les stagiaires
- Gestion des contenus par module
- Système de drag & drop pour l'upload

## Technologies Utilisées

- **Backend** : PHP natif (sans framework)
- **Frontend** : HTML5, Bootstrap 5.3, CSS3
- **Base de données** : MySQL
- **Icons** : Bootstrap Icons
- **Responsive Design** : Bootstrap Grid System

## Structure du Projet

```
projet_php_cahier_texte_101/
├── index.php                 # Page d'accueil / Dashboard
├── login.php                 # Page de connexion
├── logout.php                # Script de déconnexion
├── groupe_ipirnet.sql        # Script de création de la base de données
├── README.txt                # Ce fichier
├── css/
│   └── custom.css            # Styles personnalisés IPIRNET
├── includes/
│   ├── db_connect.php        # Connexion base de données
│   ├── header.php            # En-tête commun
│   └── footer.php            # Pied de page commun
├── views/
│   ├── cahier.php            # Gestion du cahier de texte
│   ├── generate_cahier.php   # Génération du document
│   ├── suivi.php             # Suivi des modules et notes
│   └── distance.php          # Gestion des cours à distance
└── uploads/                  # Dossier des fichiers uploadés
```

## Installation et Configuration

### Prérequis
- XAMPP (Apache + MySQL + PHP 7.4+)
- Navigateur web moderne
- Au moins 100 MB d'espace disque

### Étapes d'Installation

#### 1. Installation de XAMPP
1. Téléchargez XAMPP depuis https://www.apachefriends.org/
2. Installez XAMPP dans le dossier par défaut
3. Démarrez les services Apache et MySQL depuis le panneau de contrôle XAMPP

#### 2. Configuration du Projet
1. Copiez le dossier `projet_php_cahier_texte_101` dans le répertoire `htdocs` de XAMPP
   - Chemin par défaut : `C:\xampp\htdocs\` (Windows) ou `/Applications/XAMPP/htdocs/` (Mac)

2. Accédez à phpMyAdmin : http://localhost/phpmyadmin

3. Créez la base de données :
   - Cliquez sur "Importer"
   - Sélectionnez le fichier `groupe_ipirnet.sql`
   - Cliquez sur "Exécuter"

#### 3. Configuration de la Base de Données
Éditez le fichier `includes/db_connect.php` si nécessaire :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'groupe_ipirnet');
define('DB_USER', 'root');      // Votre utilisateur MySQL
define('DB_PASS', '');          // Votre mot de passe MySQL (vide par défaut)
```

#### 4. Configuration des Permissions
Assurez-vous que le dossier `uploads/` est accessible en écriture :
- Windows : Clic droit > Propriétés > Sécurité > Modifier les permissions
- Mac/Linux : `chmod 755 uploads/`

## Accès à l'Application

### URL d'Accès
http://localhost/projet_php_cahier_texte_101/

### Comptes de Démonstration

#### Directeur
- **Email** : directeur@ipirnet.com
- **Mot de passe** : password123
- **Accès** : Toutes les fonctionnalités

#### Formateur 1
- **Email** : formateur1@ipirnet.com
- **Mot de passe** : password123
- **Accès** : Gestion des modules assignés, upload de cours, saisie des notes

#### Formateur 2
- **Email** : formateur2@ipirnet.com
- **Mot de passe** : password123
- **Accès** : Gestion des modules assignés, upload de cours, saisie des notes

#### Stagiaire 1
- **Email** : stagiaire1@ipirnet.com
- **Mot de passe** : password123
- **Accès** : Consultation des cours, téléchargement des contenus

#### Stagiaire 2
- **Email** : stagiaire2@ipirnet.com
- **Mot de passe** : password123
- **Accès** : Consultation des cours, téléchargement des contenus

## Guide d'Utilisation

### Pour le Directeur
1. **Dashboard** : Vue d'ensemble des statistiques
2. **Cahier de Texte** : Créer des filières et modules
3. **Suivi des Modules** : Voir la progression globale
4. **Cours à Distance** : Gérer tous les contenus

### Pour les Formateurs
1. **Mes Modules** : Voir les modules assignés
2. **Saisie des Notes** : Évaluer les stagiaires
3. **Upload de Cours** : Partager du contenu à distance
4. **Suivi** : Voir les statistiques de ses modules

### Pour les Stagiaires
1. **Mes Cours** : Accéder aux contenus disponibles
2. **Mes Notes** : Consulter les évaluations
3. **Téléchargements** : Récupérer les fichiers de cours

## Fonctionnalités Techniques

### Sécurité
- Authentification par session PHP
- Hachage des mots de passe avec `password_hash()`
- Protection contre les injections SQL avec prepared statements
- Validation des types de fichiers uploadés
- Limitation de la taille des fichiers (10 MB max)

### Base de Données
```sql
-- Tables principales
users         # Utilisateurs (directeur, formateur, stagiaire)
filieres      # Filières de formation
modules       # Modules pédagogiques
cours_distance # Fichiers de cours à distance
notes         # Notes des stagiaires
```

### Upload de Fichiers
- Types acceptés : PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP, RAR
- Taille maximale : 10 MB par fichier
- Noms de fichiers uniques générés automatiquement
- Interface drag & drop pour une meilleure UX

## Résolution des Problèmes

### Problème de Connexion à la Base
- Vérifiez que MySQL est démarré dans XAMPP
- Contrôlez les paramètres dans `db_connect.php`
- Assurez-vous que la base `groupe_ipirnet` existe

### Erreur d'Upload de Fichiers
- Vérifiez les permissions du dossier `uploads/`
- Contrôlez la taille du fichier (max 10 MB)
- Vérifiez le type de fichier

### Page Blanche ou Erreur 500
- Activez l'affichage des erreurs PHP
- Vérifiez les logs d'erreur d'Apache
- Contrôlez la syntaxe PHP des fichiers

### Problèmes de Style CSS
- Videz le cache de votre navigateur (Ctrl+F5)
- Vérifiez que Bootstrap est bien chargé
- Contrôlez le chemin vers `custom.css`

## Maintenance et Sauvegarde

### Sauvegarde de la Base de Données
```bash
# Via phpMyAdmin : Exporter > SQL
# Ou en ligne de commande :
mysqldump -u root -p groupe_ipirnet > backup.sql
```

### Sauvegarde des Fichiers
Sauvegardez régulièrement :
- Le dossier `uploads/` (fichiers de cours)
- Les fichiers de configuration
- La base de données

### Mise à Jour
Pour mettre à jour l'application :
1. Sauvegardez vos données
2. Remplacez les fichiers PHP
3. Importez les modifications de base si nécessaire
4. Testez toutes les fonctionnalités

## Support et Contact

### Développement
Cette application a été développée dans le cadre du module "Technicien Spécialisé en Développement Informatique" pour répondre aux exigences du cahier des charges Groupe IPIRNET.

### Assistance Technique
Pour toute assistance :
1. Consultez ce README
2. Vérifiez les logs d'erreur
3. Testez avec les comptes de démonstration
4. Contactez l'administrateur système

---

**Version** : 1.0  
**Date de livraison** : 25 juin 2025  
**Développé pour** : Groupe IPIRNET  
**Module** : Technicien Spécialisé en Développement Informatique

*Application développée en PHP natif, HTML, Bootstrap et MySQL selon les spécifications du cahier des charges.*
