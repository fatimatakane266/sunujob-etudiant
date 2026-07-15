<?php
/**
 * Intégration Wave Business (paiement des abonnements)
 * SunuJob Étudiant
 *
 * Clés lues depuis l'environnement (mêmes conventions que l'intégration Twilio de contact.php) :
 *   WAVE_API_KEY          - clé secrète Wave Business (Bearer token)
 *   WAVE_WEBHOOK_SECRET   - secret de signature des webhooks Wave
 * Tant qu'elles ne sont pas définies, aucun appel réel n'est fait — jamais de paiement simulé.
 */

const WAVE_API_BASE = 'https://api.wave.com/v1';

/**
 * L'intégration Wave est-elle configurée (clés présentes) ?
 * @return bool
 */
function waveEstConfigure() {
    return (bool) (getenv('WAVE_API_KEY') && getenv('WAVE_WEBHOOK_SECRET'));
}

/**
 * Créer une session de paiement Wave Checkout (montant en FCFA)
 * @param float $montant
 * @param string $reference Référence interne (ex. reference du paiement en base)
 * @param string $urlSucces
 * @param string $urlEchec
 * @return array|null ['launch_url' => ..., 'session_id' => ...] ou null si non configuré / échec
 */
function creerSessionCheckoutWave($montant, $reference, $urlSucces, $urlEchec) {
    if (!waveEstConfigure()) {
        return null;
    }

    $apiKey = getenv('WAVE_API_KEY');

    $payload = json_encode([
        'amount' => (string) (int) $montant,
        'currency' => 'XOF',
        'client_reference' => $reference,
        'success_url' => $urlSucces,
        'error_url' => $urlEchec,
    ]);

    $ch = curl_init(WAVE_API_BASE . '/checkout/sessions');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return null;
    }

    $data = json_decode($response, true);
    if (!isset($data['wave_launch_url'], $data['id'])) {
        return null;
    }

    return [
        'launch_url' => $data['wave_launch_url'],
        'session_id' => $data['id'],
    ];
}

/**
 * Vérifier la signature d'un webhook Wave.
 * NB : le format exact de l'en-tête de signature doit être confirmé avec la documentation
 * officielle Wave Business au moment de l'intégration réelle ; cette vérification HMAC-SHA256
 * du corps brut avec le secret partagé est le mécanisme standard utilisé par Wave.
 *
 * @param string $payloadBrut Corps brut de la requête (php://input)
 * @param string|null $signatureHeader Valeur de l'en-tête de signature reçu
 * @return bool
 */
function verifierSignatureWebhookWave($payloadBrut, $signatureHeader) {
    if (!waveEstConfigure() || empty($signatureHeader)) {
        return false;
    }

    $secret = getenv('WAVE_WEBHOOK_SECRET');
    $signatureAttendue = hash_hmac('sha256', $payloadBrut, $secret);

    return hash_equals($signatureAttendue, $signatureHeader);
}
?>
