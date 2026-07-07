<?php
/**
 * Fonctions d'authentification
 * SunuJob Étudiant
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

/**
 * Vérifier si l'utilisateur est connecté
 * @return bool
 */
function estConnecte() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifier le rôle de l'utilisateur
 * @param string $role
 * @return bool
 */
function aRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Vérifier si l'utilisateur est administrateur
 * @return bool
 */
function isAdmin() {
    return aRole('admin');
}

/**
 * Vérifier la session et rediriger si non connecté
 * @param string|null $role
 */
function verifierSession($role = null) {
    if (!estConnecte()) {
        header('Location: /login.php');
        exit;
    }

    if ($role !== null && !aRole($role)) {
        header('Location: /index.php');
        exit;
    }
}

/**
 * Récupérer l'utilisateur connecté
 * @return array|null
 */
function getUser() {
    if (!estConnecte()) {
        return null;
    }

    return [
        'id'          => $_SESSION['user_id'],
        'nom'         => $_SESSION['user_nom'],
        'prenom'      => $_SESSION['user_prenom'],
        'email'       => $_SESSION['user_email'],
        'role'        => $_SESSION['user_role'],
        'photo'       => $_SESSION['user_photo'] ?? null,
        'localisation'=> $_SESSION['user_localisation'] ?? null,
        'telephone'   => $_SESSION['user_telephone'] ?? null
    ];
}

/**
 * Récupérer l'utilisateur complet depuis la base de données
 * Utile pour avoir les données fraîches (téléphone, photo, etc.)
 * @param int $userId
 * @return array|null
 */
function getUserComplet($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, nom, prenom, email, role, photo, localisation, telephone FROM utilisateurs WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Inscription d'un utilisateur
 * @param array $data
 * @return array
 */
function inscrire($data) {
    global $conn;

    $erreurs = [];

    // Validation des champs
    if (empty($data['nom'])) {
        $erreurs[] = "Le nom est obligatoire.";
    }
    if (empty($data['prenom'])) {
        $erreurs[] = "Le prénom est obligatoire.";
    }
    if (empty($data['email'])) {
        $erreurs[] = "L'email est obligatoire.";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email n'est pas valide.";
    }
    if (empty($data['mot_de_passe'])) {
        $erreurs[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($data['mot_de_passe']) < 6) {
        $erreurs[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    if (empty($data['role']) || !in_array($data['role'], ['etudiant', 'recruteur'])) {
        $erreurs[] = "Le rôle est obligatoire.";
    }

    // Vérifier si l'email existe déjà
    $email = securiser($data['email']);
    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $erreurs[] = "Cet email est déjà utilisé.";
    }

    if (!empty($erreurs)) {
        return ['succes' => false, 'erreurs' => $erreurs];
    }

    // Hasher le mot de passe
    $motDePasse = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);

    // Insérer l'utilisateur
    $nom = securiser($data['nom']);
    $prenom = securiser($data['prenom']);
    $telephone = securiser($data['telephone'] ?? '');
    $role = securiser($data['role']);
    $localisation = securiser($data['localisation'] ?? '');

    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, localisation) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nom, $prenom, $email, $motDePasse, $telephone, $role, $localisation);

    if ($stmt->execute()) {
        $userId = $conn->insert_id;

        // Créer le profil correspondant
        if ($role === 'etudiant') {
            $conn->query("INSERT INTO profils_etudiants (utilisateur_id) VALUES ($userId)");
        } else {
            $conn->query("INSERT INTO profils_recruteurs (utilisateur_id) VALUES ($userId)");
        }

        return ['succes' => true, 'user_id' => $userId];
    }

    return ['succes' => false, 'erreurs' => ["Erreur lors de l'inscription."]];
}

/**
 * Connexion d'un utilisateur
 * @param string $email
 * @param string $motDePasse
 * @return array
 */
