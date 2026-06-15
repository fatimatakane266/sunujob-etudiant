<?php
/**
 * Dashboard Recruteur
 * SunuJob Étudiant
 */

$pageTitle = 'Mon espace recruteur - SunuJob Étudiant';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('recruteur');

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$recruteurId = $_SESSION['user_id'];

// Statistiques
$stats = [
    'total_missions' => 0,
    'missions_actives' => 0,
    'total_candidatures' => 0,
    'candidatures_attente' => 0
];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM missions WHERE recruteur_id = ?");
$stmt->bind_param("i", $recruteurId);
$stmt->execute();
$stats['total_missions'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM missions WHERE recruteur_id = ? AND statut = 'active'");
$stmt->bind_param("i", $recruteurId);
$stmt->execute();
$stats['missions_actives'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("
    SELECT COUNT(c.id) as total FROM candidatures c
    JOIN missions m ON c.mission_id = m.id
    WHERE m.recruteur_id = ?
");
$stmt->bind_param("i", $recruteurId);
$stmt->execute();
$stats['total_candidatures'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("
    SELECT COUNT(c.id) as total FROM candidatures c
    JOIN missions m ON c.mission_id = m.id
    WHERE m.recruteur_id = ? AND c.statut = 'en_attente'
");
$stmt->bind_param("i", $recruteurId);
$stmt->execute();
$stats['candidatures_attente'] = $stmt->get_result()->fetch_assoc()['total'];

// Dernières missions
$stmt = $conn->prepare("
    SELECT m.*, c.nom as categorie_nom, c.icone,
           (SELECT COUNT(*) FROM candidatures WHERE mission_id = m.id) as nb_candidatures
    FROM missions m
    JOIN categories c ON m.categorie_id = c.id
    WHERE m.recruteur_id = ?
    ORDER BY m.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $recruteurId);
$stmt->execute();
$dernieresMissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Dernières candidatures
$stmt = $conn->prepare("
    SELECT c.*, m.titre as mission_titre,
           u.nom as etudiant_nom, u.prenom as etudiant_prenom, u.email as etudiant_email,
           pe.universite, pe.niveau_etude, pe.filiere
    FROM candidatures c
    JOIN missions m ON c.mission_id = m.id
    JOIN utilisateurs u ON c.etudiant_id = u.id
    LEFT JOIN profils_etudiants pe ON pe.utilisateur_id = u.id
    WHERE m.recruteur_id = ?
    ORDER BY c.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $recruteurId);
$stmt->execute();
$dernieresCandidatures = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Profil recruteur
$profilRecruteur = getProfilRecruteur($recruteurId);

$pageActive = 'dashboard';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Bonjour, <?= htmlspecialchars($_SESSION['user_prenom']) ?> !</h1>
                <p class="mb-0 text-white-50">Bienvenue sur votre espace recruteur</p>
            </div>
            <a href="/pages/recruteur/ajouter-mission.php" class="btn btn-cta">
                <i class="fas fa-plus me-2"></i>Publier une mission
            </a>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['total_missions'] ?></div>
                        <div class="stat-label">Missions publiées</div>
                    </div>
                    <i class="fas fa-briefcase stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['missions_actives'] ?></div>
                        <div class="stat-label">Missions actives</div>
                    </div>
                    <i class="fas fa-check-circle stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['total_candidatures'] ?></div>
                        <div class="stat-label">Candidatures reçues</div>
                    </div>
                    <i class="fas fa-users stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['candidatures_attente'] ?></div>
                        <div class="stat-label">En attente</div>
                    </div>
                    <i class="fas fa-hourglass-half stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Dernières missions -->
        <div class="col-lg-6 mb-4">
            <div class="card-dashboard">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Mes missions</h5>
                        <a href="/pages/recruteur/mes-missions.php" class="btn btn-outline-custom btn-sm">Voir tout</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($dernieresMissions)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Aucune mission publiée</p>
                            <a href="/pages/recruteur/ajouter-mission.php" class="btn btn-cta mt-3">Publier une mission</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mission</th>
                                        <th>Statut</th>
                                        <th>Candidatures</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dernieresMissions as $m): ?>
                                        <tr>
                                            <td>
                                                <a href="/mission-detail.php?id=<?= $m['id'] ?>" class="fw-semibold text-decoration-none">
                                                    <?= htmlspecialchars(substr($m['titre'], 0, 30)) ?>...
                                                </a>
                                                <br><small class="text-muted"><?= htmlspecialchars($m['categorie_nom']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $m['statut'] === 'active' ? 'active' : 'fermee' ?>">
                                                    <?= ucfirst($m['statut']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/pages/recruteur/candidatures.php?mission=<?= $m['id'] ?>">
                                                    <span class="badge bg-<?= $m['nb_candidatures'] > 0 ? 'primary' : 'secondary' ?>">
                                                        <?= $m['nb_candidatures'] ?>
                                                    </span>
                                                </a>
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

        <!-- Dernières candidatures -->
        <div class="col-lg-6 mb-4">
            <div class="card-dashboard">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Dernières candidatures</h5>
                        <a href="/pages/recruteur/candidatures.php" class="btn btn-outline-custom btn-sm">Voir tout</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($dernieresCandidatures)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Aucune candidature reçue</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($dernieresCandidatures as $c): ?>
                            <div class="d-flex align-items-center p-2 rounded mb-2" style="background: var(--color-bg);">
                                <div class="me-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background: var(--gradient-primary);">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?= htmlspecialchars($c['etudiant_prenom'] . ' ' . $c['etudiant_nom']) ?></div>
                                    <small class="text-muted">pour "<?= htmlspecialchars(substr($c['mission_titre'], 0, 25)) ?>..."</small>
                                </div>
                                <div>
                                    <span class="badge badge-<?= $c['statut'] === 'en_attente' ? 'attente' : ($c['statut'] === 'acceptee' ? 'acceptee' : 'refusee') ?>">
                                        <?= $c['statut'] === 'en_attente' ? 'En attente' : ($c['statut'] === 'acceptee' ? 'Acceptée' : 'Refusée') ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card-dashboard p-4">
                <h5 class="mb-3"><i class="fas fa-bolt me-2"></i>Actions rapides</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="/pages/recruteur/ajouter-mission.php" class="btn btn-cta w-100 py-3">
                            <i class="fas fa-plus d-block fs-4 mb-2"></i>
                            Nouvelle mission
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/pages/recruteur/candidatures.php" class="btn btn-outline-custom w-100 py-3">
                            <i class="fas fa-users d-block fs-4 mb-2"></i>
                            Gérer les candidatures
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/pages/recruteur/profil.php" class="btn btn-outline-custom w-100 py-3">
                            <i class="fas fa-building d-block fs-4 mb-2"></i>
                            Mon profil
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/pages/recruteur/mes-missions.php" class="btn btn-outline-custom w-100 py-3">
                            <i class="fas fa-list d-block fs-4 mb-2"></i>
                            Mes missions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
