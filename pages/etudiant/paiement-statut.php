<?php
/**
 * Statut d'un paiement Wave (JSON, utilisé par le polling de paiement-wave.php)
 * SunuJob Étudiant
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

header('Content-Type: application/json');

if (!estConnecte() || !aRole('etudiant')) {
    http_response_code(401);
    echo json_encode(['statut' => 'inconnu']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$reference = $_GET['reference'] ?? '';

$stmt = $conn->prepare("SELECT statut FROM paiements WHERE reference_wave = ? AND utilisateur_id = ?");
$stmt->bind_param("si", $reference, $_SESSION['user_id']);
$stmt->execute();
$paiement = $stmt->get_result()->fetch_assoc();

echo json_encode(['statut' => $paiement['statut'] ?? 'inconnu']);
?>
