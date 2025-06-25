# Documentation Technique - Application IPIRNET

## Architecture de l'Application

### Structure MVC Simplifiée
```
Application/
├── Model (includes/db_connect.php)
├── View (includes/header.php, footer.php, views/*.php)
├── Controller (logique métier dans chaque page)
```

### Base de Données

#### Schéma Relationnel
```sql
users (utilisateurs)
├── id (PK)
├── email (UNIQUE)
├── password (hashed)
├── role (enum: directeur, formateur, stagiaire)
├── nom, prenom
└── created_at

filieres (filières de formation)
├── id (PK)
├── nom
├── description
└── created_at

modules (modules pédagogiques)
├── id (PK)
├── filiere_id (FK → filieres.id)
├── titre
├── sequence (ordre dans la filière)
├── duree_heures
├── opo (objectifs pédagogiques)
├── controle (boolean)
├── formateur_id (FK → users.id)
└── created_at

cours_distance (fichiers de cours)
├── id (PK)
├── module_id (FK → modules.id)
├── titre
├── fichier (nom du fichier)
├── date_upload
└── uploader_id (FK → users.id)

notes (évaluations des stagiaires)
├── id (PK)
├── stagiaire_id (FK → users.id)
├── module_id (FK → modules.id)
├── note (decimal 0-20)
├── controle_effectue (boolean)
├── date_evaluation
└── formateur_id (FK → users.id)
```

#### Relations
- `filieres` 1→N `modules`
- `users(formateur)` 1→N `modules`
- `modules` 1→N `cours_distance`
- `users(stagiaire)` N→M `modules` (via `notes`)

### Sécurité

#### Authentification
- Sessions PHP sécurisées
- Mots de passe hachés avec `password_hash()`
- Vérification des rôles sur chaque page protégée

#### Protection des Données
- Requêtes préparées (prepared statements)
- Validation et échappement des entrées
- Protection CSRF (à implémenter si nécessaire)

#### Upload de Fichiers
- Vérification des types MIME
- Limitation de taille (10 MB)
- Noms de fichiers uniques
- Dossier uploads protégé (.htaccess)

### API Interne

#### Fonctions Principales (db_connect.php)

```php
// Authentification
getUserByEmail($email)
verifyLogin($email, $password)
checkUserRole($required_roles)

// Données de base
getAllFilieres()
getAllFormateurs()
getAllStagiaires()
getModulesByFiliere($filiere_id)

// Utilitaires
executeQuery($query, $params, $types)
sanitizeInput($data)
getGeneralStats()
```

### Pages et Fonctionnalités

#### Pages Publiques
- `login.php` - Authentification
- `404.php` - Erreur 404 personnalisée

#### Pages Protégées

**Directeur uniquement:**
- `views/cahier.php` - Gestion du cahier de texte
  - Création de filières
  - Ajout de modules
  - Génération de documents

**Directeur + Formateur:**
- `views/suivi.php` - Suivi des modules
  - Consultation des modules
  - Saisie des notes (formateur)
  - Vue d'ensemble (directeur)

- `views/distance.php` - Cours à distance
  - Upload de fichiers (formateur/directeur)
  - Téléchargement (tous)

**Tous les utilisateurs:**
- `index.php` - Dashboard personnalisé
- `logout.php` - Déconnexion

### Responsive Design

#### Bootstrap 5.3
- Grid système pour la responsive
- Composants: cards, modals, forms, tables
- Utilitaires pour l'espacement et les couleurs

#### Thème IPIRNET
```css
:root {
    --ipirnet-blue: #0066CC;
    --ipirnet-dark-blue: #004499;
    --ipirnet-light-blue: #3388FF;
}
```

### Optimisations

#### Performance
- Compression GZIP (.htaccess)
- Cache des ressources statiques
- Requêtes SQL optimisées

#### UX/UI
- Messages de feedback utilisateur
- Animations CSS subtiles
- Interface intuitive par rôle
- Drag & drop pour les uploads

### Déploiement

#### Environnement de Développement
```bash
# XAMPP requis
- Apache 2.4+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10+
```

#### Configuration Serveur
```apache
# .htaccess principal
- Protection des fichiers sensibles
- Compression GZIP
- Cache des ressources
- Limitation des uploads

# .htaccess uploads/
- Désactivation PHP
- Types de fichiers autorisés
- Limitation de taille
```

### Tests et Validation

#### Comptes de Test
```
Directeur:  directeur@ipirnet.com  / password123
Formateur:  formateur1@ipirnet.com / password123
Stagiaire:  stagiaire1@ipirnet.com / password123
```

#### Scénarios de Test

**Test Directeur:**
1. Connexion avec compte directeur
2. Création d'une nouvelle filière
3. Ajout de modules à la filière
4. Génération du document cahier
5. Consultation des statistiques globales

**Test Formateur:**
1. Connexion avec compte formateur
2. Consultation des modules assignés
3. Upload d'un fichier de cours
4. Saisie d'une note pour un stagiaire
5. Consultation du tableau de bord

**Test Stagiaire:**
1. Connexion avec compte stagiaire
2. Consultation des cours disponibles
3. Téléchargement d'un fichier
4. Consultation des notes
5. Navigation dans l'interface

### Maintenance

#### Logs et Monitoring
- Logs d'erreur Apache/PHP
- Logs d'accès pour le monitoring
- Espace disque (dossier uploads)

#### Sauvegarde
```sql
-- Base de données
mysqldump -u root -p groupe_ipirnet > backup_$(date +%Y%m%d).sql

-- Fichiers uploads
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/
```

#### Mises à Jour
1. Sauvegarde complète
2. Mise à jour des fichiers PHP
3. Exécution des scripts SQL de migration
4. Tests fonctionnels
5. Mise en production

### Extensions Possibles

#### Fonctionnalités Avancées
- Système de notifications
- Export PDF natif des documents
- API REST pour applications mobiles
- Intégration avec des outils externes (Moodle, etc.)
- Système de messagerie interne

#### Améliorations Techniques
- Cache Redis/Memcached
- CDN pour les ressources statiques
- Authentification OAuth2/LDAP
- Monitoring avancé (APM)
- Tests automatisés (PHPUnit)

### Conformité

#### Standards Web
- HTML5 sémantique
- CSS3 responsive
- Accessibilité WCAG 2.1 (niveau AA)
- Compatible navigateurs modernes

#### Sécurité
- Protection OWASP Top 10
- Chiffrement des mots de passe
- Validation côté serveur
- Échappement des sorties

---

**Note:** Cette documentation technique doit être maintenue à jour lors des évolutions de l'application.
