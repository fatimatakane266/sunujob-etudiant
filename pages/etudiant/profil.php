<?php
/**
 * Profil Étudiant
 * SunuJob Étudiant
 */

$pageTitle = 'Mon profil - SunuJob Étudiant';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('etudiant');

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$etudiantId = $_SESSION['user_id'];
$user = getUserComplet($etudiantId);
$profilEtudiant = getProfilEtudiant($etudiantId);

$erreurs = [];
$success = false;
$erreursMdp = [];
$successMdp = false;

// Traitement du changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'changer_mot_de_passe') {
    exigerCsrfPost('/pages/etudiant/profil.php');

    $resultMdp = changerMotDePasse(
        $etudiantId,
        $_POST['mot_de_passe_actuel'] ?? '',
        $_POST['nouveau_mot_de_passe'] ?? '',
        $_POST['confirmation_mot_de_passe'] ?? ''
    );

    if ($resultMdp['succes']) {
        $successMdp = true;
    } else {
        $erreursMdp = $resultMdp['erreurs'];
    }
}

// Traitement du formulaire de profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'changer_mot_de_passe') {
    exigerCsrfPost('/pages/etudiant/profil.php');

    $donnees = [
        'universite' => trim($_POST['universite'] ?? ''),
        'niveau_etude' => trim($_POST['niveau_etude'] ?? ''),
        'filiere' => trim($_POST['filiere'] ?? ''),
        'competences' => trim($_POST['competences'] ?? ''),
        'disponibilite' => trim($_POST['disponibilite'] ?? ''),
        'bio' => trim($_POST['bio'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? ''),
        'localisation' => trim($_POST['localisation'] ?? '')
    ];

    // Upload photo
    $photo = $user['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errPhoto = validerFichierUpload($_FILES['photo'], ['jpg', 'jpeg', 'png', 'gif'], 2 * 1024 * 1024, 'Photo');
        if ($errPhoto) {
            $erreurs[] = $errPhoto;
        } else {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $newName = 'photo_' . $etudiantId . '_' . time() . '.' . $ext;
            $destination = $_SERVER['DOCUMENT_ROOT'] . '/uploads/photos/' . $newName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                $photo = $newName;
                $_SESSION['user_photo'] = $photo;
            }
        }
        }
    }

    // Upload CV
    $cv = $profilEtudiant['cv'] ?? null;
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errCv = validerFichierUpload($_FILES['cv'], ['pdf', 'doc', 'docx'], 5 * 1024 * 1024, 'CV');
        if ($errCv) {
            $erreurs[] = $errCv;
        } else {
        $allowed = ['pdf', 'doc', 'docx'];
        $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $newName = 'cv_' . $etudiantId . '_' . time() . '.' . $ext;
            $destination = $_SERVER['DOCUMENT_ROOT'] . '/uploads/cv/' . $newName;

            if (move_uploaded_file($_FILES['cv']['tmp_name'], $destination)) {
                $cv = $newName;
            }
        }
        }
    }

    if (empty($erreurs)) {
        // Mettre à jour le profil étudiant
        $stmt = $conn->prepare("
            UPDATE profils_etudiants
            SET universite = ?, niveau_etude = ?, filiere = ?, competences = ?, disponibilite = ?, bio = ?, cv = ?
            WHERE utilisateur_id = ?
        ");
        $stmt->bind_param("sssssssi",
            $donnees['universite'],
            $donnees['niveau_etude'],
            $donnees['filiere'],
            $donnees['competences'],
            $donnees['disponibilite'],
            $donnees['bio'],
            $cv,
            $etudiantId
        );

        // Mettre à jour l'utilisateur
        $stmt2 = $conn->prepare("UPDATE utilisateurs SET telephone = ?, localisation = ?, photo = ? WHERE id = ?");
        $stmt2->bind_param("sssi", $donnees['telephone'], $donnees['localisation'], $photo, $etudiantId);

        if ($stmt->execute() && $stmt2->execute()) {
            $_SESSION['user_localisation'] = $donnees['localisation'];
            $_SESSION['user_telephone']    = $donnees['telephone'];
            $_SESSION['user_photo']        = $photo;
            $success = true;
            $profilEtudiant = getProfilEtudiant($etudiantId);
            $user = getUserComplet($etudiantId);
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
        <h1><i class="fas fa-user-cog me-2"></i>Mon profil</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="/pages/etudiant/dashboard.php">Dashboard</a></li>
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
                    <?= champCsrf() ?>
                    <!-- Photo de profil -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <?php if ($user['photo']): ?>
                                <img src="/uploads/photos/<?= htmlspecialchars($user['photo']) ?>" alt="Photo" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px; background: var(--gradient-primary);">
                                    <i class="fas fa-user fa-3x text-white"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <label for="photo" class="btn btn-outline-custom">
                            <i class="fas fa-camera me-2"></i>Changer la photo
                        </label>
                        <input type="file" id="photo" name="photo" accept="image/*" class="d-none">
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
                        <input type="text" class="form-control" id="localisation" name="localisation" value="<?= htmlspecialchars($user['localisation'] ?? '') ?>" placeholder="Ex: Dakar, Sacré-Cœur">
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3"><i class="fas fa-graduation-cap me-2"></i>Informations académiques</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="universite" class="form-label">Université / École</label>
                            <input type="text" class="form-control" id="universite" name="universite" value="<?= htmlspecialchars($profilEtudiant['universite'] ?? '') ?>" placeholder="Ex: UCAD, ENEA...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="niveau_etude" class="form-label">Niveau d'étude</label>
                            <select class="form-select" id="niveau_etude" name="niveau_etude">
                                <option value="">Sélectionner</option>
                                <option value="BAC" <?= ($profilEtudiant['niveau_etude'] ?? '') === 'BAC' ? 'selected' : '' ?>>BAC</option>
                                <option value="BTS" <?= ($profilEtudiant['niveau_etude'] ?? '') === 'BTS' ? 'selected' : '' ?>>BTS</option>
                                <option value="Licence 1" <?= ($profilEtudiant['niveau_etude'] ?? '') === 'Licence 1' ? 'selected' : '' ?>>Licence 1</option>
                                <option value="Licence 2" <?= ($profilEtudiant['niveau_etude'] ?? '') === 'Licence 2' ? 'selected' : '' ?>>Licence 2</option>
                                <option value="Licence 3" <?= ($profilEtudiant['niveau_etude'] ?? '') === 'Licence 3' ? 'selected' : '' ?>>Licence 3</option>
                                <option value="Master 1" <?= ($profilEtudiant['niveau_etude'] ?? '') === 'Master 1' ? 'selected' : '' ?>>Master 1</option>
                                <option value="Master 2" <?= ($profilEtudiant['niveau_etude'] ?? '') === 'Master 2' ? 'selected' : '' ?>>Master 2</option>
                                <option value="Doctorat" <?= ($profilEtudiant['niveau_etude'] ?? '') === 'Doctorat' ? 'selected' : '' ?>>Doctorat</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="filiere" class="form-label">Filière / Domaine d'études</label>
                        <input type="text" class="form-control" id="filiere" name="filiere" value="<?= htmlspecialchars($profilEtudiant['filiere'] ?? '') ?>" placeholder="Ex: Informatique, Marketing, Droit...">
                    </div>

                    <div class="mb-3">
                        <label for="competences" class="form-label">Compétences</label>
                        <textarea class="form-control" id="competences" name="competences" rows="3" placeholder="Ex: Microsoft Office, Communication, Gestion de projet..."><?= htmlspecialchars($profilEtudiant['competences'] ?? '') ?></textarea>
                        <small class="form-text">Séparez vos compétences par des virgules</small>
                    </div>

                    <div class="mb-3">
                        <label for="disponibilite" class="form-label">Disponibilité</label>
                        <input type="text" class="form-control" id="disponibilite" name="disponibilite" value="<?= htmlspecialchars($profilEtudiant['disponibilite'] ?? '') ?>" placeholder="Ex: Week-ends, Soirs après 18h, Vacances...">
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Présentation personnelle</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Parlez-nous de vous, vos motivations, vos expériences..."><?= htmlspecialchars($profilEtudiant['bio'] ?? '') ?></textarea>
                    </div>

                    <!-- CV -->
                    <div class="mb-4">
                        <label class="form-label">CV (PDF, DOC)</label>
                        <?php if ($profilEtudiant['cv'] ?? null): ?>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-file-pdf fa-2x text-danger me-2"></i>
                                <a href="/uploads/cv/<?= htmlspecialchars($profilEtudiant['cv']) ?>" target="_blank" class="text-decoration-none">
                                    Voir mon CV actuel
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="cv" name="cv" accept=".pdf,.doc,.docx">
                        <small class="form-text">PDF ou Word - 5Mo max</small>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                        <a href="/pages/etudiant/dashboard.php" class="btn btn-outline-custom btn-lg">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>

            <div class="mission-detail-card mt-4">
                <h5 class="mb-3"><i class="fas fa-lock me-2"></i>Changer le mot de passe</h5>

                <?php if ($successMdp): ?>
                    <div class="alert alert-success-custom mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Mot de passe mis à jour avec succès !
                    </div>
                <?php endif; ?>

                <?php if (!empty($erreursMdp)): ?>
                    <div class="alert alert-danger-custom mb-4" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($erreursMdp as $erreur): ?>
                                <li><?= htmlspecialchars($erreur) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= champCsrf() ?>
                    <input type="hidden" name="action" value="changer_mot_de_passe">
                    <div class="mb-3">
                        <label for="mot_de_passe_actuel" class="form-label">Mot de passe actuel</label>
                        <input type="password" class="form-control" id="mot_de_passe_actuel" name="mot_de_passe_actuel" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nouveau_mot_de_passe" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" minlength="6" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirmation_mot_de_passe" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe" minlength="6" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-key me-2"></i>Mettre à jour le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
