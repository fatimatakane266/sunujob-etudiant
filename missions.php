<?php
/**
 * Liste des missions
 * SunuJob Étudiant
 */

$pageTitle = 'Trouver une mission - SunuJob Étudiant';
$pageActive = 'missions';

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

// Filtres
$categorieId = isset($_GET['categorie']) ? (int)$_GET['categorie'] : null;
$localisation = isset($_GET['localisation']) ? securiser($_GET['localisation']) : '';
$typeMission = isset($_GET['type_mission']) ? securiser($_GET['type_mission']) : '';
$search = isset($_GET['search']) ? securiser($_GET['search']) : '';
$remunerationMin = isset($_GET['remuneration_min']) ? (float)$_GET['remuneration_min'] : null;
$remunerationMax = isset($_GET['remuneration_max']) ? (float)$_GET['remuneration_max'] : null;

// Construction de la requête
$where = ["m.statut = 'active'"];
$params = [];
$types = '';

if ($categorieId) {
    $where[] = "m.categorie_id = ?";
    $params[] = $categorieId;
    $types .= 'i';
}

if ($localisation) {
    $where[] = "m.localisation LIKE ?";
    $params[] = "%$localisation%";
    $types .= 's';
}

if ($typeMission) {
    $where[] = "m.type_mission = ?";
    $params[] = $typeMission;
    $types .= 's';
}

if ($search) {
    $where[] = "(m.titre LIKE ? OR m.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if ($remunerationMin !== null) {
    $where[] = "m.remuneration >= ?";
    $params[] = $remunerationMin;
    $types .= 'd';
}

if ($remunerationMax !== null) {
    $where[] = "(m.remuneration <= ? OR m.remuneration IS NULL)";
    $params[] = $remunerationMax;
    $types .= 'd';
}

$whereClause = implode(' AND ', $where);

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Compter le total
$countSql = "SELECT COUNT(*) as total FROM missions m WHERE $whereClause";
$stmt = $conn->prepare($countSql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalMissions = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalMissions / $perPage);

// Récupérer les missions
$sql = "
    SELECT m.*, c.nom as categorie_nom, c.icone,
           u.nom as recruteur_nom, u.prenom as recruteur_prenom,
           pr.nom_structure, pr.type_recruteur
    FROM missions m
    JOIN categories c ON m.categorie_id = c.id
    JOIN utilisateurs u ON m.recruteur_id = u.id
    LEFT JOIN profils_recruteurs pr ON pr.utilisateur_id = u.id
    WHERE $whereClause
    ORDER BY m.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$typesWithPagination = $types . 'ii';
$paramsWithPagination = array_merge($params, [$perPage, $offset]);
$stmt->bind_param($typesWithPagination, ...$paramsWithPagination);
$stmt->execute();
$result = $stmt->get_result();

$missions = [];
while ($row = $result->fetch_assoc()) {
    $missions[] = $row;
}

// Récupérer les catégories pour le filtre
$categories = $conn->query("SELECT * FROM categories ORDER BY nom")->fetch_all(MYSQLI_ASSOC);

// Récupérer les localisations uniques
$localisations = $conn->query("SELECT DISTINCT localisation FROM missions WHERE statut = 'active' ORDER BY localisation")->fetch_all(MYSQLI_ASSOC);

