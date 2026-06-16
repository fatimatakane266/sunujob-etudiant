<?php
/**
 * Dashboard Étudiant
 * SunuJob Étudiant
 */

$pageTitle = 'Mon espace étudiant - SunuJob Étudiant';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('etudiant');

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$etudiantId = $_SESSION['user_id'];

// Statistiques
$stats = [
    'total_candidatures' => 0,
    'en_attente' => 0,
    'acceptees' => 0,
    'en_cours' => 0,
    'terminees' => 0,
    'refusees' => 0
];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM candidatures WHERE etudiant_id = ?");
$stmt->bind_param("i", $etudiantId);
$stmt->execute();
$stats['total_candidatures'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM candidatures WHERE etudiant_id = ? AND statut = 'en_attente'");
$stmt->bind_param("i", $etudiantId);
$stmt->execute();
$stats['en_attente'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM candidatures WHERE etudiant_id = ? AND statut = 'acceptee'");
$stmt->bind_param("i", $etudiantId);
$stmt->execute();
$stats['acceptees'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM candidatures WHERE etudiant_id = ? AND statut = 'refusee'");
$stmt->bind_param("i", $etudiantId);
$stmt->execute();
$stats['refusees'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM candidatures WHERE etudiant_id = ? AND statut = 'en_cours'");
$stmt->bind_param("i", $etudiantId);
$stmt->execute();
$stats['en_cours'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM candidatures WHERE etudiant_id = ? AND statut = 'terminee'");
$stmt->bind_param("i", $etudiantId);
$stmt->execute();
$stats['terminees'] = $stmt->get_result()->fetch_assoc()['total'];

// Dernières candidatures
$stmt = $conn->prepare("
    SELECT c.*, m.titre, m.localisation, m.remuneration, m.type_mission, cat.nom as categorie_nom, cat.icone
    FROM candidatures c
    JOIN missions m ON c.mission_id = m.id
    JOIN categories cat ON m.categorie_id = cat.id
    WHERE c.etudiant_id = ?
    ORDER BY c.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $etudiantId);
$stmt->execute();
$dernieresCandidatures = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Dernières missions recommandées
$stmt = $conn->prepare("
    SELECT m.*, c.nom as categorie_nom, c.icone
    FROM missions m
    JOIN categories c ON m.categorie_id = c.id
    WHERE m.statut = 'active'
    ORDER BY m.created_at DESC
    LIMIT 6
");
$stmt->execute();
$missionsRecentes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Profil étudiant
$profilEtudiant = getProfilEtudiant($etudiantId);

$pageActive = 'dashboard';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Bonjour, <?= htmlspecialchars($_SESSION['user_prenom']) ?> !</h1>
                <p class="mb-0 text-white-50">Bienvenue sur votre espace étudiant</p>
            </div>
            <a href="/missions.php" class="btn btn-light">
                <i class="fas fa-search me-2"></i>Trouver une mission
            </a>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-md-4 col-6">
            <div class="stat-card primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['total_candidatures'] ?></div>
                        <div class="stat-label">Candidatures</div>
                    </div>
                    <i class="fas fa-paper-plane stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['en_attente'] ?></div>
                        <div class="stat-label">En attente</div>
                    </div>
                    <i class="fas fa-clock stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['acceptees'] ?></div>
                        <div class="stat-label">Acceptées</div>
                    </div>
                    <i class="fas fa-check-circle stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['en_cours'] ?></div>
                        <div class="stat-label">En cours</div>
                    </div>
                    <i class="fas fa-play-circle stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['terminees'] ?></div>
                        <div class="stat-label">Terminées</div>
                    </div>
                    <i class="fas fa-flag-checkered stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #991B1B, #DC2626);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['refusees'] ?></div>
                        <div class="stat-label">Refusées</div>
                    </div>
                    <i class="fas fa-times-circle stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Dernières candidatures -->
        <div class="col-lg-8 mb-4">
            <div class="card-dashboard">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Mes dernières candidatures</h5>
                        <a href="/pages/etudiant/mes-candidatures.php" class="btn btn-outline-custom btn-sm">Voir tout</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($dernieresCandidatures)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Aucune candidature pour le moment</p>
                            <a href="/missions.php" class="btn btn-primary-custom mt-3">Voir les missions</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mission</th>
                                        <th>Catégorie</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dernieresCandidatures as $cand): ?>
                                        <tr>
                                            <td>
                                                <a href="/mission-detail.php?id=<?= $cand['mission_id'] ?>" class="fw-semibold text-decoration-none">
                                                    <?= htmlspecialchars(substr($cand['titre'], 0, 30)) ?>...
                                                </a>
                                                <br><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($cand['localisation']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge-categorie">
                                                    <i class="fas <?= htmlspecialchars($cand['icone']) ?> me-1"></i>
                                                    <?= htmlspecialchars($cand['categorie_nom']) ?>
                                                </span>
                                            </td>
                                            <td><small><?= date('d/m/Y', strtotime($cand['created_at'])) ?></small></td>
                                            <td>
                                                <span class="badge badge-<?= badgeClassStatutCandidature($cand['statut']) ?>">
                                                    <?= libelleStatutCandidature($cand['statut']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profil & Actions rapides -->
        <div class="col-lg-4">
            <!-- Complétion du profil -->
            <div class="card-dashboard p-4 mb-4">
                <h5 class="mb-3"><i class="fas fa-user-cog me-2"></i>Mon profil</h5>
                <?php
                $completion = 0;
                if (!empty($profilEtudiant['universite'])) $completion += 20;
                if (!empty($profilEtudiant['niveau_etude'])) $completion += 20;
                if (!empty($profilEtudiant['filiere'])) $completion += 20;
                if (!empty($profilEtudiant['competences'])) $completion += 20;
                if (!empty($profilEtudiant['bio'])) $completion += 20;
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Complétion du profil</small>
                        <small><?= $completion ?>%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: <?= $completion ?>%; background: var(--gradient-primary);"></div>
                    </div>
                </div>
                <?php if ($completion < 100): ?>
                    <p class="text-muted small mb-3">Complétez votre profil pour augmenter vos chances d'être retenu !</p>
                <?php endif; ?>
                <a href="/pages/etudiant/profil.php" class="btn btn-primary-custom w-100">
                    <i class="fas fa-edit me-2"></i>Modifier mon profil
                </a>
            </div>

            <!-- Actions rapides -->
            <div class="card-dashboard p-4">
                <h5 class="mb-3"><i class="fas fa-bolt me-2"></i>Actions rapides</h5>
                <div class="d-grid gap-2">
                    <a href="/missions.php" class="btn btn-outline-custom">
                        <i class="fas fa-search me-2"></i>Rechercher des missions
                    </a>
                    <a href="/categories.php" class="btn btn-outline-custom">
                        <i class="fas fa-th-large me-2"></i>Parcourir les catégories
                    </a>
                    <a href="/pages/etudiant/mes-candidatures.php" class="btn btn-outline-custom">
                        <i class="fas fa-list me-2"></i>Mes candidatures
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Missions récentes -->
    <div class="mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-fire me-2" style="color: var(--color-accent-gold);"></i>Missions récentes</h4>
            <a href="/missions.php" class="btn btn-outline-custom">Voir toutes les missions</a>
        </div>
        <div class="row g-4">
            <?php foreach ($missionsRecentes as $m): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card-mission">
                        <div class="card-body">
                            <span class="badge-categorie mb-2">
                                <i class="fas <?= htmlspecialchars($m['icone']) ?> me-1"></i>
                                <?= htmlspecialchars($m['categorie_nom']) ?>
                            </span>
                            <h5 class="card-title"><?= htmlspecialchars(substr($m['titre'], 0, 40)) ?>...</h5>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="localisation">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($m['localisation']) ?>
                                </span>
                                <span class="remuneration">
                                    <?= $m['remuneration'] ? number_format($m['remuneration'], 0, ',', ' ') . ' FCFA' : 'À négocier' ?>
                                </span>
                            </div>
                            <a href="/mission-detail.php?id=<?= $m['id'] ?>" class="btn btn-primary-custom btn-sm w-100">
                                Voir et postuler <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
