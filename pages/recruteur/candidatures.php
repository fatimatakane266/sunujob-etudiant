<?php
/**
 * Gestion des candidatures (Recruteur)
 * SunuJob Étudiant
 */

$pageTitle = 'Gérer les candidatures - SunuJob Étudiant';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('recruteur');

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$recruteurId = $_SESSION['user_id'];

// Filtres
$missionId = isset($_GET['mission']) ? (int)$_GET['mission'] : null;
$statut = isset($_GET['statut']) ? securiser($_GET['statut']) : '';

// Traitement acceptation/refus/suivi mission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['candidature_id'])) {
    exigerCsrfPost('/pages/recruteur/candidatures.php' . ($missionId ? "?mission=$missionId" : ''));

    $candId = (int)$_POST['candidature_id'];
    $action = $_POST['action'];

    if (in_array($action, ['acceptee', 'refusee', 'en_cours', 'terminee'])) {
        // Vérifier que la candidature appartient à une de ses missions
        $stmt = $conn->prepare("
            SELECT c.*, m.recruteur_id, m.id as mission_id_ref, m.places_disponibles, m.titre as mission_titre
            FROM candidatures c
            JOIN missions m ON c.mission_id = m.id
            WHERE c.id = ? AND m.recruteur_id = ?
        ");
        $stmt->bind_param("ii", $candId, $recruteurId);
        $stmt->execute();
        $cand = $stmt->get_result()->fetch_assoc();

        if ($cand) {
            if ($action === 'acceptee' && !peutAccepterCandidature((int)$cand['mission_id_ref'])) {
                $_SESSION['flash_message'] = "Impossible d'accepter : toutes les places sont pourvues pour cette mission.";
                $_SESSION['flash_type'] = 'danger';
            } else {
            $stmt = $conn->prepare("UPDATE candidatures SET statut = ? WHERE id = ?");
            $stmt->bind_param("si", $action, $candId);
            $stmt->execute();

            // Notification à l'étudiant
            $messagesNotif = [
                'acceptee'  => ['Candidature acceptée !', 'Votre candidature a été acceptée.', 'success'],
                'refusee'   => ['Candidature refusée', 'Votre candidature a été refusée.', 'info'],
                'en_cours'  => ['Mission en cours', 'Votre mission a démarré. Bon courage !', 'info'],
                'terminee'  => ['Mission terminée', 'Votre mission est marquée comme terminée.', 'success']
            ];
            [$titre, $message, $typeNotif] = $messagesNotif[$action];
            $lien = "/pages/etudiant/mes-candidatures.php";

            $stmtNotif = $conn->prepare("INSERT INTO notifications (utilisateur_id, type, titre, message, lien) VALUES (?, ?, ?, ?, ?)");
            $stmtNotif->bind_param("issss", $cand['etudiant_id'], $typeNotif, $titre, $message, $lien);
            $stmtNotif->execute();

            // Fermer la mission si toutes les places sont pourvues
            if ($action === 'acceptee' && !peutAccepterCandidature((int)$cand['mission_id_ref'])) {
                $stmtClose = $conn->prepare("UPDATE missions SET statut = 'fermee' WHERE id = ?");
                $mid = (int)$cand['mission_id_ref'];
                $stmtClose->bind_param("i", $mid);
                $stmtClose->execute();
            }

            $labelsAction = [
                'acceptee' => 'acceptée',
                'refusee' => 'refusée',
                'en_cours' => 'marquée en cours',
                'terminee' => 'marquée terminée'
            ];
            $_SESSION['flash_message'] = "Candidature " . $labelsAction[$action] . " avec succès.";
            $_SESSION['flash_type'] = 'success';
            }
        }
    }

    header('Location: /pages/recruteur/candidatures.php' . ($missionId ? "?mission=$missionId" : ''));
    exit;
}

