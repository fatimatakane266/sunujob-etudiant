# BASE DE DONNÉES — SUNUJOB ÉTUDIANT
### Modélisation des tables et relations (MySQL)

---

## 1. SCHÉMA DES TABLES

---

### 🗂️ Table `utilisateurs`
> Stocke tous les comptes (étudiants et recruteurs)

| Colonne | Type | Contrainte | Description |
|---|---|---|---|
| `id` | INT | PK, AUTO_INCREMENT | Identifiant unique |
| `nom` | VARCHAR(100) | NOT NULL | Nom de l'utilisateur |
| `prenom` | VARCHAR(100) | NOT NULL | Prénom de l'utilisateur |
| `email` | VARCHAR(150) | NOT NULL, UNIQUE | Adresse email |
| `mot_de_passe` | VARCHAR(255) | NOT NULL | Mot de passe hashé |
| `telephone` | VARCHAR(20) | NULL | Numéro de téléphone |
| `role` | ENUM('etudiant', 'recruteur', 'admin') | NOT NULL | Rôle de l'utilisateur — `admin` gère la plateforme (voir §5) |
| `photo` | VARCHAR(255) | NULL | Photo de profil |
| `localisation` | VARCHAR(100) | NULL | Ville / quartier |
| `last_login` | DATETIME | NULL | Dernière connexion |
| `email_verified` | TINYINT(1) | DEFAULT 0 | Email vérifié (0 = non, 1 = oui) |
| `token_reset` | VARCHAR(255) | NULL | Token récupération mot de passe |
| `statut` | ENUM('actif', 'inactif') | DEFAULT 'actif' | Statut du compte |
| `created_at` | TIMESTAMP | DEFAULT NOW() | Date de création |
| `updated_at` | TIMESTAMP | DEFAULT NOW() | Date de modification |

---

### 🎓 Table `profils_etudiants`
> Informations complémentaires des étudiants

| Colonne | Type | Contrainte | Description |
|---|---|---|---|
| `id` | INT | PK, AUTO_INCREMENT | Identifiant unique |
| `utilisateur_id` | INT | FK → utilisateurs.id | Référence utilisateur |
| `universite` | VARCHAR(150) | NULL | Établissement fréquenté |
| `niveau_etude` | VARCHAR(50) | NULL | Licence, Master, BTS… |
| `filiere` | VARCHAR(100) | NULL | Domaine d'études |
| `competences` | TEXT | NULL | Compétences (liste) |
| `cv` | VARCHAR(255) | NULL | Fichier CV (chemin) |
| `disponibilite` | VARCHAR(100) | NULL | Jours/heures disponibles |
| `bio` | TEXT | NULL | Présentation personnelle |

---

### 🏢 Table `profils_recruteurs`
> Informations complémentaires des recruteurs

| Colonne | Type | Contrainte | Description |
|---|---|---|---|
| `id` | INT | PK, AUTO_INCREMENT | Identifiant unique |
| `utilisateur_id` | INT | FK → utilisateurs.id | Référence utilisateur |
| `nom_structure` | VARCHAR(150) | NULL | Nom de l'entreprise / structure |
| `type_recruteur` | ENUM('entreprise', 'startup', 'agence', 'commerçant', 'particulier') | NULL | Type de recruteur |
| `site_web` | VARCHAR(255) | NULL | Site web |
| `description` | TEXT | NULL | Présentation de la structure |
| `logo` | VARCHAR(255) | NULL | Logo de la structure |

---

### 📋 Table `categories`
> Catégories de missions disponibles

| Colonne | Type | Contrainte | Description |
|---|---|---|---|
| `id` | INT | PK, AUTO_INCREMENT | Identifiant unique |
| `nom` | VARCHAR(100) | NOT NULL, UNIQUE | Nom de la catégorie |
| `icone` | VARCHAR(50) | NULL | Icône associée |

> **Exemples de catégories :** Cours particuliers, Événementiel, Livraison, Informatique, Community Management, Accueil, Autre

---

### 📌 Table `missions`
> Offres de missions publiées par les recruteurs

