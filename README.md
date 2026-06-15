# SUNUJOB ÉTUDIANT
## Plateforme numérique de missions temporaires pour étudiants sénégalais

---

## 📁 Structure du projet

```
sunujob-etudiant/
├── assets/
│   ├── css/
│   │   ├── variables.css          # Variables CSS (palette couleurs)
│   │   └── style.css              # Styles globaux Bootstrap custom
│   ├── js/
│   │   └── main.js                # JavaScript principal
│   └── images/
│       └── logo.png               # Logo officiel (à ajouter)
│
├── includes/
│   ├── db.php                     # Connexion base de données
│   ├── auth.php                   # Fonctions authentification
│   ├── header.php                 # Navbar commune
│   └── footer.php                 # Footer commun
│
├── pages/
│   ├── etudiant/
│   │   ├── dashboard.php          # Dashboard étudiant
│   │   ├── mes-candidatures.php   # Liste candidatures
│   │   └── profil.php             # Profil étudiant
│   └── recruteur/
│       ├── dashboard.php          # Dashboard recruteur
│       ├── mes-missions.php       # Liste des missions
│       ├── ajouter-mission.php    # Publier une mission
│       ├── modifier-mission.php   # Modifier une mission
│       ├── candidatures.php       # Gérer candidatures
│       └── profil.php             # Profil recruteur
│
├── uploads/
│   ├── photos/                    # Photos de profil
│   ├── cv/                        # CV des étudiants
│   └── logos/                      # Logos entreprises
│
├── database/
│   └── sunujob_db.sql             # Script SQL complet
│
├── index.php                      # Page d'accueil
├── login.php                      # Connexion
├── register.php                   # Inscription
├── logout.php                     # Déconnexion
├── missions.php                   # Liste des missions
├── mission-detail.php             # Détail d'une mission
├── categories.php                 # Liste catégories
├── forgot-password.php            # Mot de passe oublié
├── reset-password.php             # Réinitialisation
├── a-propos.php                   # À propos
├── contact.php                    # Contact
└── mentions-legales.php           # Mentions légales
```

---

## 🚀 Installation

### 1. Prérequis
- Serveur web (Apache, Nginx)
- PHP 8.x ou supérieur
- MySQL 8.x ou MariaDB
- Extensions PHP : mysqli, gd, fileinfo

### 2. Importer la base de données
1. Ouvrir phpMyAdmin ou votre outil MySQL
2. Créer une nouvelle base de données `sunujob_db`
3. Importer le fichier `database/sunujob_db.sql`

### 3. Configurer la connexion
Modifier le fichier `includes/db.php` si nécessaire :
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Votre utilisateur MySQL
define('DB_PASS', '');             // Votre mot de passe
define('DB_NAME', 'sunujob_db');
```

### 4. Placer les fichiers
- Copier tous les fichiers dans le dossier `htdocs` ou `www` de votre serveur
- Ajouter le logo `logo.png` dans `assets/images/`
- Donner les permissions d'écriture au dossier `uploads/`

### 5. Tester
Accédez à `http://localhost/sunujob/index.php`

---

## 🎨 Palette de couleurs

| Nom | Code HEX | Usage |
|-----|----------|-------|
| Bleu Marine | #1B3F72 | Principal |
| Bleu Moyen | #2E6DB4 | Liens, icônes |
| Vert Émeraude | #2D9B4E | Succès, rémunération |
| Orange Vif | #F5A623 | CTA, highlights |
| Blanc Cassé | #F5F4F0 | Fond |

---

## ✅ Fonctionnalités

### Étudiants
- Inscription et connexion
- Gestion du profil (CV, photo, compétences)
- Recherche de missions avec filtres
- Postulation aux missions
- Suivi des candidatures

### Recruteurs
- Inscription et connexion
- Gestion du profil entreprise
- Publication de missions
- Modification et suppression de missions
- Gestion des candidatures (accepter/refuser)

### Administrateur
- Dashboard avec statistiques
- Notifications automatiques
- Système de récupération mot de passe

---

## 📋 Technologies

- **Frontend** : HTML5, Bootstrap 5.3, CSS3
- **Backend** : PHP 8.x (procédural avec MySQLi)
- **Base de données** : MySQL 8.x
- **Icônes** : Font Awesome 6.x
- **Police** : Poppins (Google Fonts)

---

## 🔒 Sécurité

- Requêtes préparées (anti-injection SQL)
- Mots de passe hashés (bcrypt)
- Validation côté serveur
- Protection CSRF sur formulaires
- Sessions sécurisées

---

## 📧 Contact

- Email : contact@sunujob.sn
- Téléphone : +221 77 123 45 67
- Adresse : Dakar, Sénégal

---

## 📝 Licence

Projet de fin de formation - Usage éducatif

---

**Développé avec ❤️ au Sénégal**
