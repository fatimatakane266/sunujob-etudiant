<?php
/**
 * Gestion des utilisateurs
 * SunuJob Étudiant
 */

$pageTitle = 'Gestion des utilisateurs - Admin';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('admin');
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    exigerCsrfPost('/pages/admin/users.php');

    $action = $_POST['action'];
    $userId = (int)$_POST['user_id'];

    if ($userId === $_SESSION['user_id']) {
        $_SESSION['flash_message'] = "Impossible de modifier ou supprimer votre propre compte depuis ce panneau.";
        $_SESSION['flash_type'] = 'danger';
    } else {
        if ($action === 'toggle_status') {
            $stmt = $conn->prepare("SELECT statut FROM utilisateurs WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user) {
                $newStatus = $user['statut'] === 'actif' ? 'inactif' : 'actif';
                $stmt = $conn->prepare("UPDATE utilisateurs SET statut = ? WHERE id = ?");
                $stmt->bind_param("si", $newStatus, $userId);
                if ($stmt->execute()) {
                    $_SESSION['flash_message'] = "Statut utilisateur mis à jour.";
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = "Impossible de mettre à jour le statut.";
                    $_SESSION['flash_type'] = 'danger';
                }
            } else {
                $_SESSION['flash_message'] = "Utilisateur introuvable.";
                $_SESSION['flash_type'] = 'danger';
            }
        }

        if ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $stmt->bind_param("i", $userId);
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Utilisateur supprimé avec succès.";
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = "Impossible de supprimer l'utilisateur.";
                $_SESSION['flash_type'] = 'danger';
            }
        }
    }

    header('Location: /pages/admin/users.php');
    exit;
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$totalUsers = (int)$conn->query("SELECT COUNT(*) as total FROM utilisateurs")->fetch_assoc()['total'];
$totalPages = max(1, (int)ceil($totalUsers / $perPage));

$users = [];
$stmt = $conn->prepare("SELECT id, nom, prenom, email, role, statut, created_at FROM utilisateurs ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageActive = 'admin';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Gestion des utilisateurs</h1>
                <p class="mb-0 text-white-50">Voir, activer, désactiver et supprimer les comptes.</p>
            </div>
            <a href="/pages/admin/dashboard.php" class="btn btn-outline-header"><i class="fas fa-arrow-left me-2"></i>Retour au dashboard</a>
        </div>
    </div>
</div>

<div class="container py-4">
    <p class="text-muted mb-3"><strong><?= $totalUsers ?></strong> utilisateur(s) au total</p>

    <div class="card-dashboard">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Inscrit le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= ucfirst($user['role']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['statut'] === 'actif' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($user['statut']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                <td class="text-end">
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <form method="post" class="d-inline">
                                            <?= champCsrf() ?>
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                            <button type="submit" class="btn btn-sm admin-action-btn btn-outline-custom" title="<?= $user['statut'] === 'actif' ? 'Désactiver' : 'Activer' ?>">
                                                <i class="fas <?= $user['statut'] === 'actif' ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="post" class="d-inline ms-1" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                            <?= champCsrf() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                            <button type="submit" class="btn btn-sm admin-action-btn btn-danger" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"><i class="fas fa-chevron-left"></i></a>
                    </li>
                <?php endif; ?>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"><i class="fas fa-chevron-right"></i></a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