// Récupérer les missions du recruteur
$stmt = $conn->prepare("SELECT id, titre FROM missions WHERE recruteur_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $recruteurId);
$stmt->execute();
$missionsRecruteur = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Construction de la requête
$where = "m.recruteur_id = ?";
$params = [$recruteurId];
$types = "i";

if ($missionId) {
    $where .= " AND c.mission_id = ?";
    $params[] = $missionId;
    $types .= "i";
}

if ($statut && in_array($statut, getStatutsCandidature())) {
    $where .= " AND c.statut = ?";
    $params[] = $statut;
    $types .= "s";
}

// Récupérer les candidatures
$stmt = $conn->prepare("
    SELECT c.*, m.titre as mission_titre, m.localisation, m.remuneration,
           u.nom as etudiant_nom, u.prenom as etudiant_prenom, u.email as etudiant_email, u.telephone as etudiant_telephone,
           pe.universite, pe.niveau_etude, pe.filiere, pe.competences, pe.cv, pe.bio
    FROM candidatures c
    JOIN missions m ON c.mission_id = m.id
    JOIN utilisateurs u ON c.etudiant_id = u.id
    LEFT JOIN profils_etudiants pe ON pe.utilisateur_id = u.id
    WHERE $where
    ORDER BY c.created_at DESC
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$candidatures = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageActive = 'dashboard';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-users me-2"></i>Gérer les candidatures</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="/pages/recruteur/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Candidatures</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <!-- Filtres -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="mission" class="form-label">Filtrer par mission</label>
            <select class="form-select" id="mission" onchange="location.href='?mission='+this.value+'&statut=<?= $statut ?>'">
                <option value="">Toutes les missions</option>
                <?php foreach ($missionsRecruteur as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $missionId == $m['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(substr($m['titre'], 0, 40)) ?>...
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="statut" class="form-label">Filtrer par statut</label>
            <select class="form-select" id="statut_filter" onchange="location.href='?mission=<?= $missionId ?>&statut='+this.value">
                <option value="">Tous les statuts</option>
                <option value="en_attente" <?= $statut === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                <option value="acceptee" <?= $statut === 'acceptee' ? 'selected' : '' ?>>Acceptées</option>
                <option value="en_cours" <?= $statut === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                <option value="terminee" <?= $statut === 'terminee' ? 'selected' : '' ?>>Terminées</option>
                <option value="refusee" <?= $statut === 'refusee' ? 'selected' : '' ?>>Refusées</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <a href="/pages/recruteur/candidatures.php" class="btn btn-outline-custom">
                <i class="fas fa-times me-2"></i>Réinitialiser
            </a>
        </div>
    </div>

    <?php if (empty($candidatures)): ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Aucune candidature</h4>
            <p class="text-muted mb-0">
                <?= $missionId || $statut ? "Aucune candidature ne correspond aux filtres." : "Vous n'avez reçu aucune candidature pour le moment." ?>
            </p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($candidatures as $cand): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card-dashboard h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background: var(--gradient-primary);">
                                        <span class="text-white fw-bold"><?= strtoupper(substr($cand['etudiant_prenom'], 0, 1) . substr($cand['etudiant_nom'], 0, 1)) ?></span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($cand['etudiant_prenom'] . ' ' . $cand['etudiant_nom']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($cand['etudiant_email']) ?></small>
                                    </div>
                                </div>
                                <span class="badge badge-<?= badgeClassStatutCandidature($cand['statut']) ?>">
                                    <?= libelleStatutCandidature($cand['statut']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Pour :</strong> <?= htmlspecialchars($cand['mission_titre']) ?></p>

                            <?php if ($cand['niveau_etude']): ?>
                                <p class="mb-1"><i class="fas fa-graduation-cap me-2 text-muted"></i><?= htmlspecialchars($cand['niveau_etude']) ?> - <?= htmlspecialchars($cand['filiere'] ?? '') ?></p>
                            <?php endif; ?>

                            <?php if ($cand['universite']): ?>
                                <p class="mb-1"><i class="fas fa-university me-2 text-muted"></i><?= htmlspecialchars($cand['universite']) ?></p>
                            <?php endif; ?>

                            <?php if ($cand['etudiant_telephone']): ?>
                                <p class="mb-1"><i class="fas fa-phone me-2 text-muted"></i><?= htmlspecialchars($cand['etudiant_telephone']) ?></p>
                            <?php endif; ?>

                            <?php if ($cand['message_motivation']): ?>
                                <div class="p-2 rounded mt-2" style="background: var(--color-bg);">
                                    <small class="text-muted">Message de motivation :</small>
                                    <p class="small mb-0"><?= htmlspecialchars(substr($cand['message_motivation'], 0, 150)) ?>...</p>
                                </div>
                            <?php endif; ?>

                            <?php if ($cand['cv']): ?>
                                <a href="/uploads/cv/<?= htmlspecialchars($cand['cv']) ?>" target="_blank" class="btn btn-outline-custom btn-sm mt-2">
                                    <i class="fas fa-file-pdf me-1"></i>Voir le CV
                                </a>
                            <?php endif; ?>

                            <p class="text-muted small mt-3 mb-0">
                                <i class="fas fa-calendar me-1"></i>
                                Postulé le <?= date('d/m/Y à H:i', strtotime($cand['created_at'])) ?>
                            </p>
                        </div>
                        <?php if ($cand['statut'] === 'en_attente'): ?>
                            <div class="card-footer bg-white border-0 py-3">
                                <div class="d-flex gap-2">
                                    <form method="POST" action="" class="flex-grow-1">
                                        <?= champCsrf() ?>
                                        <input type="hidden" name="candidature_id" value="<?= $cand['id'] ?>">
                                        <input type="hidden" name="action" value="acceptee">
                                        <button type="submit" class="btn btn-success-custom w-100 btn-sm">
                                            <i class="fas fa-check me-1"></i>Accepter
                                        </button>
                                    </form>
                                    <form method="POST" action="" class="flex-grow-1">
                                        <?= champCsrf() ?>
                                        <input type="hidden" name="candidature_id" value="<?= $cand['id'] ?>">
                                        <input type="hidden" name="action" value="refusee">
                                        <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                                            <i class="fas fa-times me-1"></i>Refuser
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php elseif (in_array($cand['statut'], ['acceptee', 'en_cours'])): ?>
                            <div class="card-footer bg-white border-0 py-3">
                                <div class="d-flex gap-2">
                                    <?php if ($cand['statut'] === 'acceptee'): ?>
                                        <form method="POST" action="" class="flex-grow-1">
                                        <?= champCsrf() ?>
                                            <input type="hidden" name="candidature_id" value="<?= $cand['id'] ?>">
                                            <input type="hidden" name="action" value="en_cours">
                                            <button type="submit" class="btn btn-primary-custom w-100 btn-sm">
                                                <i class="fas fa-play me-1"></i>En cours
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" action="" class="flex-grow-1">
                                        <?= champCsrf() ?>
                                        <input type="hidden" name="candidature_id" value="<?= $cand['id'] ?>">
                                        <input type="hidden" name="action" value="terminee">
                                        <button type="submit" class="btn btn-success-custom w-100 btn-sm">
                                            <i class="fas fa-flag-checkered me-1"></i>Terminée
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
