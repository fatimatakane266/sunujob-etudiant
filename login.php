<?php
/**
 * Page de connexion
 * SunuJob Étudiant
 */

$pageTitle = 'Connexion - SunuJob Étudiant';

require_once 'includes/auth.php';

// Rediriger si déjà connecté
if (estConnecte()) {
    if (aRole('admin')) {
        $redirect = '/pages/admin/dashboard.php';
    } elseif (aRole('etudiant')) {
        $redirect = '/pages/etudiant/dashboard.php';
    } else {
        $redirect = '/pages/recruteur/dashboard.php';
    }
    header('Location: ' . $redirect);
    exit;
}

$erreur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigerCsrfPost('/login.php');

    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    $result = connecter($email, $motDePasse);

    if ($result['succes']) {
        if ($result['user']['role'] === 'admin') {
            $redirect = '/pages/admin/dashboard.php';
        } elseif ($result['user']['role'] === 'etudiant') {
            $redirect = '/pages/etudiant/dashboard.php';
        } else {
            $redirect = '/pages/recruteur/dashboard.php';
        }
        header('Location: ' . $redirect);
        exit;
    } else {
        $erreur = $result['erreur'];
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
                <img src="/assets/images/logo.svg" alt="SunuJob Étudiant">
            </div>

            <h2>Connexion</h2>

            <?php if ($erreur): ?>
                <div class="alert alert-danger-custom mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= champCsrf() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required placeholder="votre@email.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="mot_de_passe" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required placeholder="Votre mot de passe">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="/forgot-password.php" class="text-decoration-none">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                </button>
            </form>

            <div class="auth-divider">
                <span>ou</span>
            </div>

            <p class="text-center mt-3">
                Pas encore de compte ?
                <a href="/register.php" class="fw-bold">S'inscrire</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('mot_de_passe');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>
</html>
