<?php
/**
 * Gestion des utilisateurs
 * SunuJob Étudiant
 */

$pageTitle = 'Gestion des utilisateurs - Admin';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('admin');
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    exigerCsrfPost('/pages/admin/users.php');

    $action = $_POST['action'];
    $userId = (int)$_POST['user_id'];

    if ($userId === $_SESSION['user_id']) {
        $errors[] = "Impossible de modifier ou supprimer votre propre compte depuis ce panneau.";
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
                    $messages[] = "Statut utilisateur mis à jour.";
                } else {
                    $errors[] = "Impossible de mettre à jour le statut.";
                }
            } else {
                $errors[] = "Utilisateur introuvable.";
            }
        }

        if ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $stmt->bind_param("i", $userId);
            if ($stmt->execute()) {
                $messages[] = "Utilisateur supprimé avec succès.";
            } else {
                $errors[] = "Impossible de supprimer l'utilisateur.";
            }
        }
    }
}

$users = [];
$result = $conn->query("SELECT id, nom, prenom, email, role, statut, created_at FROM utilisateurs ORDER BY created_at DESC");
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}

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
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
