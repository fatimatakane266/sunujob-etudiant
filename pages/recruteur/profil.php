<?php
/**
 * Profil Recruteur
 * SunuJob Étudiant
 */

$pageTitle = 'Mon profil entreprise - SunuJob Étudiant';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('recruteur');

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$recruteurId = $_SESSION['user_id'];
$user = getUserComplet($recruteurId);
$profilRecruteur = getProfilRecruteur($recruteurId);

$erreurs = [];
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donnees = [
        'nom_structure' => trim($_POST['nom_structure'] ?? ''),
        'type_recruteur' => trim($_POST['type_recruteur'] ?? ''),
        'site_web' => trim($_POST['site_web'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? ''),
        'localisation' => trim($_POST['localisation'] ?? '')
    ];

    // Upload logo
    $logo = $profilRecruteur['logo'] ?? null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $newName = 'logo_' . $recruteurId . '_' . time() . '.' . $ext;
            $destination = $_SERVER['DOCUMENT_ROOT'] . '/uploads/logos/' . $newName;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $destination)) {
                $logo = $newName;
            }
        } else {
            $erreurs[] = "Format de logo non autorisé (jpg, png, gif uniquement).";
        }
    }

    if (empty($erreurs)) {
        // Mettre à jour le profil recruteur
        $stmt = $conn->prepare("
            UPDATE profils_recruteurs
            SET nom_structure = ?, type_recruteur = ?, site_web = ?, description = ?, logo = ?
            WHERE utilisateur_id = ?
        ");
        $stmt->bind_param("sssssi",
            $donnees['nom_structure'],
            $donnees['type_recruteur'],
            $donnees['site_web'],
            $donnees['description'],
            $logo,
            $recruteurId
        );

        // Mettre à jour l'utilisateur
        $stmt2 = $conn->prepare("UPDATE utilisateurs SET telephone = ?, localisation = ? WHERE id = ?");
        $stmt2->bind_param("ssi", $donnees['telephone'], $donnees['localisation'], $recruteurId);

        if ($stmt->execute() && $stmt2->execute()) {
            $_SESSION['user_localisation'] = $donnees['localisation'];
            $_SESSION['user_telephone']    = $donnees['telephone'];
            $success = true;
            $profilRecruteur = getProfilRecruteur($recruteurId);
            $user = getUserComplet($recruteurId);
        } else {
            $erreurs[] = "Erreur lors de la mise à jour du profil.";
        }
    }
}

$pageActive = 'dashboard';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-building me-2"></i>Mon profil entreprise</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="/pages/recruteur/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Profil</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mission-detail-card">
                <?php if ($success): ?>
                    <div class="alert alert-success-custom mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Profil mis à jour avec succès !
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

                <form method="POST" action="" enctype="multipart/form-data">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <?php if ($profilRecruteur['logo'] ?? null): ?>
                                <img src="/uploads/logos/<?= htmlspecialchars($profilRecruteur['logo']) ?>" alt="Logo" class="rounded" style="width: 120px; height: 120px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px; background: var(--gradient-primary);">
                                    <i class="fas fa-building fa-3x text-white"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <label for="logo" class="btn btn-outline-custom">
                            <i class="fas fa-image me-2"></i>Changer le logo
                        </label>
                        <input type="file" id="logo" name="logo" accept="image/*" class="d-none">
                        <small class="d-block text-muted mt-2">JPG, PNG ou GIF - 2Mo max</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="localisation" class="form-label">Localisation</label>
                        <input type="text" class="form-control" id="localisation" name="localisation" value="<?= htmlspecialchars($user['localisation'] ?? '') ?>" placeholder="Ex: Dakar, Plateau">
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Informations entreprise</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom_structure" class="form-label">Nom de la structure</label>
                            <input type="text" class="form-control" id="nom_structure" name="nom_structure" value="<?= htmlspecialchars($profilRecruteur['nom_structure'] ?? '') ?>" placeholder="Ex: SunuTech SARL">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type_recruteur" class="form-label">Type de structure</label>
                            <select class="form-select" id="type_recruteur" name="type_recruteur">
                                <option value="">Sélectionner</option>
                                <option value="entreprise" <?= ($profilRecruteur['type_recruteur'] ?? '') === 'entreprise' ? 'selected' : '' ?>>Entreprise</option>
                                <option value="startup" <?= ($profilRecruteur['type_recruteur'] ?? '') === 'startup' ? 'selected' : '' ?>>Startup</option>
                                <option value="agence" <?= ($profilRecruteur['type_recruteur'] ?? '') === 'agence' ? 'selected' : '' ?>>Agence</option>
                                <option value="commercant" <?= ($profilRecruteur['type_recruteur'] ?? '') === 'commercant' ? 'selected' : '' ?>>Commerçant</option>
                                <option value="particulier" <?= ($profilRecruteur['type_recruteur'] ?? '') === 'particulier' ? 'selected' : '' ?>>Particulier</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="site_web" class="form-label">Site web</label>
                        <input type="url" class="form-control" id="site_web" name="site_web" value="<?= htmlspecialchars($profilRecruteur['site_web'] ?? '') ?>" placeholder="https://www.monentreprise.com">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Description de l'activité</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Décrivez votre entreprise, votre activité, vos valeurs..."><?= htmlspecialchars($profilRecruteur['description'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                        <a href="/pages/recruteur/dashboard.php" class="btn btn-outline-custom btn-lg">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
