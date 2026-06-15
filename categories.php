<?php
/**
 * Liste des catégories
 * SunuJob Étudiant
 */

$pageTitle = 'Catégories de missions - SunuJob Étudiant';
$pageActive = 'categories';

require_once 'includes/db.php';
require_once 'includes/header.php';

// Récupérer les catégories avec le nombre de missions
$categories = [];
$result = $conn->query("
    SELECT c.*, COUNT(m.id) as nb_missions
    FROM categories c
    LEFT JOIN missions m ON c.id = m.categorie_id AND m.statut = 'active'
    GROUP BY c.id
    ORDER BY c.nom
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-th-large me-2"></i>Catégories de missions</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                <li class="breadcrumb-item active">Catégories</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <?php foreach ($categories as $cat): ?>
            <div class="col-md-6 col-lg-4">
                <a href="/missions.php?categorie=<?= $cat['id'] ?>" class="text-decoration-none">
                    <div class="card-dashboard h-100 p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="card-icon primary me-3">
                                <i class="fas <?= htmlspecialchars($cat['icone']) ?>"></i>
                            </div>
                            <div>
                                <h4 class="mb-0"><?= htmlspecialchars($cat['nom']) ?></h4>
                            </div>
                        </div>
                        <p class="text-muted mb-0">
                            <?= $cat['nb_missions'] ?> mission<?= $cat['nb_missions'] > 1 ? 's' : '' ?> disponible<?= $cat['nb_missions'] > 1 ? 's' : '' ?>
                        </p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