function connecter($email, $motDePasse) {
    global $conn;

    $email = securiser($email);

    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = ? AND statut = 'actif'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['succes' => false, 'erreur' => "Email ou mot de passe incorrect."];
    }

    $user = $result->fetch_assoc();

    if (!password_verify($motDePasse, $user['mot_de_passe'])) {
        return ['succes' => false, 'erreur' => "Email ou mot de passe incorrect."];
    }

    // Mettre à jour la dernière connexion
    $conn->query("UPDATE utilisateurs SET last_login = NOW() WHERE id = " . $user['id']);

    // Créer la session
    $_SESSION['user_id']          = $user['id'];
    $_SESSION['user_nom']         = $user['nom'];
    $_SESSION['user_prenom']      = $user['prenom'];
    $_SESSION['user_email']       = $user['email'];
    $_SESSION['user_role']        = $user['role'];
    $_SESSION['user_photo']       = $user['photo'];
    $_SESSION['user_localisation']= $user['localisation'];
    $_SESSION['user_telephone']   = $user['telephone'];

    return ['succes' => true, 'user' => $user];
}

/**
 * Déconnexion
 */
function deconnecter() {
    $_SESSION = array();
    session_destroy();
    header('Location: /login.php');
    exit;
}

/**
 * Vérifier si l'email est une adresse scolaire (étudiant)
 * @param string $email
 * @return bool
 */
function estEmailScolaire($email) {
    $email = strtolower(trim($email));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $domaine = substr(strrchr($email, '@'), 1);

    if (preg_match('/\.(edu\.sn|ac\.sn|edu)$/', $domaine)) {
        return true;
    }

    if (preg_match('/^(etu|etudiant|student)\./i', $domaine)) {
        return true;
    }

    $domainesUniversitaires = [
        'ucad.sn', 'ugb.sn', 'uam.sn', 'univ-thies.sn', 'esp.sn',
        'estm.sn', 'isep.sn', 'ehtp.sn', 'uasz.sn', 'uvs.sn', 'universite.sn'
    ];

    return in_array($domaine, $domainesUniversitaires, true);
}

/**
 * Libellé affiché d'un statut de candidature
 * @param string $statut
 * @return string
 */
function libelleStatutCandidature($statut) {
    $labels = [
        'en_attente' => 'En attente',
        'acceptee'   => 'Acceptée',
        'refusee'    => 'Refusée',
        'en_cours'   => 'En cours',
        'terminee'   => 'Terminée'
    ];
    return $labels[$statut] ?? ucfirst($statut);
}

/**
 * Classe CSS du badge d'un statut de candidature
 * @param string $statut
 * @return string
 */
function badgeClassStatutCandidature($statut) {
    $classes = [
        'en_attente' => 'attente',
        'acceptee'   => 'acceptee',
        'refusee'    => 'refusee',
        'en_cours'   => 'en-cours',
        'terminee'   => 'terminee'
    ];
    return $classes[$statut] ?? 'attente';
}

/**
 * Liste des statuts de candidature valides
 * @return array
 */
function getStatutsCandidature() {
    return ['en_attente', 'acceptee', 'refusee', 'en_cours', 'terminee'];
}

/**
 * Incrémenter le compteur de vues d'une mission
 * @param int $missionId
 * @param int|null $recruteurId ID du recruteur propriétaire (ses vues ne sont pas comptées)
 */
function incrementerVuesMission($missionId, $recruteurId = null) {
    global $conn;

    if ($recruteurId !== null && estConnecte() && (int)$_SESSION['user_id'] === (int)$recruteurId) {
        return;
    }

    $stmt = $conn->prepare("UPDATE missions SET nb_vues = nb_vues + 1 WHERE id = ?");
    $stmt->bind_param("i", $missionId);
    $stmt->execute();
}

/**
 * Récupérer le profil étudiant
 * @param int $userId
 * @return array|null
 */
function getProfilEtudiant($userId) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM profils_etudiants WHERE utilisateur_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

