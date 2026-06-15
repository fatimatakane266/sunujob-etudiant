<?php
/**
 * Page 404 — SunuJob Étudiant
 */

http_response_code(404);

$pageTitle  = 'Page introuvable — SunuJob Étudiant';
$pageActive = '';

require_once 'includes/header.php';
?>

<div style="min-height:70vh;display:flex;align-items:center;">
    <div class="container text-center py-5">
        <!-- Grand 404 -->
        <div style="font-size:8rem;font-weight:700;line-height:1;background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            404
        </div>

        <div style="width:80px;height:4px;background:var(--gradient-accent);border-radius:2px;margin:1rem auto 2rem;"></div>

        <h2 style="color:var(--color-primary);margin-bottom:1rem;">Page introuvable</h2>
        <p class="text-muted mb-4" style="font-size:1.05rem;max-width:500px;margin:0 auto 2rem;">
            La page que vous cherchez n'existe pas ou a été déplacée.
        </p>

        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="/index.php" class="btn btn-primary-custom btn-lg">
                <i class="fas fa-home me-2"></i>Retour à l'accueil
            </a>
            <a href="/missions.php" class="btn btn-outline-custom btn-lg">
                <i class="fas fa-briefcase me-2"></i>Voir les missions
            </a>
        </div>

        <!-- Illustration -->
        <div class="mt-5">
            <img src="https://images.pexels.com/photos/3807517/pexels-photo-3807517.jpeg?auto=compress&cs=tinysrgb&w=400"
                 alt="Page non trouvée" class="img-fluid rounded-4" style="max-height:280px;object-fit:cover;opacity:0.7;">
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
