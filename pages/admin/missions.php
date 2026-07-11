<?php
/**
 * Gestion des missions
 * SunuJob Étudiant
 */

$pageTitle = 'Gestion des missions - Admin';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('admin');
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['mission_id'])) {
    exigerCsrfPost('/pages/admin/missions.php');

    $action = $_POST['action'];
    $missionId = (int)$_POST['mission_id'];

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM missions WHERE id = ?");
        $stmt->bind_param("i", $missionId);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Mission supprimée avec succès.";
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Impossible de supprimer la mission.";
            $_SESSION['flash_type'] = 'danger';
        }
    }

    header('Location: /pages/admin/missions.php');
    exit;
}

// Filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categorieId = isset($_GET['categorie']) && $_GET['categorie'] !== '' ? (int)$_GET['categorie'] : null;
$statutFiltre = isset($_GET['statut']) && in_array($_GET['statut'], ['active', 'fermee', 'expiree'], true) ? $_GET['statut'] : '';

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "m.titre LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}
if ($categorieId) {
    $where[] = "m.categorie_id = ?";
    $params[] = $categorieId;
    $types .= 'i';
}
if ($statutFiltre !== '') {
    $where[] = "m.statut = ?";
    $params[] = $statutFiltre;
    $types .= 's';
}
$whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$categories = $conn->query("SELECT * FROM categories ORDER BY nom")->fetch_all(MYSQLI_ASSOC);

$missions = [];
$query = "SELECT m.id, m.titre, m.localisation, m.statut, m.created_at, u.nom AS recruteur_nom, u.prenom AS recruteur_prenom, c.nom AS categorie_nom, COUNT(ca.id) AS candidatures_count
          FROM missions m
          JOIN utilisateurs u ON u.id = m.recruteur_id
          JOIN categories c ON c.id = m.categorie_id
          LEFT JOIN candidatures ca ON ca.mission_id = m.id
          $whereClause
          GROUP BY m.id
          ORDER BY m.created_at DESC";
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$missions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageActive = 'admin';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Gestion des missions</h1>
                <p class="mb-0 text-white-50">Voir toutes les missions et supprimer les offres abusives.</p>
            </div>
            <a href="/pages/admin/dashboard.php" class="btn btn-outline-header"><i class="fas fa-arrow-left me-2"></i>Retour au dashboard</a>
        </div>
    </div>
</div>

<div class="container py-4">
    <form method="GET" action="" novalidate class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label for="search" class="form-label">Recherche</label>
            <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Titre de la mission...">
        </div>
        <div class="col-md-3">
            <label for="categorie" class="form-label">Catégorie</label>
            <select class="form-select" id="categorie" name="categorie">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categorieId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="statut" class="form-label">Statut</label>
            <select class="form-select" id="statut" name="statut">
                <option value="">Tous les statuts</option>
                <option value="active" <?= $statutFiltre === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="fermee" <?= $statutFiltre === 'fermee' ? 'selected' : '' ?>>Fermée</option>
                <option value="expiree" <?= $statutFiltre === 'expiree' ? 'selected' : '' ?>>Expirée</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-custom flex-fill">
                <i class="fas fa-filter me-1"></i>Filtrer
            </button>
            <a href="/pages/admin/missions.php" class="btn btn-outline-custom" title="Réinitialiser">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>

    <p class="text-muted mb-3"><strong><?= count($missions) ?></strong> mission(s) trouvée(s)</p>

    <div class="card-dashboard">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mission</th>
                            <th>Recruteur</th>
                            <th>Catégorie</th>
                            <th>Candidatures</th>
                            <th>Statut</th>
                            <th>Créée le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($missions as $mission): ?>
                            <tr>
                                <td><?= htmlspecialchars($mission['id']) ?></td>
                                <td><?= htmlspecialchars($mission['titre']) ?></td>
                                <td><?= htmlspecialchars($mission['recruteur_prenom'] . ' ' . $mission['recruteur_nom']) ?></td>
                                <td><?= htmlspecialchars($mission['categorie_nom']) ?></td>
                                <td><?= htmlspecialchars($mission['candidatures_count']) ?></td>
                                <td><span class="badge bg-<?= $mission['statut'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($mission['statut']) ?></span></td>
                                <td><?= date('d/m/Y', strtotime($mission['created_at'])) ?></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        <a href="/mission-detail.php?id=<?= htmlspecialchars($mission['id']) ?>" class="btn btn-sm admin-action-btn btn-outline-custom" title="Voir la mission">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Supprimer cette mission ?');">
                                            <?= champCsrf() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="mission_id" value="<?= htmlspecialchars($mission['id']) ?>">
                                            <button type="submit" class="btn btn-sm admin-action-btn btn-danger" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
