<?php
/**
 * Détail d'une mission
 * SunuJob Étudiant
 */

require_once 'includes/db.php';
require_once 'includes/auth.php';

$missionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$missionId) {
    header('Location: /missions.php');
    exit;
}

// Récupérer la mission
$stmt = $conn->prepare("
    SELECT m.*, c.nom as categorie_nom, c.icone,
           u.id as recruteur_id, u.nom as recruteur_nom, u.prenom as recruteur_prenom, u.email as recruteur_email,
           pr.nom_structure, pr.type_recruteur, pr.description as recruteur_description, pr.logo
    FROM missions m
    JOIN categories c ON m.categorie_id = c.id
    JOIN utilisateurs u ON m.recruteur_id = u.id
    LEFT JOIN profils_recruteurs pr ON pr.utilisateur_id = u.id
    WHERE m.id = ?
");
$stmt->bind_param("i", $missionId);
$stmt->execute();
$mission = $stmt->get_result()->fetch_assoc();

if (!$mission) {
    header('Location: /missions.php');
    exit;
}

$pageTitle = htmlspecialchars($mission['titre']) . ' - SunuJob Étudiant';
$pageActive = 'missions';

// Vérifier si l'étudiant a déjà postulé
$dejaPostule = false;
$candidature = null;

if (estConnecte() && aRole('etudiant')) {
    $stmt = $conn->prepare("SELECT * FROM candidatures WHERE etudiant_id = ? AND mission_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $missionId);
    $stmt->execute();
    $candidature = $stmt->get_result()->fetch_assoc();
    $dejaPostule = $candidature !== null;
}

// Traitement du formulaire de candidature
$messageSent = false;
$erreurCandidature = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && estConnecte() && aRole('etudiant') && !$dejaPostule) {
    $messageMotivation = trim($_POST['message_motivation'] ?? '');

    if (empty($messageMotivation)) {
        $erreurCandidature = "Veuillez rédiger un message de motivation.";
    } else {
        $messageMotivation = securiser($messageMotivation);

        $stmt = $conn->prepare("INSERT INTO candidatures (etudiant_id, mission_id, message_motivation) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $_SESSION['user_id'], $missionId, $messageMotivation);

        if ($stmt->execute()) {
            // Notification au recruteur
            $titre = "Nouvelle candidature";
            $message = "Un étudiant a postulé à votre mission : " . $mission['titre'];
            $lien = "/pages/recruteur/candidatures.php?mission=" . $missionId;

            $stmtNotif = $conn->prepare("INSERT INTO notifications (utilisateur_id, type, titre, message, lien) VALUES (?, 'info', ?, ?, ?)");
            $stmtNotif->bind_param("isss", $mission['recruteur_id'], $titre, $message, $lien);
            $stmtNotif->execute();

            $messageSent = true;
            $dejaPostule = true;
        } else {
            $erreurCandidature = "Erreur lors de l'envoi de la candidature.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="/missions.php">Missions</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars(substr($mission['titre'], 0, 30)) ?>...</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <div class="row">
        <!-- Détails de la mission -->
        <div class="col-lg-8 mb-4">
            <div class="mission-detail-card">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge-categorie">
                        <i class="fas <?= htmlspecialchars($mission['icone']) ?> me-1"></i>
                        <?= htmlspecialchars($mission['categorie_nom']) ?>
                    </span>
                    <span class="badge badge-<?= $mission['statut'] === 'active' ? 'active' : ($mission['statut'] === 'fermee' ? 'fermee' : 'expiree') ?>">
                        <?= ucfirst($mission['statut']) ?>
                    </span>
                </div>

                <h1 class="h2 mb-3"><?= htmlspecialchars($mission['titre']) ?></h1>

                <!-- Métadonnées -->
                <div class="mission-meta mb-4">
                    <div class="mission-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($mission['localisation']) ?></span>
                    </div>
                    <div class="mission-meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?= $mission['type_mission'] === 'ponctuelle' ? 'Ponctuelle' : ($mission['type_mission'] === 'temps_partiel' ? 'Temps partiel' : 'Stage') ?></span>
                    </div>
                    <div class="mission-meta-item">
                        <i class="fas fa-users"></i>
                        <span><?= $mission['places_disponibles'] ?> place<?= $mission['places_disponibles'] > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="mission-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>Publié le <?= date('d/m/Y', strtotime($mission['created_at'])) ?></span>
                    </div>
                </div>

                <!-- Rémunération -->
                <div class="d-flex align-items-center mb-4 p-3 rounded" style="background: rgba(45, 155, 78, 0.1);">
                    <i class="fas fa-coins fa-2x me-3" style="color: var(--color-accent-green);"></i>
                    <div>
                        <small class="text-muted">Rémunération</small>
                        <div class="remuneration h4 mb-0">
                            <?= $mission['remuneration'] ? number_format($mission['remuneration'], 0, ',', ' ') . ' FCFA' : 'À négocier' ?>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <?php if ($mission['date_debut'] || $mission['date_fin']): ?>
                    <div class="row mb-4">
                        <?php if ($mission['date_debut']): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 rounded border mb-3">
                                    <i class="fas fa-play-circle fa-lg me-3 text-primary"></i>
                                    <div>
                                        <small class="text-muted">Date de début</small>
                                        <div class="fw-semibold"><?= date('d/m/Y', strtotime($mission['date_debut'])) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($mission['date_fin']): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 rounded border mb-3">
                                    <i class="fas fa-stop-circle fa-lg me-3 text-danger"></i>
                                    <div>
                                        <small class="text-muted">Date de fin</small>
                                        <div class="fw-semibold"><?= date('d/m/Y', strtotime($mission['date_fin'])) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Description -->
                <h5 class="mb-3">Description de la mission</h5>
                <div class="mb-4">
                    <?= nl2br(htmlspecialchars($mission['description'])) ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Postuler -->
            <?php if ($mission['statut'] === 'active'): ?>
                <div class="filter-card mb-4">
                    <?php if (!estConnecte()): ?>
                        <div class="text-center">
                            <p class="mb-3">Connectez-vous pour postuler à cette mission.</p>
                            <a href="/login.php?redirect=/mission-detail.php?id=<?= $missionId ?>" class="btn btn-primary-custom w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Connexion
                            </a>
                            <hr>
                            <p class="mb-2">Pas encore de compte ?</p>
                            <a href="/register.php?role=etudiant" class="btn btn-outline-custom w-100">
                                <i class="fas fa-user-plus me-2"></i>S'inscrire
                            </a>
                        </div>
                    <?php elseif (aRole('recruteur')): ?>
                        <div class="text-center">
                            <p class="text-muted">Vous êtes connecté en tant que recruteur.</p>
                            <a href="/pages/recruteur/mes-missions.php" class="btn btn-primary-custom w-100">
                                <i class="fas fa-list me-2"></i>Mes missions
                            </a>
                        </div>
                    <?php elseif ($messageSent): ?>
                        <div class="alert alert-success-custom text-center">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p class="mb-0">Candidature envoyée avec succès !</p>
                        </div>
                    <?php elseif ($dejaPostule): ?>
                        <div class="text-center">
                            <p class="mb-2"><i class="fas fa-check-circle text-success fa-2x mb-3"></i></p>
                            <p class="fw-semibold">Vous avez déjà postulé</p>
                            <span class="badge badge-<?= $candidature['statut'] === 'en_attente' ? 'attente' : ($candidature['statut'] === 'acceptee' ? 'acceptee' : 'refusee') ?>">
                                <?= $candidature['statut'] === 'en_attente' ? 'En attente' : ($candidature['statut'] === 'acceptee' ? 'Acceptée' : 'Refusée') ?>
                            </span>
                            <hr>
                            <a href="/pages/etudiant/mes-candidatures.php" class="btn btn-outline-custom w-100">
                                Voir mes candidatures
                            </a>
                        </div>
                    <?php else: ?>
                        <?php if ($erreurCandidature): ?>
                            <div class="alert alert-danger-custom mb-3">
                                <?= htmlspecialchars($erreurCandidature) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <h5 class="mb-3"><i class="fas fa-paper-plane me-2"></i>Postuler</h5>
                            <div class="mb-3">
                                <label class="form-label">Message de motivation</label>
                                <textarea class="form-control" name="message_motivation" rows="5" required placeholder="Expliquez pourquoi vous êtes le candidat idéal pour cette mission..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-cta w-100">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer ma candidature
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="filter-card mb-4 text-center">
                    <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                    <p class="fw-semibold">Cette mission n'est plus disponible</p>
                </div>
            <?php endif; ?>

            <!-- Info recruteur -->
            <div class="filter-card">
                <h5 class="mb-3"><i class="fas fa-building me-2"></i>Recruteur</h5>
                <div class="d-flex align-items-center mb-3">
                    <?php if ($mission['logo']): ?>
                        <img src="/uploads/logos/<?= htmlspecialchars($mission['logo']) ?>" alt="Logo" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: var(--gradient-primary);">
                            <i class="fas fa-building text-white fa-lg"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h6 class="mb-0"><?= $mission['nom_structure'] ? htmlspecialchars($mission['nom_structure']) : htmlspecialchars($mission['recruteur_prenom'] . ' ' . $mission['recruteur_nom']) ?></h6>
                        <?php if ($mission['type_recruteur']): ?>
                            <small class="text-muted"><?= ucfirst($mission['type_recruteur']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($mission['recruteur_description']): ?>
                    <p class="text-muted small"><?= htmlspecialchars(substr($mission['recruteur_description'], 0, 200)) ?>...</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
