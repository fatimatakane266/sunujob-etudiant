<?php
/**
 * Page À propos
 * SunuJob Étudiant
 */

$pageTitle = 'À propos - SunuJob Étudiant';
$pageActive = 'a-propos';

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-info-circle me-2"></i>À propos de SunuJob Étudiant</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                <li class="breadcrumb-item active">À propos</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mission-detail-card mb-4">
                <h2 class="mb-4"><i class="fas fa-bullseye me-2" style="color: var(--color-accent-gold);"></i>Notre Mission</h2>
                <p class="lead">
                    <strong>SunuJob Étudiant</strong> est une plateforme numérique innovante conçue pour connecter les étudiants sénégalais aux opportunités de missions temporaires.
                </p>
                <p>
                    Au Sénégal, de nombreux étudiants rencontrent des difficultés pour trouver des emplois temporaires ou des activités génératrices de revenus compatibles avec leurs études. De leur côté, les entreprises, startups, et particuliers recherchent régulièrement des profils étudiants dynamiques pour des missions ponctuelles.
                </p>
                <p>
                    SunuJob Étudiant centralise ces opportunités et simplifie la mise en relation entre étudiants et recruteurs.
                </p>
            </div>

            <div class="mission-detail-card mb-4">
                <h2 class="mb-4"><i class="fas fa-users me-2" style="color: var(--color-accent-green);"></i>Pour qui ?</h2>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="p-4 rounded text-center h-100" style="background: var(--color-bg);">
                            <i class="fas fa-user-graduate fa-3x mb-3" style="color: var(--color-primary);"></i>
                            <h4>Étudiants</h4>
                            <ul class="list-unstyled text-muted">
                                <li>Étudiants universitaires</li>
                                <li>Étudiants en écoles de formation</li>
                                <li>Jeunes diplômés</li>
                                <li>Freelances débutants</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="p-4 rounded text-center h-100" style="background: var(--color-bg);">
                            <i class="fas fa-building fa-3x mb-3" style="color: var(--color-primary);"></i>
                            <h4>Recruteurs</h4>
                            <ul class="list-unstyled text-muted">
                                <li>Entreprises</li>
                                <li>Startups</li>
                                <li>Agences événementielles</li>
                                <li>Particuliers</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mission-detail-card mb-4">
                <h2 class="mb-4"><i class="fas fa-lightbulb me-2" style="color: var(--color-accent-gold);"></i>Nos valeurs</h2>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="step-item p-3">
                            <div class="step-number mx-auto">1</div>
                            <h5 class="text-center">Simplicité</h5>
                            <p class="text-muted text-center small">Une interface intuitive pour publier et postuler en quelques clics.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="step-item p-3">
                            <div class="step-number mx-auto">2</div>
                            <h5 class="text-center">Accessibilité</h5>
                            <p class="text-muted text-center small">Une plateforme accessible depuis mobile, tablette et ordinateur.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="step-item p-3">
                            <div class="step-number mx-auto">3</div>
                            <h5 class="text-center">Confiance</h5>
                            <p class="text-muted text-center small">Un environnement sécurisé pour étudiants et recruteurs.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mission-detail-card">
                <h2 class="mb-4"><i class="fas fa-envelope me-2" style="color: var(--color-primary-light);"></i>Nous contacter</h2>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p><i class="fas fa-envelope me-2" style="color: var(--color-accent-gold);"></i>contact@sunujob.sn</p>
                        <p><i class="fas fa-phone me-2" style="color: var(--color-accent-gold);"></i>+221 77 123 45 67</p>
                        <p><i class="fas fa-map-marker-alt me-2" style="color: var(--color-accent-gold);"></i>Dakar, Sénégal</p>
                    </div>
                    <div class="col-md-6">
                        <div class="social-links d-flex gap-3">
                            <a href="#" class="btn btn-outline-custom"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-outline-custom"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="btn btn-outline-custom"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="btn btn-outline-custom"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
