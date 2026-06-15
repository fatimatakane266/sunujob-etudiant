<?php
/**
 * Modifier une mission (Recruteur)
 * SunuJob Étudiant
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('recruteur');

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$missionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$recruteurId = $_SESSION['user_id'];

// Vérifier que la mission appartient au recruteur
$stmt = $conn->prepare("SELECT * FROM missions WHERE id = ? AND recruteur_id = ?");
$stmt->bind_param("ii", $missionId, $recruteurId);
$stmt->execute();
$mission = $stmt->get_result()->fetch_assoc();

if (!$mission) {
    header('Location: /pages/recruteur/mes-missions.php');
    exit;
}

$pageTitle = 'Modifier la mission - SunuJob Étudiant';

// Récupérer les catégories
$categories = $conn->query("SELECT * FROM categories ORDER BY nom")->fetch_all(MYSQLI_ASSOC);

$erreurs = [];
$donnees = [
    'titre' => $mission['titre'],
    'description' => $mission['description'],
    'categorie_id' => $mission['categorie_id'],
    'localisation' => $mission['localisation'],
    'type_mission' => $mission['type_mission'],
    'remuneration' => $mission['remuneration'],
    'date_debut' => $mission['date_debut'],
    'date_fin' => $mission['date_fin'],
    'places_disponibles' => $mission['places_disponibles']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donnees = [
        'titre' => trim($_POST['titre'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'categorie_id' => $_POST['categorie_id'] ?? '',
        'localisation' => trim($_POST['localisation'] ?? ''),
        'type_mission' => $_POST['type_mission'] ?? '',
        'remuneration' => $_POST['remuneration'] ?? '',
        'date_debut' => $_POST['date_debut'] ?? '',
        'date_fin' => $_POST['date_fin'] ?? '',
        'places_disponibles' => $_POST['places_disponibles'] ?? 1
    ];

    // Validation
    if (empty($donnees['titre'])) $erreurs[] = "Le titre est obligatoire.";
    if (empty($donnees['description'])) $erreurs[] = "La description est obligatoire.";
    if (empty($donnees['categorie_id'])) $erreurs[] = "La catégorie est obligatoire.";
    if (empty($donnees['localisation'])) $erreurs[] = "La localisation est obligatoire.";
    if (empty($donnees['type_mission'])) $erreurs[] = "Le type de mission est obligatoire.";

    if (!empty($donnees['date_debut']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $donnees['date_debut'])) {
        $erreurs[] = "Le format de la date de début est invalide.";
    }
    if (!empty($donnees['date_fin']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $donnees['date_fin'])) {
        $erreurs[] = "Le format de la date de fin est invalide.";
    }
    if (!empty($donnees['date_debut']) && !empty($donnees['date_fin']) && $donnees['date_fin'] < $donnees['date_debut']) {
        $erreurs[] = "La date de fin doit être postérieure ou égale à la date de début.";
    }

    if (empty($erreurs)) {
        $stmt = $conn->prepare("
            UPDATE missions SET
            categorie_id = ?, titre = ?, description = ?, localisation = ?,
            type_mission = ?, remuneration = ?, date_debut = ?, date_fin = ?, places_disponibles = ?
            WHERE id = ? AND recruteur_id = ?
        ");

        $remuneration = is_numeric($donnees['remuneration']) ? (float)$donnees['remuneration'] : null;
        $date_debut = !empty($donnees['date_debut']) ? $donnees['date_debut'] : null;
        $date_fin = !empty($donnees['date_fin']) ? $donnees['date_fin'] : null;

        $stmt->bind_param("issssdssiii",
            $donnees['categorie_id'],
            $donnees['titre'],
            $donnees['description'],
            $donnees['localisation'],
            $donnees['type_mission'],
            $remuneration,
            $date_debut,
            $date_fin,
            $donnees['places_disponibles'],
            $missionId,
            $recruteurId
        );

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Mission mise à jour avec succès !";
            $_SESSION['flash_type'] = 'success';
            header('Location: /pages/recruteur/mes-missions.php');
            exit;
        } else {
            $erreurs[] = "Erreur lors de la mise à jour de la mission.";
        }
    }
}

$pageActive = 'mes-missions';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-edit me-2"></i>Modifier la mission</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="/pages/recruteur/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/pages/recruteur/mes-missions.php">Mes missions</a></li>
                <li class="breadcrumb-item active">Modifier</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mission-detail-card">
                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger-custom mb-4" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?= htmlspecialchars($erreur) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-lg-8 mb-3">
                            <label for="titre" class="form-label">Titre de la mission *</label>
                            <input type="text" class="form-control" id="titre" name="titre" value="<?= htmlspecialchars($donnees['titre']) ?>" required>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="categorie_id" class="form-label">Catégorie *</label>
                            <select class="form-select" id="categorie_id" name="categorie_id" required>
                                <option value="">Sélectionner</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $donnees['categorie_id'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($donnees['description']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="localisation" class="form-label">Localisation *</label>
                            <input type="text" class="form-control" id="localisation" name="localisation" value="<?= htmlspecialchars($donnees['localisation']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type_mission" class="form-label">Type de mission *</label>
                            <select class="form-select" id="type_mission" name="type_mission" required>
                                <option value="">Sélectionner</option>
                                <option value="ponctuelle" <?= $donnees['type_mission'] === 'ponctuelle' ? 'selected' : '' ?>>Ponctuelle</option>
                                <option value="temps_partiel" <?= $donnees['type_mission'] === 'temps_partiel' ? 'selected' : '' ?>>Temps partiel</option>
                                <option value="stage" <?= $donnees['type_mission'] === 'stage' ? 'selected' : '' ?>>Stage</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="remuneration" class="form-label">Rémunération (FCFA)</label>
                            <input type="number" class="form-control" id="remuneration" name="remuneration" value="<?= htmlspecialchars($donnees['remuneration']) ?>" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_debut" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?= htmlspecialchars($donnees['date_debut']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_fin" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" value="<?= htmlspecialchars($donnees['date_fin']) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="places_disponibles" class="form-label">Places disponibles</label>
                            <input type="number" class="form-control" id="places_disponibles" name="places_disponibles" value="<?= htmlspecialchars($donnees['places_disponibles']) ?>" min="1">
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                        <a href="/pages/recruteur/mes-missions.php" class="btn btn-outline-custom btn-lg">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
