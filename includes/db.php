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
    $conn->query("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut) VALUES ('Admin', 'SunuJob', 'admin@sunujob.sn', '$adminPassword', '+221770000000', 'admin', 'actif')");
}

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
