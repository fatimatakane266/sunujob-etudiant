<?php
/**
 * Connexion à la base de données
 * SunuJob Étudiant
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'fatima');
define('DB_NAME', 'sunujob_db');
define('DB_CHARSET', 'utf8mb4');

// Connexion MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Définir le charset
$conn->set_charset(DB_CHARSET);

// Vérifie si la table principale existe et charge le schéma si nécessaire
$schemaCheck = $conn->query("SHOW TABLES LIKE 'utilisateurs'");
if ($schemaCheck && $schemaCheck->num_rows === 0) {
    $schemaFile = __DIR__ . '/../database/sunujob_db.sql';
    if (!file_exists($schemaFile)) {
        die("Base de données non initialisée : fichier de schéma introuvable ($schemaFile)");
    }

    $schemaSql = file_get_contents($schemaFile);
    if ($schemaSql === false) {
        die('Impossible de lire le fichier de schéma de la base de données.');
    }

    if (!$conn->multi_query($schemaSql)) {
        die('Erreur d\'initialisation du schéma de la base de données : ' . $conn->error);
    }

    while ($conn->more_results() && $conn->next_result()) {
        // Consommer tous les résultats pour terminer multi_query
    }

    $schemaCheck = $conn->query("SHOW TABLES LIKE 'utilisateurs'");
    if (!$schemaCheck || $schemaCheck->num_rows === 0) {
        die('Le schéma de la base de données n\'a pas pu être initialisé correctement.');
    }
}

// Évolutions mineures du schéma si la table existe déjà.
$roleColumn = $conn->query("SHOW COLUMNS FROM utilisateurs LIKE 'role'");
if ($roleColumn && $roleColumn->num_rows > 0) {
    $row = $roleColumn->fetch_assoc();
    if (!str_contains($row['Type'], "'admin'")) {
        $conn->query("ALTER TABLE utilisateurs MODIFY role ENUM('etudiant','recruteur','admin') NOT NULL");
    }
}

$adminCount = $conn->query("SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'admin'")->fetch_assoc()['total'];
if ((int)$adminCount === 0) {
    $adminPassword = password_hash('Admin123!', PASSWORD_BCRYPT);
    $conn->query("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut) VALUES ('Admin', 'SunuJob', 'admin@sunujob.sn', '$adminPassword', '+221775037106', 'admin', 'actif')");
}

// Colonne compteur de vues sur les missions
$vuesColumn = $conn->query("SHOW COLUMNS FROM missions LIKE 'nb_vues'");
if ($vuesColumn && $vuesColumn->num_rows === 0) {
    $conn->query("ALTER TABLE missions ADD COLUMN nb_vues INT NOT NULL DEFAULT 0 AFTER places_disponibles");
}

// Planning de travail pour les missions acceptées
$joursColumn = $conn->query("SHOW COLUMNS FROM missions LIKE 'jours_travail'");
if ($joursColumn && $joursColumn->num_rows === 0) {
    $conn->query("ALTER TABLE missions ADD COLUMN jours_travail VARCHAR(100) NULL AFTER date_fin");
}

$heuresColumn = $conn->query("SHOW COLUMNS FROM missions LIKE 'heures_travail'");
if ($heuresColumn && $heuresColumn->num_rows === 0) {
    $conn->query("ALTER TABLE missions ADD COLUMN heures_travail VARCHAR(100) NULL AFTER jours_travail");
}

// Statuts candidatures : en_cours et terminee
$statutCandidatureColumn = $conn->query("SHOW COLUMNS FROM candidatures LIKE 'statut'");
if ($statutCandidatureColumn && $statutCandidatureColumn->num_rows > 0) {
    $row = $statutCandidatureColumn->fetch_assoc();
    if (!str_contains($row['Type'], 'en_cours')) {
        $conn->query("ALTER TABLE candidatures MODIFY statut ENUM('en_attente','acceptee','refusee','en_cours','terminee') DEFAULT 'en_attente'");
    }
}

// Table des abonnements (candidatures illimitées après le quota gratuit)
$abonnementsTable = $conn->query("SHOW TABLES LIKE 'abonnements'");
if ($abonnementsTable && $abonnementsTable->num_rows === 0) {
    $conn->query("CREATE TABLE abonnements (
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
    )");
}

// Table des paiements Wave
$paiementsTable = $conn->query("SHOW TABLES LIKE 'paiements'");
if ($paiementsTable && $paiementsTable->num_rows === 0) {
    $conn->query("CREATE TABLE paiements (
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
    )");
}

// Table générique de paramètres (clé/valeur) - ex. lien de partage Wave configurable par l'admin
$parametresTable = $conn->query("SHOW TABLES LIKE 'parametres'");
if ($parametresTable && $parametresTable->num_rows === 0) {
    $conn->query("CREATE TABLE parametres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cle VARCHAR(100) NOT NULL UNIQUE,
        valeur TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
}

/**
 * Fermer automatiquement les missions dont la date de fin est dépassée
 */
function fermerMissionsExpirees() {
    global $conn;
    $conn->query("UPDATE missions SET statut = 'expiree' WHERE statut = 'active' AND date_fin IS NOT NULL AND date_fin < CURDATE()");
}

function actualiserCandidaturesTerminees() {
    global $conn;
    $conn->query(
        "UPDATE candidatures c
         JOIN missions m ON c.mission_id = m.id
         SET c.statut = 'terminee'
         WHERE c.statut IN ('acceptee', 'en_cours')
           AND m.date_fin IS NOT NULL
           AND m.date_fin < CURDATE()"
    );
}

fermerMissionsExpirees();
actualiserCandidaturesTerminees();

/**
 * Fonction pour sécuriser les données
 * @param string $data
 * @return string
 */
function securiser($data) {
    global $conn;
    return htmlspecialchars($conn->real_escape_string(trim($data)));
}

/**
 * Fonction pour exécuter une requête préparée
 * @param string $sql
 * @param string $types
 * @param array $params
 * @return mysqli_result|bool
 */
function executerRequete($sql, $types = '', $params = []) {
    global $conn;

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Obtenir le dernier ID inséré
 * @return int
 */
function dernierId() {
    global $conn;
    return $conn->insert_id;
}
?>
