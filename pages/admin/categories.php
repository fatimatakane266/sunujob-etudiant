<?php
/**
 * Gestion des catégories
 * SunuJob Étudiant
 */

$pageTitle = 'Gestion des catégories - Admin';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('admin');
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$messages = [];
$errors = [];
$edition = false;
$categorie = ['id' => 0, 'nom' => '', 'icone' => 'fa-ellipsis-h'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    exigerCsrfPost('/pages/admin/categories.php');

    $action = $_POST['action'];
    $nom = trim($_POST['nom'] ?? '');
    $icone = trim($_POST['icone'] ?? 'fa-ellipsis-h');
    $categorieId = (int)($_POST['categorie_id'] ?? 0);

    if ($action === 'add' || $action === 'update') {
        if ($nom === '') {
            $errors[] = "Le nom de la catégorie est requis.";
        }

        if (empty($errors)) {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO categories (nom, icone) VALUES (?, ?)");
                $stmt->bind_param("ss", $nom, $icone);
                if ($stmt->execute()) {
                    $messages[] = "Catégorie ajoutée avec succès.";
                } else {
                    $errors[] = "Impossible d'ajouter la catégorie. Vérifiez qu'elle n'existe pas déjà.";
                }
            } else {
                $stmt = $conn->prepare("UPDATE categories SET nom = ?, icone = ? WHERE id = ?");
                $stmt->bind_param("ssi", $nom, $icone, $categorieId);
                if ($stmt->execute()) {
                    $messages[] = "Catégorie mise à jour avec succès.";
                } else {
                    $errors[] = "Impossible de modifier la catégorie.";
                }
            }
        }
    }

    if ($action === 'delete' && isset($_POST['categorie_id'])) {
        $categorieId = (int)$_POST['categorie_id'];
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $categorieId);
        if ($stmt->execute()) {
            if ($conn->affected_rows > 0) {
                $messages[] = "Catégorie supprimée avec succès.";
            } else {
                $errors[] = "Catégorie introuvable ou impossible à supprimer.";
            }
        } else {
            $errors[] = "Impossible de supprimer la catégorie. Vérifiez qu'aucune mission n'en dépend.";
        }
    }
}

if (isset($_GET['edit'])) {
    $categorieId = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT id, nom, icone FROM categories WHERE id = ?");
    $stmt->bind_param("i", $categorieId);
    $stmt->execute();
    $categorie = $stmt->get_result()->fetch_assoc();
    if ($categorie) {
        $edition = true;
    }
}

$categories = [];
$result = $conn->query("SELECT id, nom, icone FROM categories ORDER BY nom");
if ($result) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}

$pageActive = 'admin';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Gestion des catégories</h1>
                <p class="mb-0 text-white-50">Ajouter, modifier ou supprimer les catégories de mission.</p>
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

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card-dashboard p-4">
                <h5><?= $edition ? 'Modifier la catégorie' : 'Ajouter une catégorie' ?></h5>
                <form method="post">
                    <?= champCsrf() ?>
                    <input type="hidden" name="action" value="<?= $edition ? 'update' : 'add' ?>">
                    <input type="hidden" name="categorie_id" value="<?= htmlspecialchars($categorie['id']) ?>">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($categorie['nom']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="icone" class="form-label">Icône FontAwesome</label>
                        <input type="text" class="form-control" id="icone" name="icone" value="<?= htmlspecialchars($categorie['icone']) ?>" placeholder="fa-book">
                    </div>
                    <button type="submit" class="btn btn-cta w-100">
                        <?= $edition ? 'Mettre à jour' : 'Ajouter' ?>
                    </button>
                    <?php if ($edition): ?>
                        <a href="/pages/admin/categories.php" class="btn btn-outline-secondary w-100 mt-2">Annuler</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card-dashboard p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Icône</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cat['nom']) ?></td>
                                    <td><i class="fas <?= htmlspecialchars($cat['icone']) ?>"></i> <?= htmlspecialchars($cat['icone']) ?></td>
                                    <td class="text-end">
                                        <a href="/pages/admin/categories.php?edit=<?= htmlspecialchars($cat['id']) ?>" class="btn btn-sm admin-action-btn btn-outline-custom" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="post" class="d-inline ms-1" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                            <?= champCsrf() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="categorie_id" value="<?= htmlspecialchars($cat['id']) ?>">
                                            <button type="submit" class="btn btn-sm admin-action-btn btn-danger" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
