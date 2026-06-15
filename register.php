<?php
/**
 * Page d'inscription
 * SunuJob Étudiant
 */

$pageTitle = 'Inscription - SunuJob Étudiant';

require_once 'includes/auth.php';

// Rediriger si déjà connecté
if (estConnecte()) {
    $redirect = aRole('etudiant') ? '/pages/etudiant/dashboard.php' : '/pages/recruteur/dashboard.php';
    header('Location: ' . $redirect);
    exit;
}

$erreurs = [];
$donnees = [
    'nom' => '',
    'prenom' => '',
    'email' => '',
    'telephone' => '',
    'localisation' => '',
    'role' => $_GET['role'] ?? 'etudiant'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donnees = [
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? ''),
        'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
        'mot_de_passe_confirm' => $_POST['mot_de_passe_confirm'] ?? '',
        'localisation' => trim($_POST['localisation'] ?? ''),
        'role' => $_POST['role'] ?? 'etudiant'
    ];

    // Validation supplémentaire
    if ($donnees['mot_de_passe'] !== $donnees['mot_de_passe_confirm']) {
        $erreurs[] = "Les mots de passe ne correspondent pas.";
    }

    if (empty($erreurs)) {
        $result = inscrire($donnees);

        if ($result['succes']) {
            // Connecter automatiquement l'utilisateur
            connecter($donnees['email'], $donnees['mot_de_passe']);
            $redirect = $donnees['role'] === 'etudiant' ? '/pages/etudiant/dashboard.php' : '/pages/recruteur/dashboard.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $erreurs = $result['erreurs'];
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
    <div class="auth-container py-4">
        <div class="auth-card" style="max-width: 550px;">
            <div class="logo">
                <img src="/assets/images/logo.png" alt="SunuJob Étudiant">
            </div>

            <h2>Créer un compte</h2>

            <?php if (!empty($erreurs)): ?>
                <div class="alert alert-danger-custom mb-4" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($erreurs as $erreur): ?>
                            <li><?= htmlspecialchars($erreur) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Sélection du type de compte -->
            <div class="mb-4">
                <div class="row g-2">
                    <div class="col-6">
                        <input type="radio" class="btn-check" name="role_select" id="role_etudiant" value="etudiant" <?= $donnees['role'] === 'etudiant' ? 'checked' : '' ?> onchange="selectRole('etudiant')">
                        <label class="btn btn-outline-custom w-100 py-3" for="role_etudiant">
                            <i class="fas fa-user-graduate d-block fs-3 mb-2"></i>
                            <span class="fw-semibold">Étudiant</span>
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check" name="role_select" id="role_recruteur" value="recruteur" <?= $donnees['role'] === 'recruteur' ? 'checked' : '' ?> onchange="selectRole('recruteur')">
                        <label class="btn btn-outline-custom w-100 py-3" for="role_recruteur">
                            <i class="fas fa-building d-block fs-3 mb-2"></i>
                            <span class="fw-semibold">Recruteur</span>
                        </label>
                    </div>
                </div>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="role" id="role_input" value="<?= htmlspecialchars($donnees['role']) ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="prenom" class="form-label">Prénom *</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($donnees['prenom']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nom" class="form-label">Nom *</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($donnees['nom']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email *</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($donnees['email']) ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="telephone" class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($donnees['telephone']) ?>" placeholder="77 123 45 67">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="localisation" class="form-label">Localisation</label>
                        <input type="text" class="form-control" id="localisation" name="localisation" value="<?= htmlspecialchars($donnees['localisation']) ?>" placeholder="Dakar, Thiès...">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mot_de_passe" class="form-label">Mot de passe *</label>
                        <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required minlength="6">
                        <div class="form-text">Minimum 6 caractères</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mot_de_passe_confirm" class="form-label">Confirmer le mot de passe *</label>
                        <input type="password" class="form-control" id="mot_de_passe_confirm" name="mot_de_passe_confirm" required>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input" id="cgu" name="cgu" required>
                    <label class="form-check-label" for="cgu">
                        J'accepte les <a href="/mentions-legales.php" target="_blank">conditions d'utilisation</a> *
                    </label>
                </div>

                <button type="submit" class="btn btn-cta w-100 mb-3">
                    <i class="fas fa-user-plus me-2"></i>Créer mon compte
                </button>
            </form>

            <div class="auth-divider">
                <span>ou</span>
            </div>

            <p class="text-center mt-3">
                Déjà inscrit ?
                <a href="/login.php" class="fw-bold">Se connecter</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectRole(role) {
            document.getElementById('role_input').value = role;

            // Update button states
            document.getElementById('role_etudiant').checked = role === 'etudiant';
            document.getElementById('role_recruteur').checked = role === 'recruteur';
        }

        // Password confirmation validation
        document.getElementById('mot_de_passe_confirm').addEventListener('input', function() {
            const password = document.getElementById('mot_de_passe').value;
            if (this.value !== password) {
                this.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
