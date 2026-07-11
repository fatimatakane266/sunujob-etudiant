<?php
/**
 * Page Contact
 * SunuJob Étudiant
 */

$pageTitle = 'Contact - SunuJob Étudiant';
$pageActive = 'contact';

require_once 'includes/auth.php';
require_once 'includes/header.php';

$erreurs = [];
$success = false;
$donnees = ['nom' => '', 'email' => '', 'sujet' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigerCsrfPost('/contact.php');

    $donnees = [
        'nom' => trim($_POST['nom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'sujet' => trim($_POST['sujet'] ?? ''),
        'message' => trim($_POST['message'] ?? '')
    ];

    if (empty($donnees['nom'])) $erreurs[] = "Le nom est obligatoire.";
    if (empty($donnees['email']) || !filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email est invalide.";
    }
    if (empty($donnees['sujet'])) $erreurs[] = "Le sujet est obligatoire.";
    if (empty($donnees['message'])) $erreurs[] = "Le message est obligatoire.";

    if (empty($erreurs)) {
        // Préparer notification / email vers les administrateurs
        $success = false;

        // Titre et message destinés aux admins
        $titreNotif = 'Nouveau message contact';
        $messageNotif = "Message de {$donnees['nom']} ({$donnees['email']})\nSujet: {$donnees['sujet']}\n\n{$donnees['message']}";

        // Envoyer un email simple au contact principal (fallback)
        $adminEmail = 'contact@sunujob.sn';
        $subject = "[SunuJob] Nouveau message: " . $donnees['sujet'];
        $headers  = 'From: ' . $donnees['nom'] . " <" . $donnees['email'] . ">\r\n";
        $headers .= 'Reply-To: ' . $donnees['email'] . "\r\n";
        $headers .= 'Content-Type: text/plain; charset=UTF-8\r\n';

        @mail($adminEmail, $subject, $messageNotif, $headers);

        // Insérer une notification pour chaque administrateur présent en base
        if (isset($conn)) {
            $stmtAdmins = $conn->query("SELECT id, email, telephone FROM utilisateurs WHERE role = 'admin'");
            if ($stmtAdmins) {
                while ($admin = $stmtAdmins->fetch_assoc()) {
                    $uid = (int)$admin['id'];
                    $stmtNotif = $conn->prepare("INSERT INTO notifications (utilisateur_id, type, titre, message, lien) VALUES (?, ?, ?, ?, '')");
                    if ($stmtNotif) {
                        $type = 'info';
                        $stmtNotif->bind_param('isss', $uid, $type, $titreNotif, $messageNotif);
                        $stmtNotif->execute();
                    }

                    // Optionnel : envoi SMS via Twilio si configuré
                    if (!empty($admin['telephone']) && getenv('TWILIO_SID') && getenv('TWILIO_TOKEN') && getenv('TWILIO_FROM')) {
                        $sid = getenv('TWILIO_SID');
                        $token = getenv('TWILIO_TOKEN');
                        $from = getenv('TWILIO_FROM');
                        $to = preg_replace('/[^0-9+]/', '', $admin['telephone']);
                        $smsBody = "SunuJob: Nouveau message de {$donnees['nom']} ({$donnees['email']}) - Sujet: {$donnees['sujet']}.";

                        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
                        $data = http_build_query(['From' => $from, 'To' => $to, 'Body' => $smsBody]);

                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $token);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        $res = curl_exec($ch);
                        curl_close($ch);
                        // Ne faisons rien du résultat ici; log si besoin
                    }
                }
            }
        }

        // Tout s'est bien passé côté utilisateur
        $success = true;
        $donnees = ['nom' => '', 'email' => '', 'sujet' => '', 'message' => ''];
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-envelope me-2"></i>Nous contacter</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                <li class="breadcrumb-item active">Contact</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="mission-detail-card contact-card">
                <?php if ($success): ?>
                    <div class="alert alert-success-custom mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.
                    </div>
                <?php endif; ?>

                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger-custom mb-4" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?= htmlspecialchars($erreur) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <h5 class="mb-4">Nos coordonnées</h5>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-envelope me-3" style="color: var(--color-accent-gold); width: 24px;"></i>
                                <span>contact@sunujob.sn</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-phone me-3" style="color: var(--color-accent-gold); width: 24px;"></i>
                                <span>+221 77 123 45 67</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-map-marker-alt me-3" style="color: var(--color-accent-gold); width: 24px;"></i>
                                <span>Dakar, Sénégal</span>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3">Suivez-nous</h6>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-custom btn-sm"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-outline-custom btn-sm"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="btn btn-outline-custom btn-sm"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="btn btn-outline-custom btn-sm"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <form method="POST" action="">
                            <?= champCsrf() ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($donnees['nom']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($donnees['email']) ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="sujet" class="form-label">Sujet *</label>
                                <select class="form-select" id="sujet" name="sujet" required>
                                    <option value="">Sélectionner</option>
                                    <option value="question" <?= $donnees['sujet'] === 'question' ? 'selected' : '' ?>>Question générale</option>
                                    <option value="probleme" <?= $donnees['sujet'] === 'probleme' ? 'selected' : '' ?>>Signaler un problème</option>
                                    <option value="partenariat" <?= $donnees['sujet'] === 'partenariat' ? 'selected' : '' ?>>Partenariat</option>
                                    <option value="autre" <?= $donnees['sujet'] === 'autre' ? 'selected' : '' ?>>Autre</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Votre message..."><?= htmlspecialchars($donnees['message']) ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
