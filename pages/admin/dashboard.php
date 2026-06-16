<?php
/**
 * Dashboard Admin
 * SunuJob Étudiant
 */

$pageTitle = 'Administration - SunuJob Étudiant';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('admin');
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$stats = [
    'total_users' => 0,
    'total_etudiants' => 0,
    'total_recruteurs' => 0,
    'total_missions' => 0,
    'missions_actives' => 0,
    'total_candidatures' => 0,
    'total_categories' => 0,
];

$result = $conn->query("SELECT COUNT(*) as total FROM utilisateurs");
$stats['total_users'] = $result ? (int)$result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'etudiant'");
$stats['total_etudiants'] = $result ? (int)$result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'recruteur'");
$stats['total_recruteurs'] = $result ? (int)$result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM missions");
$stats['total_missions'] = $result ? (int)$result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM missions WHERE statut = 'active'");
$stats['missions_actives'] = $result ? (int)$result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM candidatures");
$stats['total_candidatures'] = $result ? (int)$result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM categories");
$stats['total_categories'] = $result ? (int)$result->fetch_assoc()['total'] : 0;

$dernieresUsers = [];
$result = $conn->query("SELECT id, nom, prenom, email, role, statut, created_at FROM utilisateurs ORDER BY created_at DESC LIMIT 6");
if ($result) {
    $dernieresUsers = $result->fetch_all(MYSQLI_ASSOC);
}

$dernieresMissions = [];
$result = $conn->query("SELECT m.id, m.titre, m.localisation, m.statut, u.nom as recruteur_nom, u.prenom as recruteur_prenom, c.nom as categorie_nom FROM missions m JOIN utilisateurs u ON u.id = m.recruteur_id JOIN categories c ON c.id = m.categorie_id ORDER BY m.created_at DESC LIMIT 6");
if ($result) {
    $dernieresMissions = $result->fetch_all(MYSQLI_ASSOC);
}

$pageActive = 'admin';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1>Tableau de bord admin</h1>
                <p class="mb-0 text-white-50">Statistiques globales et supervision de la plateforme.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="/pages/admin/users.php" class="btn btn-outline-header"><i class="fas fa-users me-2"></i>Utilisateurs</a>
                <a href="/pages/admin/missions.php" class="btn btn-outline-header"><i class="fas fa-briefcase me-2"></i>Missions</a>
                <a href="/pages/admin/categories.php" class="btn btn-outline-header"><i class="fas fa-tags me-2"></i>Catégories</a>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Raccourcis administration -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="/pages/admin/users.php" class="admin-quick-card">
                <div class="icon-box" style="background: var(--gradient-primary);">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h6>Gérer les utilisateurs</h6>
                    <p><?= $stats['total_users'] ?> comptes — activer, désactiver, supprimer</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="/pages/admin/missions.php" class="admin-quick-card">
                <div class="icon-box" style="background: var(--gradient-green);">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div>
                    <h6>Gérer les missions</h6>
                    <p><?= $stats['missions_actives'] ?> actives sur <?= $stats['total_missions'] ?> au total</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="/pages/admin/categories.php" class="admin-quick-card">
                <div class="icon-box" style="background: var(--gradient-orange);">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <h6>Gérer les catégories</h6>
                    <p><?= $stats['total_categories'] ?> catégories de missions</p>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['total_users'] ?></div>
                        <div class="stat-label">Utilisateurs</div>
                    </div>
                    <i class="fas fa-users stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['total_missions'] ?></div>
                        <div class="stat-label">Missions</div>
                    </div>
                    <i class="fas fa-briefcase stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['total_candidatures'] ?></div>
                        <div class="stat-label">Candidatures</div>
                    </div>
                    <i class="fas fa-file-alt stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['total_categories'] ?></div>
                        <div class="stat-label">Catégories</div>
                    </div>
                    <i class="fas fa-tags stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card-dashboard">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" style="color: var(--color-primary);"><i class="fas fa-users me-2"></i>Derniers inscrits</h5>
                    <a href="/pages/admin/users.php" class="btn btn-sm btn-primary-custom">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dernieresUsers as $user): ?>
                                    <tr>
                                        <td class="fw-semibold" style="color: var(--color-text-dark);"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
                                        <td style="color: var(--color-text-muted); font-size: 0.85rem;"><?= htmlspecialchars($user['email']) ?></td>
                                        <td><span class="badge-categorie"><?= ucfirst($user['role']) ?></span></td>
                                        <td>
                                            <span class="badge badge-<?= $user['statut'] === 'actif' ? 'active' : 'fermee' ?>">
                                                <?= ucfirst($user['statut']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-dashboard">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" style="color: var(--color-primary);"><i class="fas fa-briefcase me-2"></i>Dernières missions</h5>
                    <a href="/pages/admin/missions.php" class="btn btn-sm btn-primary-custom">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Mission</th>
                                    <th>Recruteur</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dernieresMissions as $mission): ?>
                                    <tr>
                                        <td>
                                            <a href="/mission-detail.php?id=<?= $mission['id'] ?>" class="fw-semibold text-decoration-none" style="color: var(--color-primary);">
                                                <?= htmlspecialchars(substr($mission['titre'], 0, 35)) ?>...
                                            </a>
                                            <br><small class="text-muted"><?= htmlspecialchars($mission['categorie_nom']) ?></small>
                                        </td>
                                        <td style="color: var(--color-text-muted);"><?= htmlspecialchars($mission['recruteur_prenom'] . ' ' . $mission['recruteur_nom']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $mission['statut'] === 'active' ? 'active' : ($mission['statut'] === 'fermee' ? 'fermee' : 'expiree') ?>">
                                                <?= ucfirst($mission['statut']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