| Colonne | Type | Contrainte | Description |
|---|---|---|---|
| `id` | INT | PK, AUTO_INCREMENT | Identifiant unique |
| `recruteur_id` | INT | FK → utilisateurs.id | Recruteur ayant publié |
| `categorie_id` | INT | FK → categories.id | Catégorie de la mission |
| `titre` | VARCHAR(200) | NOT NULL | Titre de la mission |
| `description` | TEXT | NOT NULL | Détail de la mission |
| `localisation` | VARCHAR(100) | NOT NULL | Lieu de la mission |
| `type_mission` | ENUM('ponctuelle', 'temps_partiel', 'stage') | NOT NULL | Type de mission |
| `remuneration` | DECIMAL(10,2) | NULL | Rémunération (FCFA) |
| `date_debut` | DATE | NULL | Date de début |
| `date_fin` | DATE | NULL | Date de fin |
| `jours_travail` | VARCHAR(100) | NULL | Jours de travail (ex. "Lundi, Mercredi") |
| `heures_travail` | VARCHAR(100) | NULL | Heures de travail (ex. "09h00 - 13h00") |
| `places_disponibles` | INT | DEFAULT 1 | Nombre de postes |
| `nb_vues` | INT | DEFAULT 0 | Nombre de vues de l'annonce |
| `statut` | ENUM('active', 'fermee', 'expiree') | DEFAULT 'active' | Statut de l'annonce — `expiree` est automatique (date de fin dépassée), `fermee` est une fermeture manuelle du recruteur avant terme |
| `created_at` | TIMESTAMP | DEFAULT NOW() | Date de publication (ne change jamais, y compris après modification de la mission) |
| `updated_at` | TIMESTAMP | DEFAULT NOW() | Date de modification |

---

### 📨 Table `candidatures`
> Candidatures envoyées par les étudiants

| Colonne | Type | Contrainte | Description |
|---|---|---|---|
| `id` | INT | PK, AUTO_INCREMENT | Identifiant unique |
| `etudiant_id` | INT | FK → utilisateurs.id | Étudiant candidat |
| `mission_id` | INT | FK → missions.id | Mission ciblée |
| `message_motivation` | TEXT | NULL | Lettre de motivation |
| `statut` | ENUM('en_attente', 'acceptee', 'refusee', 'en_cours', 'terminee') | DEFAULT 'en_attente' | Statut de la candidature — `en_cours`/`terminee` suivent la mission après acceptation, jusqu'à la date de fin |
| `created_at` | TIMESTAMP | DEFAULT NOW() | Date de candidature |
| `updated_at` | TIMESTAMP | DEFAULT NOW() | Date de mise à jour |

> **Contrainte d'unicité :** `UNIQUE(etudiant_id, mission_id)` — un étudiant ne peut postuler qu'une seule fois par mission.

---

## 2. RELATIONS ENTRE LES TABLES

```
utilisateurs (1) ──────────── (1) profils_etudiants
                                   [utilisateur_id]

utilisateurs (1) ──────────── (1) profils_recruteurs
                                   [utilisateur_id]

utilisateurs (1) ──────────── (N) missions
  [recruteur]                      [recruteur_id]

categories   (1) ──────────── (N) missions
                                   [categorie_id]

utilisateurs (1) ──────────── (N) candidatures
  [etudiant]                       [etudiant_id]

missions     (1) ──────────── (N) candidatures
                                   [mission_id]

utilisateurs (1) ──────────── (N) notifications
  [utilisateur]                    [utilisateur_id]
```

---

## 3. TABLEAUX DE BORD

Les pages de tableau de bord sont des vues métiers construites en temps réel à partir des données existantes. Elles n'ont pas besoin de tables dédiées, mais elles s'appuient sur des requêtes SQL agrégées.

### Dashboard étudiant
- Total de candidatures envoyées : `SELECT COUNT(*) FROM candidatures WHERE etudiant_id = ?`.
- Candidatures en attente : `SELECT COUNT(*) FROM candidatures WHERE etudiant_id = ? AND statut = 'en_attente'`.
- Candidatures acceptées : `SELECT COUNT(*) FROM candidatures WHERE etudiant_id = ? AND statut = 'acceptee'`.
- Candidatures refusées : `SELECT COUNT(*) FROM candidatures WHERE etudiant_id = ? AND statut = 'refusee'`.
- 5 dernières candidatures : `SELECT c.*, m.titre FROM candidatures c JOIN missions m ON c.mission_id = m.id WHERE c.etudiant_id = ? ORDER BY c.created_at DESC LIMIT 5`.

### Dashboard recruteur
- Total des missions publiées : `SELECT COUNT(*) FROM missions WHERE recruteur_id = ?`.
- Missions actives : `SELECT COUNT(*) FROM missions WHERE recruteur_id = ? AND statut = 'active'`.
- Total des candidatures reçues : `SELECT COUNT(c.id) FROM candidatures c JOIN missions m ON c.mission_id = m.id WHERE m.recruteur_id = ?`.
- 5 dernières missions avec nombre de candidatures :
  `SELECT m.id, m.titre, COUNT(c.id) AS total_candidatures FROM missions m LEFT JOIN candidatures c ON c.mission_id = m.id WHERE m.recruteur_id = ? GROUP BY m.id ORDER BY m.created_at DESC LIMIT 5`.

### Notifications
- La table `notifications` sert à afficher les alertes récentes sur les dashboards.
- Exemple : nouvelle candidature reçue, candidature acceptée/refusée, mission fermée, mise à jour de profil.

