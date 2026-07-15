-- ============================================
-- BASE DE DONNÉES SUNUJOB ÉTUDIANT
-- Script de création complet
-- ============================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS sunujob_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sunujob_db;

-- ============================================
-- TABLE utilisateurs
-- ============================================
DROP TABLE IF EXISTS utilisateurs;
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

-- ============================================
-- TABLE profils_etudiants
-- ============================================
DROP TABLE IF EXISTS profils_etudiants;
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

-- ============================================
-- TABLE profils_recruteurs
-- ============================================
DROP TABLE IF EXISTS profils_recruteurs;
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

-- ============================================
-- TABLE categories
-- ============================================
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    icone VARCHAR(50)
);

-- ============================================
-- TABLE missions
-- ============================================
DROP TABLE IF EXISTS missions;
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

-- ============================================
-- TABLE candidatures
-- ============================================
DROP TABLE IF EXISTS candidatures;
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

-- ============================================
-- TABLE notifications
-- ============================================
DROP TABLE IF EXISTS notifications;
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

-- ============================================
-- TABLE abonnements
-- ============================================
DROP TABLE IF EXISTS abonnements;
CREATE TABLE abonnements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    type_abonnement ENUM('mensuel','trimestriel','annuel') NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('actif','expire','annule') NOT NULL DEFAULT 'actif',
    mode_paiement VARCHAR(50) NOT NULL DEFAULT 'wave',
    reference_paiement VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_utilisateur_statut (utilisateur_id, statut)
);

-- ============================================
-- TABLE paiements
-- ============================================
DROP TABLE IF EXISTS paiements;
CREATE TABLE paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    abonnement_id INT NULL,
    montant DECIMAL(10,2) NOT NULL,
    devise VARCHAR(10) NOT NULL DEFAULT 'XOF',
    moyen_paiement VARCHAR(50) NOT NULL DEFAULT 'wave',
    reference_wave VARCHAR(150),
    transaction_id VARCHAR(150),
    statut ENUM('en_attente','paye','echoue') NOT NULL DEFAULT 'en_attente',
    date_paiement DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (abonnement_id) REFERENCES abonnements(id) ON DELETE SET NULL,
    INDEX idx_statut (statut),
    INDEX idx_reference_wave (reference_wave)
);

-- ============================================
-- TABLE parametres
-- ============================================
DROP TABLE IF EXISTS parametres;
CREATE TABLE parametres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- DONNÉES INITIALES
-- ============================================

-- Insertion des catégories
INSERT INTO categories (nom, icone) VALUES
('Cours particuliers', 'fa-book'),
('Événementiel', 'fa-calendar'),
('Livraison', 'fa-motorcycle'),
('Informatique', 'fa-laptop'),
('Community Management', 'fa-hashtag'),
('Agent d''accueil', 'fa-handshake'),
('Autre', 'fa-ellipsis-h');

-- Utilisateur admin initial
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
VALUES ('Admin', 'SunuJob', 'admin@sunujob.sn', '$2y$10$sbaJAz0ESGVT7CgqknsDjOk5zM7q.YAursiNGnwVOmWGS9xdPCiBq', '+221770000000', 'admin', 'actif');

-- ============================================
-- INDEX POUR OPTIMISATION
-- ============================================
CREATE INDEX idx_missions_recruteur ON missions(recruteur_id);
CREATE INDEX idx_missions_categorie ON missions(categorie_id);
CREATE INDEX idx_missions_statut ON missions(statut);
CREATE INDEX idx_missions_localisation ON missions(localisation);
CREATE INDEX idx_candidatures_etudiant ON candidatures(etudiant_id);
CREATE INDEX idx_candidatures_mission ON candidatures(mission_id);
CREATE INDEX idx_notifications_utilisateur ON notifications(utilisateur_id);
CREATE INDEX idx_utilisateurs_email ON utilisateurs(email);
CREATE INDEX idx_utilisateurs_role ON utilisateurs(role);
