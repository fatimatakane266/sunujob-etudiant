<?php
/**
 * Gestion des missions
 * SunuJob Étudiant
 */

$pageTitle = 'Gestion des missions - Admin';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('admin');
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['mission_id'])) {
    $action = $_POST['action'];
    $missionId = (int)$_POST['mission_id'];

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM missions WHERE id = ?");
        $stmt->bind_param("i", $missionId);
        if ($stmt->execute()) {
            $messages[] = "Mission supprimée avec succès.";
        } else {
            $errors[] = "Impossible de supprimer la mission.";
        }
    }
}

$missions = [];
$query = "SELECT m.id, m.titre, m.localisation, m.statut, m.created_at, u.nom AS recruteur_nom, u.prenom AS recruteur_prenom, c.nom AS categorie_nom, COUNT(ca.id) AS candidatures_count
          FROM missions m
          JOIN utilisateurs u ON u.id = m.recruteur_id
          JOIN categories c ON c.id = m.categorie_id
          LEFT JOIN candidatures ca ON ca.mission_id = m.id
          GROUP BY m.id
          ORDER BY m.created_at DESC";
$result = $conn->query($query);
if ($result) {
    $missions = $result->fetch_all(MYSQLI_ASSOC);
}

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
    <?php foreach ($messages as $message): ?>
        <div class="alert alert-success-custom mb-3" role="alert"><?= htmlspecialchars($message) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger-custom mb-3" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

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
