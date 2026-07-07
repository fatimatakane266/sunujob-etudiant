<?php
/**
 * Mes candidatures (Étudiant)
 * SunuJob Étudiant
 */

$pageTitle = 'Mes candidatures - SunuJob Étudiant';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('etudiant');

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$etudiantId = $_SESSION['user_id'];

// Filtre par statut
$statut = isset($_GET['statut']) ? securiser($_GET['statut']) : '';

// Requête
$where = "c.etudiant_id = ?";
$params = [$etudiantId];
$types = "i";

if ($statut && in_array($statut, getStatutsCandidature())) {
    $where .= " AND c.statut = ?";
    $params[] = $statut;
    $types .= "s";
}

$stmt = $conn->prepare("
    SELECT c.*, m.titre, m.localisation, m.remuneration, m.type_mission, m.statut as mission_statut,
           cat.nom as categorie_nom, cat.icone,
           u.nom as recruteur_nom, u.prenom as recruteur_prenom, pr.nom_structure
    FROM candidatures c
    JOIN missions m ON c.mission_id = m.id
    JOIN categories cat ON m.categorie_id = cat.id
    JOIN utilisateurs u ON m.recruteur_id = u.id
    LEFT JOIN profils_recruteurs pr ON pr.utilisateur_id = u.id
    WHERE $where
    ORDER BY c.created_at DESC
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$candidatures = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Statistiques
$stats = [
    'total' => 0,
    'en_attente' => 0,
    'acceptee' => 0,
    'en_cours' => 0,
    'terminee' => 0,
    'refusee' => 0
];

$stmt = $conn->prepare("SELECT statut, COUNT(*) as nb FROM candidatures WHERE etudiant_id = ? GROUP BY statut");
$stmt->bind_param("i", $etudiantId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $stats[$row['statut']] = $row['nb'];
    $stats['total'] += $row['nb'];
}

$pageActive = 'dashboard';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-paper-plane me-2"></i>Mes candidatures</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="/pages/etudiant/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Candidatures</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <!-- Filtres -->
    <div class="row g-3 mb-4">
        <div class="col-auto">
            <a href="/pages/etudiant/mes-candidatures.php" class="btn <?= !$statut ? 'btn-primary-custom' : 'btn-outline-custom' ?>">
                Toutes <span class="badge bg-secondary ms-1"><?= $stats['total'] ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="?statut=en_attente" class="btn <?= $statut === 'en_attente' ? 'btn-primary-custom' : 'btn-outline-custom' ?>">
                En attente <span class="badge bg-warning ms-1"><?= $stats['en_attente'] ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="?statut=acceptee" class="btn <?= $statut === 'acceptee' ? 'btn-success-custom' : 'btn-outline-custom' ?>">
                Acceptées <span class="badge bg-success ms-1"><?= $stats['acceptee'] ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="?statut=en_cours" class="btn <?= $statut === 'en_cours' ? 'btn-primary-custom' : 'btn-outline-custom' ?>">
                En cours <span class="badge bg-primary ms-1"><?= $stats['en_cours'] ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="?statut=terminee" class="btn <?= $statut === 'terminee' ? 'btn-success-custom' : 'btn-outline-custom' ?>">
                Terminées <span class="badge bg-success ms-1"><?= $stats['terminee'] ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="?statut=refusee" class="btn <?= $statut === 'refusee' ? 'btn-primary-custom' : 'btn-outline-custom' ?>">
                Refusées <span class="badge bg-danger ms-1"><?= $stats['refusee'] ?></span>
            </a>
        </div>
    </div>

    <?php if (empty($candidatures)): ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Aucune candidature</h4>
            <p class="text-muted mb-4">
                <?= $statut ? "Aucune candidature avec ce statut." : "Vous n'avez encore postulé à aucune mission." ?>
            </p>
            <a href="/missions.php" class="btn btn-cta-etudiant">
                <i class="fas fa-search me-2"></i>Rechercher des missions
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($candidatures as $cand): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card-dashboard p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge-categorie">
                                <i class="fas <?= htmlspecialchars($cand['icone']) ?> me-1"></i>
                                <?= htmlspecialchars($cand['categorie_nom']) ?>
                            </span>
                            <span class="badge badge-<?= badgeClassStatutCandidature($cand['statut']) ?>">
                                <?= libelleStatutCandidature($cand['statut']) ?>
                            </span>
                        </div>

                        <h5 class="mb-2"><?= htmlspecialchars($cand['titre']) ?></h5>

                        <p class="text-muted small mb-2">
                            <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($cand['localisation']) ?>
                            <span class="ms-2"><i class="fas fa-coins me-1"></i>
                                <?= $cand['remuneration'] ? number_format($cand['remuneration'], 0, ',', ' ') . ' FCFA' : 'À négocier' ?>
                            </span>
                        </p>

                        <p class="text-muted small mb-3">
                            <i class="fas fa-building me-1"></i>
                            <?= $cand['nom_structure'] ? htmlspecialchars($cand['nom_structure']) : htmlspecialchars($cand['recruteur_prenom'] . ' ' . $cand['recruteur_nom']) ?>
                        </p>

                        <?php if ($cand['message_motivation']): ?>
                            <div class="p-2 rounded mb-3" style="background: var(--color-bg);">
                                <small class="text-muted">Message de motivation :</small>
                                <p class="small mb-0"><?= htmlspecialchars(substr($cand['message_motivation'], 0, 100)) ?>...</p>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array($cand['statut'], ['acceptee', 'en_cours'])): ?>
                            <div class="alert alert-success-custom mb-3" role="alert">
                                <div class="fw-semibold mb-1"><i class="fas fa-check-circle me-2"></i>Mission acceptée</div>
                                <small class="d-block mb-2">Vous êtes désormais retenu pour cette mission. Voici la suite à suivre :</small>
                                <ul class="mb-0 ps-3 small">
                                    <li>Présentez-vous à <strong><?= htmlspecialchars($cand['localisation']) ?></strong></li>
                                    <?php if (!empty($cand['jours_travail'])): ?><li>Jours : <strong><?= htmlspecialchars($cand['jours_travail']) ?></strong></li><?php endif; ?>
                                    <?php if (!empty($cand['heures_travail'])): ?><li>Horaires : <strong><?= htmlspecialchars($cand['heures_travail']) ?></strong></li><?php endif; ?>
                                    <li>Apportez votre pièce d'identité, votre carte d'étudiant et votre CV.</li>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d/m/Y', strtotime($cand['created_at'])) ?>
                            </small>
                            <a href="/mission-detail.php?id=<?= $cand['mission_id'] ?>" class="btn btn-primary-custom btn-sm">
                                Voir la mission <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