// Vérifier si l'utilisateur a déjà postulé (si connecté et étudiant)
$candidaturesEtudiant = [];
if (estConnecte() && aRole('etudiant')) {
    $stmt = $conn->prepare("SELECT mission_id FROM candidatures WHERE etudiant_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $candidaturesEtudiant[$row['mission_id']] = true;
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-briefcase me-2"></i>Trouver une mission</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                <li class="breadcrumb-item active">Missions</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <div class="row">
        <!-- Filtres -->
        <div class="col-lg-3 mb-4">
            <div class="filter-card sticky-top" style="top: 100px;">
                <h4 class="filter-title"><i class="fas fa-filter me-2"></i>Filtres</h4>

                <form method="GET" action="">
                    <!-- Recherche -->
                    <div class="mb-3">
                        <label class="form-label">Recherche</label>
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Mot-clé...">
                    </div>

                    <!-- Catégorie -->
                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <select class="form-select" name="categorie">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $categorieId == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Localisation -->
                    <div class="mb-3">
                        <label class="form-label">Localisation</label>
                        <input type="text" class="form-control" name="localisation" value="<?= htmlspecialchars($localisation) ?>" list="localisations" placeholder="Ville, quartier...">
                        <datalist id="localisations">
                            <?php foreach ($localisations as $loc): ?>
                                <option value="<?= htmlspecialchars($loc['localisation']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <!-- Type de mission -->
                    <div class="mb-3">
                        <label class="form-label">Type de mission</label>
                        <select class="form-select" name="type_mission">
                            <option value="">Tous les types</option>
                            <option value="ponctuelle" <?= $typeMission === 'ponctuelle' ? 'selected' : '' ?>>Ponctuelle</option>
                            <option value="temps_partiel" <?= $typeMission === 'temps_partiel' ? 'selected' : '' ?>>Temps partiel</option>
                            <option value="stage" <?= $typeMission === 'stage' ? 'selected' : '' ?>>Stage</option>
                        </select>
                    </div>

                    <!-- Rémunération -->
                    <div class="mb-3">
                        <label class="form-label">Rémunération (FCFA)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" class="form-control" name="remuneration_min" value="<?= $remunerationMin ?? '' ?>" placeholder="Min">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" name="remuneration_max" value="<?= $remunerationMax ?? '' ?>" placeholder="Max">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-search me-2"></i>Filtrer
                        </button>
                        <a href="/missions.php" class="btn btn-outline-custom">
                            <i class="fas fa-times me-2"></i>Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des missions -->
        <div class="col-lg-9">
            <!-- Résultats -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted mb-0">
                    <strong><?= $totalMissions ?></strong> mission<?= $totalMissions > 1 ? 's' : '' ?> trouvée<?= $totalMissions > 1 ? 's' : '' ?>
                </p>
                <?php if (estConnecte() && aRole('recruteur')): ?>
                    <a href="/pages/recruteur/ajouter-mission.php" class="btn btn-cta">
                        <i class="fas fa-plus me-2"></i>Publier une mission
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($missions)): ?>
                <div class="empty-state py-5">
                    <div class="empty-state-icon"><i class="fas fa-search"></i></div>
                    <h4>Aucune mission trouvée</h4>
                    <p>Essayez de modifier vos critères de recherche.</p>
                    <a href="/missions.php" class="btn btn-outline-custom">
                        <i class="fas fa-times me-2"></i>Réinitialiser les filtres
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($missions as $mission): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="card-mission">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge-categorie">
                                            <i class="fas <?= htmlspecialchars($mission['icone']) ?> me-1"></i>
                                            <?= htmlspecialchars($mission['categorie_nom']) ?>
                                        </span>
                                        <span class="badge badge-<?= $mission['statut'] === 'active' ? 'active' : 'fermee' ?>">
                                            <?= $mission['statut'] === 'active' ? 'Active' : 'Fermée' ?>
                                        </span>
                                    </div>
                                    <h5 class="card-title"><?= htmlspecialchars($mission['titre']) ?></h5>
                                    <p class="card-text"><?= substr(htmlspecialchars($mission['description']), 0, 100) ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="localisation">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($mission['localisation']) ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= $mission['type_mission'] === 'ponctuelle' ? 'Ponctuelle' : ($mission['type_mission'] === 'temps_partiel' ? 'Temps partiel' : 'Stage') ?>
                                        </small>
                                        <span class="remuneration">
                                            <?= $mission['remuneration'] ? number_format($mission['remuneration'], 0, ',', ' ') . ' FCFA' : 'À négocier' ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <?php if (estConnecte() && aRole('etudiant')): ?>
                                            <?php if (isset($candidaturesEtudiant[$mission['id']])): ?>
                                                <span class="badge badge-attente"><i class="fas fa-check me-1"></i>Déjà postulé</span>
                                            <?php else: ?>
                                                <a href="/mission-detail.php?id=<?= $mission['id'] ?>" class="btn btn-primary-custom btn-sm">Postuler</a>
                                            <?php endif; ?>
                                        <?php elseif (!estConnecte()): ?>
                                            <a href="/login.php" class="btn btn-outline-custom btn-sm">Connectez-vous</a>
                                        <?php endif; ?>
                                        <a href="/mission-detail.php?id=<?= $mission['id'] ?>" class="btn btn-link btn-sm">
                                            Détails <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
