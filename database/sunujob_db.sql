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
    places_disponibles INT DEFAULT 1,
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
    statut ENUM('en_attente', 'acceptee', 'refusee') DEFAULT 'en_attente',
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
