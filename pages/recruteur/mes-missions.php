<?php
/**
 * Liste des missions du recruteur
 * SunuJob Étudiant
 */

$pageTitle = 'Mes missions - SunuJob Étudiant';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('recruteur');

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$recruteurId = $_SESSION['user_id'];

// Filtres (préservés lors des actions delete/close/reopen ci-dessous)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categorieId = isset($_GET['categorie']) && $_GET['categorie'] !== '' ? (int)$_GET['categorie'] : null;
$statutFiltre = isset($_GET['statut']) && in_array($_GET['statut'], ['active', 'fermee', 'expiree'], true) ? $_GET['statut'] : '';
$filtreQueryString = http_build_query(array_filter([
    'search' => $search,
    'categorie' => $categorieId,
    'statut' => $statutFiltre,
]));
$redirectUrl = '/pages/recruteur/mes-missions.php' . ($filtreQueryString ? '?' . $filtreQueryString : '');

// Suppression / fermeture / réouverture : actions sensibles déclenchées par lien,
// donc protégées par un jeton CSRF passé en paramètre plutôt qu'en formulaire POST.
if (isset($_GET['delete']) || isset($_GET['close']) || isset($_GET['reopen'])) {
    if (!verifierCsrfToken($_GET['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = "Lien invalide ou expiré. Veuillez réessayer.";
        $_SESSION['flash_type'] = 'danger';
        header('Location: ' . $redirectUrl);
        exit;
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $missionId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM missions WHERE id = ? AND recruteur_id = ?");
    $stmt->bind_param("ii", $missionId, $recruteurId);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['flash_message'] = "Mission supprimée avec succès.";
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: ' . $redirectUrl);
    exit;
}

// Fermeture
if (isset($_GET['close']) && is_numeric($_GET['close'])) {
    $missionId = (int)$_GET['close'];
    $stmt = $conn->prepare("UPDATE missions SET statut = 'fermee' WHERE id = ? AND recruteur_id = ?");
    $stmt->bind_param("ii", $missionId, $recruteurId);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['flash_message'] = "Mission fermée avec succès.";
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: ' . $redirectUrl);
    exit;
}

// Réouverture (uniquement si la date de fin n'est pas dépassée)
if (isset($_GET['reopen']) && is_numeric($_GET['reopen'])) {
    $missionId = (int)$_GET['reopen'];
    $stmt = $conn->prepare("UPDATE missions SET statut = 'active' WHERE id = ? AND recruteur_id = ? AND (date_fin IS NULL OR date_fin >= CURDATE())");
    $stmt->bind_param("ii", $missionId, $recruteurId);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['flash_message'] = "Mission réactivée avec succès.";
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: ' . $redirectUrl);
    exit;
}

$csrfToken = genererCsrfToken();
$categories = $conn->query("SELECT * FROM categories ORDER BY nom")->fetch_all(MYSQLI_ASSOC);

// Récupérer les missions (avec filtres)
$where = ['m.recruteur_id = ?'];
$params = [$recruteurId];
$types = 'i';

if ($search !== '') {
    $where[] = 'm.titre LIKE ?';
    $params[] = "%$search%";
    $types .= 's';
}
if ($categorieId) {
    $where[] = 'm.categorie_id = ?';
    $params[] = $categorieId;
    $types .= 'i';
}
if ($statutFiltre !== '') {
    $where[] = 'm.statut = ?';
    $params[] = $statutFiltre;
    $types .= 's';
}

$stmt = $conn->prepare("
    SELECT m.*, c.nom as categorie_nom, c.icone,
           (SELECT COUNT(*) FROM candidatures WHERE mission_id = m.id) as nb_candidatures
    FROM missions m
    JOIN categories c ON m.categorie_id = c.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY m.created_at DESC
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$missions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageActive = 'mes-missions';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-list-check me-2"></i>Mes missions</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="/pages/recruteur/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Mes missions</li>
                    </ol>
                </nav>
            </div>
            <a href="/pages/recruteur/ajouter-mission.php" class="btn btn-cta">
                <i class="fas fa-plus me-2"></i>Nouvelle mission
            </a>
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
            <a href="/pages/recruteur/mes-missions.php" class="btn btn-outline-custom" title="Réinitialiser">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>

    <?php if (empty($missions)): ?>
        <div class="text-center py-5">
            <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
            <h4 class="text-muted"><?= ($search || $categorieId || $statutFiltre) ? 'Aucune mission ne correspond aux filtres' : 'Aucune mission publiée' ?></h4>
            <p class="text-muted mb-4">
                <?= ($search || $categorieId || $statutFiltre) ? 'Essayez de réinitialiser les filtres.' : 'Commencez par publier votre première mission.' ?>
            </p>
            <a href="/pages/recruteur/ajouter-mission.php" class="btn btn-cta">
                <i class="fas fa-plus me-2"></i>Publier une mission
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Localisation</th>
                        <th>Statut</th>
                        <th>Vues</th>
                        <th>Candidatures</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($missions as $mission): ?>
                        <tr>
                            <td>
                                <a href="/mission-detail.php?id=<?= $mission['id'] ?>" class="fw-semibold text-decoration-none">
                                    <?= htmlspecialchars(substr($mission['titre'], 0, 40)) ?>...
                                </a>
                            </td>
                            <td>
                                <span class="badge-categorie">
                                    <i class="fas <?= htmlspecialchars($mission['icone']) ?> me-1"></i>
                                    <?= htmlspecialchars($mission['categorie_nom']) ?>
                                </span>
                            </td>
                            <td class="localisation">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($mission['localisation']) ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $mission['statut'] === 'active' ? 'active' : ($mission['statut'] === 'fermee' ? 'fermee' : 'expiree') ?>">
                                    <?= ucfirst($mission['statut']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">
                                    <i class="fas fa-eye me-1"></i><?= number_format($mission['nb_vues'] ?? 0, 0, ',', ' ') ?>
                                </span>
                            </td>
                            <td>
                                <a href="/pages/recruteur/candidatures.php?mission=<?= $mission['id'] ?>" class="text-decoration-none">
                                    <span class="badge bg-<?= $mission['nb_candidatures'] > 0 ? 'primary' : 'secondary' ?>">
                                        <?= $mission['nb_candidatures'] ?>
                                    </span>
                                </a>
                            </td>
                            <td>
                                <small><?= date('d/m/Y', strtotime($mission['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/mission-detail.php?id=<?= $mission['id'] ?>" class="btn btn-outline-primary" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/pages/recruteur/modifier-mission.php?id=<?= $mission['id'] ?>" class="btn btn-outline-secondary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($mission['statut'] === 'active'): ?>
                                        <a href="?close=<?= $mission['id'] ?>&csrf_token=<?= urlencode($csrfToken) ?>" class="btn btn-outline-warning" title="Fermer" onclick="return confirm('Fermer cette mission ?');">
                                            <i class="fas fa-lock"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?reopen=<?= $mission['id'] ?>&csrf_token=<?= urlencode($csrfToken) ?>" class="btn btn-outline-success" title="Réactiver">
                                            <i class="fas fa-lock-open"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="?delete=<?= $mission['id'] ?>&csrf_token=<?= urlencode($csrfToken) ?>" class="btn btn-outline-danger" title="Supprimer" onclick="return confirm('Supprimer cette mission ?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
