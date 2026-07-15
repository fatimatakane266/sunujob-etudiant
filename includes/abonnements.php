<?php
/**
 * Gestion des abonnements et du quota de candidatures gratuites
 * SunuJob Étudiant
 */

const CANDIDATURES_GRATUITES_MAX = 2;

/**
 * Récupérer un paramètre de configuration (clé/valeur)
 * @param string $cle
 * @return string|null
 */
function getParametre($cle) {
    global $conn;
    $stmt = $conn->prepare("SELECT valeur FROM parametres WHERE cle = ?");
    $stmt->bind_param("s", $cle);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? $row['valeur'] : null;
}

/**
 * Définir un paramètre de configuration (clé/valeur)
 * @param string $cle
 * @param string $valeur
 */
function setParametre($cle, $valeur) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO parametres (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)");
    $stmt->bind_param("ss", $cle, $valeur);
    $stmt->execute();
}

/**
 * Lien de partage Wave configuré par l'admin pour recevoir les paiements d'abonnement
 * @return string|null
 */
function getWaveLienPartage() {
    $lien = getParametre('wave_lien_partage');
    return $lien !== null && trim($lien) !== '' ? trim($lien) : null;
}

/**
 * Liste des offres d'abonnement disponibles
 * @return array
 */
function getPlansAbonnement() {
    return [
        'mensuel' => [
            'label' => 'Pack Mensuel',
            'duree_jours' => 30,
            'prix' => 2000,
        ],
        'trimestriel' => [
            'label' => 'Pack Trimestriel',
            'duree_jours' => 90,
            'prix' => 5000,
        ],
        'annuel' => [
            'label' => 'Pack Annuel',
            'duree_jours' => 365,
            'prix' => 15000,
        ],
    ];
}

/**
 * Retrouver la clé d'un plan à partir de son prix (les 3 offres ont des prix distincts)
 * @param float $montant
 * @return string|null
 */
function getPlanParMontant($montant) {
    foreach (getPlansAbonnement() as $key => $plan) {
        if ((float)$plan['prix'] === (float)$montant) {
            return $key;
        }
    }
    return null;
}

/**
 * Récupérer un plan par sa clé
 * @param string $plan
 * @return array|null
 */
function getPlanAbonnement($plan) {
    $plans = getPlansAbonnement();
    return $plans[$plan] ?? null;
}

/**
 * Nombre total de candidatures jamais enregistrées par l'étudiant (quota à vie)
 * @param int $etudiantId
 * @return int
 */
function compterCandidaturesTotal($etudiantId) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM candidatures WHERE etudiant_id = ?");
    $stmt->bind_param("i", $etudiantId);
    $stmt->execute();
    return (int)$stmt->get_result()->fetch_assoc()['total'];
}

/**
 * Expirer automatiquement les abonnements dont la date de fin est dépassée
 */
function expirerAbonnements() {
    global $conn;
    $conn->query("UPDATE abonnements SET statut = 'expire' WHERE statut = 'actif' AND date_fin < CURDATE()");
}

/**
 * Récupérer l'abonnement actif en cours d'un étudiant, s'il existe
 * @param int $etudiantId
 * @return array|null
 */
function getAbonnementActif($etudiantId) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT * FROM abonnements
        WHERE utilisateur_id = ? AND statut = 'actif' AND date_fin >= CURDATE()
        ORDER BY date_fin DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $etudiantId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}

/**
 * Vérifier si un étudiant peut encore postuler (quota gratuit ou abonnement actif).
 * Seule source de vérité côté serveur — appelée avant tout INSERT dans candidatures.
 * @param int $etudiantId
 * @return bool
 */
function peutPostuler($etudiantId) {
    if (compterCandidaturesTotal($etudiantId) < CANDIDATURES_GRATUITES_MAX) {
        return true;
    }
    return getAbonnementActif($etudiantId) !== null;
}

/**
 * Créer un abonnement actif pour un étudiant à partir d'un plan, suite à un paiement confirmé
 * @param int $etudiantId
 * @param string $plan
 * @param string|null $referencePaiement
 * @return int|null ID de l'abonnement créé, ou null si le plan est invalide
 */
function creerAbonnement($etudiantId, $plan, $referencePaiement = null) {
    global $conn;

    $planInfo = getPlanAbonnement($plan);
    if (!$planInfo) {
        return null;
    }

    $dateDebut = date('Y-m-d');
    $dateFin = date('Y-m-d', strtotime("+{$planInfo['duree_jours']} days"));

    $stmt = $conn->prepare("
        INSERT INTO abonnements (utilisateur_id, type_abonnement, prix, date_debut, date_fin, statut, mode_paiement, reference_paiement)
        VALUES (?, ?, ?, ?, ?, 'actif', 'wave', ?)
    ");
    $stmt->bind_param("isdsss", $etudiantId, $plan, $planInfo['prix'], $dateDebut, $dateFin, $referencePaiement);
    $stmt->execute();

    return $conn->insert_id;
}

/**
 * Libellé lisible d'un statut d'abonnement
 * @param string $statut
 * @return string
 */
function libelleStatutAbonnement($statut) {
    $labels = [
        'actif' => 'Actif',
        'expire' => 'Expiré',
        'annule' => 'Annulé',
    ];
    return $labels[$statut] ?? ucfirst($statut);
}

/**
 * Classe CSS du badge pour un statut d'abonnement
 * @param string $statut
 * @return string
 */
function badgeClassStatutAbonnement($statut) {
    $classes = [
        'actif' => 'active',
        'expire' => 'expiree',
        'annule' => 'fermee',
    ];
    return $classes[$statut] ?? 'fermee';
}

expirerAbonnements();
?>
