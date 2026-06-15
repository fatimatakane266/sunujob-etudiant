<?php
/**
 * Mot de passe oublié
 * SunuJob Étudiant
 */

$pageTitle = 'Mot de passe oublié - SunuJob Étudiant';

require_once 'includes/auth.php';

$erreur = '';
$success = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $erreur = "Veuillez saisir votre adresse email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "L'adresse email n'est pas valide.";
    } else {
        $token = genererTokenReset($email);

        if ($token) {
            // En production, envoyer un email avec le lien
            // Pour le test, on affiche le lien directement
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;

            $success = "Un lien de réinitialisation a été généré. En production, un email serait envoyé.<br><br>";
            $success .= "<strong>Lien de test :</strong><br><a href='$resetLink'>$resetLink</a>";
        } else {
            $erreur = "Aucun compte n'est associé à cette adresse email.";
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

            <h2>Mot de passe oublié</h2>

            <?php if ($erreur): ?>
                <div class="alert alert-danger-custom mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success-custom mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <p class="text-muted mb-4">Entrez votre adresse email. Nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>

                <div class="mb-4">
                    <label for="email" class="form-label">Adresse email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required placeholder="votre@email.com">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                    <i class="fas fa-paper-plane me-2"></i>Envoyer le lien
                </button>
            </form>

            <p class="text-center mt-3">
                <a href="/login.php"><i class="fas fa-arrow-left me-1"></i>Retour à la connexion</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
