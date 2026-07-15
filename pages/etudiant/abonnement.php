<?php
/**
 * Abonnement étudiant — candidatures illimitées
 * SunuJob Étudiant
 */

$pageTitle = 'Mon abonnement - SunuJob Étudiant';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('etudiant');

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$etudiantId = $_SESSION['user_id'];
$candidaturesUtilisees = compterCandidaturesTotal($etudiantId);
$candidaturesRestantes = max(0, CANDIDATURES_GRATUITES_MAX - $candidaturesUtilisees);
$abonnementActif = getAbonnementActif($etudiantId);
$plans = getPlansAbonnement();

$pageActive = 'dashboard';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-crown me-2"></i>Mon abonnement</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="/pages/etudiant/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Abonnement</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <!-- Statut actuel -->
    <div class="card-dashboard p-4 mb-4">
        <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Statut actuel</h5>
        <div class="row g-4 align-items-center">
            <div class="col-md-4">
                <small class="text-muted d-block mb-1">Candidatures utilisées</small>
                <div class="h4 mb-0"><?= $candidaturesUtilisees ?> / <?= CANDIDATURES_GRATUITES_MAX ?></div>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block mb-1">Candidatures gratuites restantes</small>
                <div class="h4 mb-0"><?= $candidaturesRestantes ?></div>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block mb-1">Abonnement</small>
                <?php if ($abonnementActif): ?>
                    <span class="badge badge-<?= badgeClassStatutAbonnement($abonnementActif['statut']) ?>">
                        <?= htmlspecialchars(getPlanAbonnement($abonnementActif['type_abonnement'])['label'] ?? ucfirst($abonnementActif['type_abonnement'])) ?>
                    </span>
                    <div class="small text-muted mt-1">Expire le <?= date('d/m/Y', strtotime($abonnementActif['date_fin'])) ?></div>
                <?php else: ?>
                    <span class="badge badge-fermee">Aucun abonnement actif</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!$abonnementActif && $candidaturesRestantes === 0): ?>
        <div class="alert alert-danger-custom mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>
            Vous avez utilisé vos 2 candidatures gratuites. Pour continuer à postuler, veuillez souscrire à un abonnement.
        </div>
    <?php endif; ?>

    <!-- Offres -->
    <h4 class="mb-4">Choisir une offre</h4>
    <div class="row g-4">
        <?php foreach ($plans as $planKey => $plan): ?>
            <div class="col-md-4">
                <div class="card-dashboard h-100 p-4 text-center d-flex flex-column">
                    <h5 class="mb-2"><?= htmlspecialchars($plan['label']) ?></h5>
                    <div class="remuneration h3 mb-3"><?= number_format($plan['prix'], 0, ',', ' ') ?> FCFA</div>
                    <p class="text-muted small mb-4 flex-grow-1">
                        Candidatures illimitées pendant <?= $plan['duree_jours'] ?> jours.
                    </p>
                    <form method="POST" action="/pages/etudiant/paiement-wave.php">
                        <?= champCsrf() ?>
                        <input type="hidden" name="plan" value="<?= htmlspecialchars($planKey) ?>">
                        <button type="submit" class="btn btn-cta-etudiant w-100">
                            <i class="fas fa-mobile-alt me-2"></i>S'abonner avec Wave
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
