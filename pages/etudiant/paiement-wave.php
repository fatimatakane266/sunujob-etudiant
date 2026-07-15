<?php
/**
 * Paiement Wave d'un abonnement
 * SunuJob Étudiant
 */

$pageTitle = 'Paiement Wave - SunuJob Étudiant';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('etudiant');

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/wave.php';

$etudiantId = $_SESSION['user_id'];
$erreur = '';
$paiement = null;
$launchUrl = null;
$modeDemo = false;
$modeLienPartage = false;
$paiementSignale = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'jai_deja_paye') {
    // L'étudiant déclare avoir payé : on notifie l'admin pour qu'il vérifie son compte Wave
    // et confirme manuellement — ce bouton n'active jamais l'abonnement lui-même.
    $reference = $_POST['reference'] ?? '';
    exigerCsrfPost('/pages/etudiant/paiement-wave.php?reference=' . urlencode($reference));

    $stmt = $conn->prepare("SELECT * FROM paiements WHERE reference_wave = ? AND utilisateur_id = ? AND statut = 'en_attente'");
    $stmt->bind_param("si", $reference, $etudiantId);
    $stmt->execute();
    $paiementASignaler = $stmt->get_result()->fetch_assoc();

    if ($paiementASignaler) {
        $etudiant = getUserComplet($etudiantId);
        $titreNotif = "Paiement Wave à vérifier";
        $messageNotif = "{$etudiant['prenom']} {$etudiant['nom']} déclare avoir payé "
            . number_format($paiementASignaler['montant'], 0, ',', ' ') . " FCFA (référence {$reference}). "
            . "Vérifiez votre compte Wave puis confirmez depuis Abonnements & paiements.";
        $lienNotif = "/pages/admin/abonnements.php";

        $admins = $conn->query("SELECT id FROM utilisateurs WHERE role = 'admin'");
        while ($admin = $admins->fetch_assoc()) {
            $adminId = (int)$admin['id'];
            $stmtNotif = $conn->prepare("INSERT INTO notifications (utilisateur_id, type, titre, message, lien) VALUES (?, 'info', ?, ?, ?)");
            $stmtNotif->bind_param("isss", $adminId, $titreNotif, $messageNotif, $lienNotif);
            $stmtNotif->execute();
        }
    }

    header('Location: /pages/etudiant/paiement-wave.php?reference=' . urlencode($reference) . '&signale=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nouvelle demande de paiement, initiée depuis la page des offres
    exigerCsrfPost('/pages/etudiant/abonnement.php');

    $plan = $_POST['plan'] ?? '';
    $planInfo = getPlanAbonnement($plan);

    if (!$planInfo) {
        header('Location: /pages/etudiant/abonnement.php');
        exit;
    }

    $lienPartage = getWaveLienPartage();

    if ($lienPartage) {
        // Lien de partage Wave configuré par l'admin (paiement réel possible, confirmation manuelle
        // puisqu'un lien de partage personnel ne déclenche pas de webhook automatique).
        $modeLienPartage = true;
        $reference = 'SUB-' . strtoupper(bin2hex(random_bytes(6)));

        $stmt = $conn->prepare("
            INSERT INTO paiements (utilisateur_id, montant, devise, moyen_paiement, reference_wave, statut)
            VALUES (?, ?, 'XOF', 'wave', ?, 'en_attente')
        ");
        $stmt->bind_param("ids", $etudiantId, $planInfo['prix'], $reference);
        $stmt->execute();
        $paiementId = $conn->insert_id;

        $launchUrl = $lienPartage;

        $stmt = $conn->prepare("SELECT * FROM paiements WHERE id = ?");
        $stmt->bind_param("i", $paiementId);
        $stmt->execute();
        $paiement = $stmt->get_result()->fetch_assoc();
    } elseif (!waveEstConfigure()) {
        // Mode démonstration : pas de vraies clés Wave, mais on illustre le flux (QR code factice)
        // sans jamais créer de faux paiement confirmé — l'activation reste manuelle (admin uniquement).
        $modeDemo = true;
        $reference = 'DEMO-' . strtoupper(bin2hex(random_bytes(6)));

        $stmt = $conn->prepare("
            INSERT INTO paiements (utilisateur_id, montant, devise, moyen_paiement, reference_wave, statut)
            VALUES (?, ?, 'XOF', 'wave', ?, 'en_attente')
        ");
        $stmt->bind_param("ids", $etudiantId, $planInfo['prix'], $reference);
        $stmt->execute();
        $paiementId = $conn->insert_id;

        $launchUrl = 'SUNUJOB-DEMO:' . $reference;

        $stmt = $conn->prepare("SELECT * FROM paiements WHERE id = ?");
        $stmt->bind_param("i", $paiementId);
        $stmt->execute();
        $paiement = $stmt->get_result()->fetch_assoc();
    } else {
        $reference = 'SUB-' . strtoupper(bin2hex(random_bytes(6)));

        $stmt = $conn->prepare("
            INSERT INTO paiements (utilisateur_id, montant, devise, moyen_paiement, reference_wave, statut)
            VALUES (?, ?, 'XOF', 'wave', ?, 'en_attente')
        ");
        $stmt->bind_param("ids", $etudiantId, $planInfo['prix'], $reference);
        $stmt->execute();
        $paiementId = $conn->insert_id;

        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $urlSucces = $baseUrl . '/pages/etudiant/paiement-wave.php?reference=' . urlencode($reference);
        $urlEchec = $baseUrl . '/pages/etudiant/abonnement.php?erreur=paiement';

        $session = creerSessionCheckoutWave($planInfo['prix'], $reference, $urlSucces, $urlEchec);

        if (!$session) {
            $conn->query("UPDATE paiements SET statut = 'echoue' WHERE id = " . (int)$paiementId);
            $erreur = "Impossible de créer la session de paiement Wave pour le moment. Réessayez plus tard.";
        } else {
            $stmt = $conn->prepare("UPDATE paiements SET transaction_id = ? WHERE id = ?");
            $sessionId = $session['session_id'];
            $stmt->bind_param("si", $sessionId, $paiementId);
            $stmt->execute();

            $launchUrl = $session['launch_url'];
            $stmt = $conn->prepare("SELECT * FROM paiements WHERE id = ?");
            $stmt->bind_param("i", $paiementId);
            $stmt->execute();
            $paiement = $stmt->get_result()->fetch_assoc();
        }
    }
} elseif (isset($_GET['reference'])) {
    // Retour depuis Wave (succès) ou rechargement pendant l'attente de confirmation
    $reference = $_GET['reference'];
    $stmt = $conn->prepare("SELECT * FROM paiements WHERE reference_wave = ? AND utilisateur_id = ?");
    $stmt->bind_param("si", $reference, $etudiantId);
    $stmt->execute();
    $paiement = $stmt->get_result()->fetch_assoc();

    if (!$paiement) {
        header('Location: /pages/etudiant/abonnement.php');
        exit;
    }

    if ($paiement['statut'] === 'paye') {
        header('Location: /pages/etudiant/dashboard.php');
        exit;
    }

    // Reconstituer l'affichage du QR (rechargement de page ou retour après "J'ai déjà payé")
    $lienPartage = getWaveLienPartage();
    if ($lienPartage) {
        $modeLienPartage = true;
        $launchUrl = $lienPartage;
    } elseif (str_starts_with($paiement['reference_wave'], 'DEMO-')) {
        $modeDemo = true;
        $launchUrl = 'SUNUJOB-DEMO:' . $paiement['reference_wave'];
    }

    if (isset($_GET['signale'])) {
        $paiementSignale = true;
    }
} else {
    header('Location: /pages/etudiant/abonnement.php');
    exit;
}

$pageActive = 'dashboard';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-mobile-alt me-2"></i>Paiement Wave</h1>
    </div>
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="mission-detail-card text-center">
                <?php if ($erreur): ?>
                    <div class="alert alert-danger-custom mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($erreur) ?>
                    </div>
                    <a href="/pages/etudiant/abonnement.php" class="btn btn-outline-custom">
                        <i class="fas fa-arrow-left me-2"></i>Retour aux offres
                    </a>
                <?php elseif ($paiement && $paiement['statut'] === 'en_attente' && $launchUrl): ?>
                    <?php if ($modeDemo): ?>
                        <div class="alert alert-warning-custom mb-4 text-start">
                            <i class="fas fa-flask me-2"></i><strong>Mode démonstration</strong> — aucun lien de partage Wave configuré.
                            Ce QR code est factice, aucun paiement réel n'est effectué. Un administrateur peut valider ce paiement de test
                            depuis <em>Abonnements &amp; paiements</em> pour simuler l'activation.
                        </div>
                    <?php elseif ($modeLienPartage): ?>
                        <div class="alert alert-info-custom mb-4 text-start">
                            <i class="fas fa-info-circle me-2"></i>Après avoir envoyé le paiement sur Wave, patientez :
                            l'administrateur confirme manuellement chaque paiement reçu avant d'activer l'abonnement.
                        </div>
                    <?php endif; ?>
                    <h5 class="mb-3">Scannez ce QR code avec l'application Wave</h5>
                    <div id="qrcode" class="d-flex justify-content-center my-4"></div>
                    <p class="text-muted small">
                        Montant à envoyer : <strong><?= number_format($paiement['montant'], 0, ',', ' ') ?> FCFA</strong><br>
                        Référence (à indiquer si possible dans le message Wave) : <?= htmlspecialchars($paiement['reference_wave']) ?>
                    </p>
                    <?php if (!$modeDemo): ?>
                        <a href="<?= htmlspecialchars($launchUrl) ?>" class="btn btn-primary-custom w-100 mb-3" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>Ouvrir dans l'application Wave
                        </a>
                    <?php endif; ?>
                    <div id="statutAttente" class="alert alert-info-custom mb-3">
                        <i class="fas fa-spinner fa-spin me-2"></i>En attente de confirmation du paiement...
                    </div>
                    <?php if ($paiementSignale): ?>
                        <div class="alert alert-success-custom mb-0">
                            <i class="fas fa-check-circle me-2"></i>Merci, l'administrateur a été prévenu et vérifiera votre paiement sous peu.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <?= champCsrf() ?>
                            <input type="hidden" name="action" value="jai_deja_paye">
                            <input type="hidden" name="reference" value="<?= htmlspecialchars($paiement['reference_wave']) ?>">
                            <button type="submit" class="btn btn-outline-custom w-100">
                                <i class="fas fa-hand-holding-usd me-2"></i>J'ai déjà payé
                            </button>
                        </form>
                    <?php endif; ?>
                <?php elseif ($paiement && $paiement['statut'] === 'en_attente'): ?>
                    <div class="alert alert-info-custom mb-3">
                        <i class="fas fa-spinner fa-spin me-2"></i>En attente de confirmation du paiement Wave...
                    </div>
                    <?php if ($paiementSignale): ?>
                        <div class="alert alert-success-custom mb-0">
                            <i class="fas fa-check-circle me-2"></i>Merci, l'administrateur a été prévenu et vérifiera votre paiement sous peu.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <?= champCsrf() ?>
                            <input type="hidden" name="action" value="jai_deja_paye">
                            <input type="hidden" name="reference" value="<?= htmlspecialchars($paiement['reference_wave']) ?>">
                            <button type="submit" class="btn btn-outline-custom w-100">
                                <i class="fas fa-hand-holding-usd me-2"></i>J'ai déjà payé
                            </button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-danger-custom mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i>Paiement annulé ou échoué.
                    </div>
                    <a href="/pages/etudiant/abonnement.php" class="btn btn-outline-custom">
                        <i class="fas fa-arrow-left me-2"></i>Retour aux offres
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($paiement && $paiement['statut'] === 'en_attente'): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($launchUrl): ?>
    new QRCode(document.getElementById('qrcode'), {
        text: <?= json_encode($launchUrl) ?>,
        width: 220,
        height: 220
    });
    <?php endif; ?>

    const reference = <?= json_encode($paiement['reference_wave']) ?>;
    const poll = setInterval(function () {
        fetch('/pages/etudiant/paiement-statut.php?reference=' + encodeURIComponent(reference))
            .then(r => r.json())
            .then(data => {
                if (data.statut === 'paye') {
                    clearInterval(poll);
                    window.location.href = '/pages/etudiant/dashboard.php';
                } else if (data.statut === 'echoue') {
                    clearInterval(poll);
                    window.location.href = '/pages/etudiant/abonnement.php?erreur=paiement';
                }
            })
            .catch(() => {});
    }, 3000);
});
</script>
<?php endif; ?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
