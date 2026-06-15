<?php
/**
 * Réinitialisation du mot de passe
 * SunuJob Étudiant
 */

$pageTitle = 'Réinitialiser le mot de passe - SunuJob Étudiant';

require_once 'includes/auth.php';

$token = $_GET['token'] ?? '';
$erreur = '';
$success = false;

if (empty($token)) {
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $motDePasseConfirm = $_POST['mot_de_passe_confirm'] ?? '';

    if (empty($motDePasse) || strlen($motDePasse) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($motDePasse !== $motDePasseConfirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        if (resetMotDePasse($token, $motDePasse)) {
            $success = true;
        } else {
            $erreur = "Le lien n'est pas valide ou a expiré.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="logo">
                <img src="/assets/images/logo.png" alt="SunuJob Étudiant">
            </div>

            <h2>Nouveau mot de passe</h2>

            <?php if ($erreur): ?>
                <div class="alert alert-danger-custom mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success-custom mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>Votre mot de passe a été réinitialisé avec succès.
                </div>
                <div class="text-center">
                    <a href="/login.php" class="btn btn-primary-custom">
                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required minlength="6">
                        <div class="form-text">Minimum 6 caractères</div>
                    </div>

                    <div class="mb-4">
                        <label for="mot_de_passe_confirm" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="mot_de_passe_confirm" name="mot_de_passe_confirm" required>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                        <i class="fas fa-key me-2"></i>Réinitialiser
                    </button>
                </form>
            <?php endif; ?>

            <p class="text-center mt-3">
                <a href="/login.php"><i class="fas fa-arrow-left me-1"></i>Retour à la connexion</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