## 4. RÉSUMÉ DES RELATIONS

| Table A | Cardinalité | Table B | Clé étrangère |
|---|---|---|---|
| `utilisateurs` | 1 — 1 | `profils_etudiants` | `profils_etudiants.utilisateur_id` |
| `utilisateurs` | 1 — 1 | `profils_recruteurs` | `profils_recruteurs.utilisateur_id` |
| `utilisateurs` | 1 — N | `missions` | `missions.recruteur_id` |
| `categories` | 1 — N | `missions` | `missions.categorie_id` |
| `utilisateurs` | 1 — N | `candidatures` | `candidatures.etudiant_id` |
| `missions` | 1 — N | `candidatures` | `candidatures.mission_id` |
| `utilisateurs` | 1 — N | `notifications` | `notifications.utilisateur_id` |

---

## 5. RÔLE ADMINISTRATEUR

Un troisième rôle existe en plus de `etudiant` et `recruteur` : `admin`. Il ne possède pas de table de profil dédiée (pas de `profils_admin`) — c'est un simple utilisateur de la table `utilisateurs` avec `role = 'admin'`.

- Un compte admin est créé automatiquement au premier lancement de l'application (`includes/db.php`) si aucun n'existe encore : `admin@sunujob.sn` / `Admin123!` (à changer immédiatement en production).
- L'admin gère la plateforme depuis `pages/admin/` : tableau de bord (statistiques globales), gestion des utilisateurs (activer/désactiver/supprimer), gestion des missions (supprimer les offres abusives) et gestion des catégories (créer/modifier).
- Contrairement aux étudiants et recruteurs, l'admin ne publie pas de contenu métier — son rôle est uniquement la modération et le suivi.

---

## 6. SCRIPT SQL DE CRÉATION

```sql
-- Table utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    role ENUM('etudiant', 'recruteur', 'admin') NOT NULL,
    photo VARCHAR(255),
    localisation VARCHAR(100),
    last_login DATETIME,
    email_verified TINYINT(1) DEFAULT 0,
    token_reset VARCHAR(255),
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table profils_etudiants
CREATE TABLE profils_etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL UNIQUE,
    universite VARCHAR(150),
    niveau_etude VARCHAR(50),
    filiere VARCHAR(100),
    competences TEXT,
    cv VARCHAR(255),
    disponibilite VARCHAR(100),
    bio TEXT,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table profils_recruteurs
CREATE TABLE profils_recruteurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL UNIQUE,
    nom_structure VARCHAR(150),
    type_recruteur ENUM('entreprise', 'startup', 'agence', 'commercant', 'particulier'),
    site_web VARCHAR(255),
    description TEXT,
    logo VARCHAR(255),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    icone VARCHAR(50)
);

-- Table missions
CREATE TABLE missions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruteur_id INT NOT NULL,
    categorie_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    localisation VARCHAR(100) NOT NULL,
    type_mission ENUM('ponctuelle', 'temps_partiel', 'stage') NOT NULL,
    remuneration DECIMAL(10,2),
    date_debut DATE,
    date_fin DATE,
    jours_travail VARCHAR(100),
    heures_travail VARCHAR(100),
    places_disponibles INT DEFAULT 1,
    nb_vues INT NOT NULL DEFAULT 0,
    statut ENUM('active', 'fermee', 'expiree') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (recruteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- Table notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    type ENUM('info', 'alerte', 'success', 'erreur') DEFAULT 'info',
    titre VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    lien VARCHAR(255),
    lu TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table candidatures
CREATE TABLE candidatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    mission_id INT NOT NULL,
    message_motivation TEXT,
    statut ENUM('en_attente', 'acceptee', 'refusee', 'en_cours', 'terminee') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_candidature (etudiant_id, mission_id),
    FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE
);
```

---

## 7. DONNÉES DE TEST (Catégories)

```sql
INSERT INTO categories (nom, icone) VALUES
('Cours particuliers', 'fa-book'),
('Événementiel', 'fa-calendar'),
('Livraison', 'fa-motorcycle'),
('Informatique', 'fa-laptop'),
('Community Management', 'fa-hashtag'),
('Agent d\'accueil', 'fa-handshake'),
('Autre', 'fa-ellipsis-h');
```

---

> **Total : 7 tables** — `utilisateurs`, `profils_etudiants`, `profils_recruteurs`, `categories`, `missions`, `candidatures`, `notifications`

---

## Références liées

- [Cahier des charges](./cahier_des_charges_sunujob.md) — besoins fonctionnels et objectifs.
- [Exigences du projet](./EXIGENCES_PROJET.md) — architecture et bonnes pratiques PHP.
- [Suivi des fonctionnalités](./FONCTIONNALITES_SUIVI.md) — tâches à réaliser pour livrer le projet.
- [README](./README.md) — résumé et instructions de démarrage.
