<?php
/**
 * Webhook Wave — confirmation officielle des paiements d'abonnement
 * SunuJob Étudiant
 *
 * Endpoint public appelé par les serveurs Wave (aucune session, aucun cookie).
 * La sécurité repose uniquement sur la vérification de signature ci-dessous,
 * jamais sur une simple confiance dans le contenu de la requête.
 *
 * NB : le nom exact de l'en-tête de signature et la forme du payload doivent être
 * confirmés avec la documentation Wave Business officielle au moment de brancher
 * les vraies clés — à ajuster si besoin, la logique de traitement ci-dessous reste valable.
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/abonnements.php';
require_once __DIR__ . '/includes/wave.php';

header('Content-Type: application/json');

$payloadBrut = file_get_contents('php://input');
$signature = $_SERVER['HTTP_WAVE_SIGNATURE'] ?? null;

if (!verifierSignatureWebhookWave($payloadBrut, $signature)) {
    http_response_code(401);
    echo json_encode(['error' => 'Signature invalide']);
    exit;
}

$event = json_decode($payloadBrut, true);
$reference = $event['data']['client_reference'] ?? null;
$transactionId = $event['data']['id'] ?? null;
$paiementConfirme = ($event['type'] ?? '') === 'checkout.session.completed';

if (!$reference || !$paiementConfirme) {
    http_response_code(200);
    echo json_encode(['status' => 'ignore']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM paiements WHERE reference_wave = ?");
$stmt->bind_param("s", $reference);
$stmt->execute();
$paiement = $stmt->get_result()->fetch_assoc();

if (!$paiement || $paiement['statut'] === 'paye') {
    http_response_code(200);
    echo json_encode(['status' => 'deja_traite']);
    exit;
}

$plan = getPlanParMontant($paiement['montant']);

if ($plan) {
    $abonnementId = creerAbonnement((int)$paiement['utilisateur_id'], $plan, $reference);

    $stmt = $conn->prepare("UPDATE paiements SET statut = 'paye', date_paiement = NOW(), transaction_id = ?, abonnement_id = ? WHERE id = ?");
    $stmt->bind_param("sii", $transactionId, $abonnementId, $paiement['id']);
    $stmt->execute();
}

http_response_code(200);
echo json_encode(['status' => 'ok']);
?>