/**
 * Récupérer le profil recruteur
 * @param int $userId
 * @return array|null
 */
function getProfilRecruteur($userId) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM profils_recruteurs WHERE utilisateur_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

/**
 * Générer un token de réinitialisation
 * @param string $email
 * @return string|null
 */
function genererTokenReset($email) {
    global $conn;

    $email = securiser($email);

    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    if ($stmt->get_result()->num_rows === 0) {
        return null;
    }

    $token = bin2hex(random_bytes(32));

    $stmt = $conn->prepare("UPDATE utilisateurs SET token_reset = ? WHERE email = ?");
    $stmt->bind_param("ss", $token, $email);
    $stmt->execute();

    return $token;
}

/**
 * Réinitialiser le mot de passe
 * @param string $token
 * @param string $nouveauMdp
 * @return bool
 */
function resetMotDePasse($token, $nouveauMdp) {
    global $conn;

    $token = trim($token);
    $motDePasse = password_hash($nouveauMdp, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = ?, token_reset = NULL WHERE token_reset = ?");
    $stmt->bind_param("ss", $motDePasse, $token);

    return $stmt->execute() && $stmt->affected_rows > 0;
}

/**
 * Générer / récupérer le token CSRF de session
 * @return string
 */
function genererCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier le token CSRF
 * @param string|null $token
 * @return bool
 */
function verifierCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Champ hidden CSRF pour formulaires
 * @return string
 */
function champCsrf() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(genererCsrfToken()) . '">';
}

/**
 * Exiger un token CSRF valide sur les requêtes POST
 * @param string|null $redirect
 */
function exigerCsrfPost($redirect = null) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifierCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = 'Session expirée ou formulaire invalide. Veuillez réessayer.';
        $_SESSION['flash_type'] = 'danger';
        $url = $redirect ?? ($_SERVER['HTTP_REFERER'] ?? '/index.php');
        header('Location: ' . $url);
        exit;
    }
}

/**
 * Vérifier si une mission accepte encore des candidatures
 * @param array $mission
 * @return bool
 */
function missionEstOuverte($mission) {
    if (($mission['statut'] ?? '') !== 'active') {
        return false;
    }
    if (!empty($mission['date_fin']) && $mission['date_fin'] < date('Y-m-d')) {
        return false;
    }
    return true;
}

/**
 * Compter les places occupées sur une mission
 * @param int $missionId
 * @return int
 */
function compterPlacesOccupees($missionId) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM candidatures
        WHERE mission_id = ? AND statut IN ('acceptee', 'en_cours', 'terminee')
    ");
    $stmt->bind_param("i", $missionId);
    $stmt->execute();
    return (int)$stmt->get_result()->fetch_assoc()['total'];
}

/**
 * Vérifier s'il reste des places sur une mission
 * @param int $missionId
 * @return bool
 */
function peutAccepterCandidature($missionId) {
    global $conn;
    $stmt = $conn->prepare("SELECT places_disponibles FROM missions WHERE id = ?");
    $stmt->bind_param("i", $missionId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        return false;
    }
    return compterPlacesOccupees($missionId) < (int)$row['places_disponibles'];
}

/**
 * Valider un fichier uploadé
 * @param array $file $_FILES entry
 * @param array $extensionsAutorisees
 * @param int $tailleMaxOctets
 * @param string $libelle
 * @return string|null Message d'erreur ou null si OK
 */
function validerFichierUpload($file, array $extensionsAutorisees, $tailleMaxOctets, $libelle = 'Fichier') {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "$libelle : erreur lors du téléversement.";
    }
    if ($file['size'] > $tailleMaxOctets) {
        $mo = round($tailleMaxOctets / 1048576, 1);
        return "$libelle : taille maximale {$mo} Mo dépassée.";
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $extensionsAutorisees, true)) {
        return "$libelle : format non autorisé.";
    }
    return null;
}
?>
