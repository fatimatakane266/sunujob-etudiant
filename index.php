<?php
/**
 * Page d'accueil — SunuJob Étudiant
 */

$pageTitle  = 'SunuJob Étudiant — Missions temporaires pour étudiants sénégalais';
$pageActive = 'accueil';

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

/* ---------- statistiques ---------- */
$stats = ['missions' => 0, 'etudiants' => 0, 'recruteurs' => 0, 'candidatures' => 0];

$r = $conn->query("SELECT COUNT(*) c FROM missions WHERE statut='active'");
if ($r) $stats['missions'] = (int)$r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) c FROM utilisateurs WHERE role='etudiant'");
if ($r) $stats['etudiants'] = (int)$r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) c FROM utilisateurs WHERE role='recruteur'");
if ($r) $stats['recruteurs'] = (int)$r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) c FROM candidatures");
if ($r) $stats['candidatures'] = (int)$r->fetch_assoc()['c'];

/* ---------- dernières missions ---------- */
$dernieresMissions = [];
$r = $conn->query("
    SELECT m.*, c.nom cat_nom, c.icone,
           COALESCE(pr.nom_structure, CONCAT(u.prenom,' ',u.nom)) recruteur_label
    FROM missions m
    JOIN categories c  ON m.categorie_id = c.id
    JOIN utilisateurs u ON m.recruteur_id = u.id
    LEFT JOIN profils_recruteurs pr ON pr.utilisateur_id = u.id
    WHERE m.statut = 'active'
    ORDER BY m.created_at DESC
    LIMIT 6
");
if ($r) { while ($row = $r->fetch_assoc()) $dernieresMissions[] = $row; }

/* ---------- catégories avec nb missions ---------- */
$categories = [];
$r = $conn->query("
    SELECT c.*, COUNT(m.id) nb
    FROM categories c
    LEFT JOIN missions m ON c.id = m.categorie_id AND m.statut='active'
    GROUP BY c.id
    ORDER BY nb DESC
    LIMIT 8
");
if ($r) { while ($row = $r->fetch_assoc()) $categories[] = $row; }
?>

<!-- ====================== HERO ====================== -->
<section class="hero-section">
    <div class="container position-relative" style="z-index:1;">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <!-- Badge -->
                <span class="d-inline-block mb-3 px-3 py-1 rounded-pill"
                      style="background:rgba(245,166,35,0.18);color:#F5A623;font-size:0.82rem;font-weight:600;border:1px solid rgba(245,166,35,0.3);">
                    <i class="fas fa-bolt me-1"></i> Plateforme #1 pour étudiants au Sénégal
                </span>

                <h1>Trouvez des <span class="accent-orange">missions</span> adaptées à vos <span class="accent-green">études</span></h1>

                <p class="subtitle">
                    SunuJob Étudiant connecte les étudiants sénégalais aux opportunités temporaires :
                    cours particuliers, événementiel, informatique, livraison et plus encore.
                </p>

                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="/register.php?role=etudiant" class="btn btn-cta btn-lg">
                        <i class="fas fa-user-graduate me-2"></i>Je suis étudiant
                    </a>
                    <a href="/register.php?role=recruteur" class="btn btn-outline-custom btn-lg"
                       style="border-color:rgba(255,255,255,0.5);color:#fff;">
                        <i class="fas fa-building me-2"></i>Je suis recruteur
                    </a>
                </div>

                <!-- Mini-stats hero -->
                <div class="d-flex flex-wrap gap-4">
                    <div>
                        <div style="color:#fff;font-size:1.5rem;font-weight:700;"><?= number_format($stats['missions']) ?>+</div>
                        <div style="color:rgba(255,255,255,0.7);font-size:0.82rem;">Missions actives</div>
                    </div>
                    <div style="width:1px;background:rgba(255,255,255,0.2);"></div>
                    <div>
                        <div style="color:#fff;font-size:1.5rem;font-weight:700;"><?= number_format($stats['etudiants']) ?>+</div>
                        <div style="color:rgba(255,255,255,0.7);font-size:0.82rem;">Étudiants inscrits</div>
                    </div>
                    <div style="width:1px;background:rgba(255,255,255,0.2);"></div>
                    <div>
                        <div style="color:#fff;font-size:1.5rem;font-weight:700;"><?= number_format($stats['recruteurs']) ?>+</div>
                        <div style="color:rgba(255,255,255,0.7);font-size:0.82rem;">Recruteurs</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-image">
                    <img src="assets/images/Image collée.png"
                         alt="Étudiants au travail" class="img-fluid w-100" style="height:420px;object-fit:cover;">
                </div>

                <!-- Floating card -->
                <div class="position-absolute d-none d-xl-block"
                     style="bottom:20px;left:-20px;background:#fff;border-radius:12px;padding:14px 18px;box-shadow:0 8px 30px rgba(27,63,114,0.25);min-width:200px;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;background:var(--gradient-accent);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-check-circle text-white fs-5"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;color:var(--color-primary);font-size:0.95rem;">Candidature envoyée !</div>
                            <div style="font-size:0.78rem;color:var(--color-text-muted);">Mission acceptée en 24h</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====================== STATS ====================== -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="stat-icon-wrap" style="background:rgba(27,63,114,0.08);">
                    <i class="fas fa-briefcase" style="color:var(--color-primary);"></i>
                </div>
                <span class="stat-number d-block"><?= number_format($stats['missions']) ?></span>
                <span class="stat-label">Missions actives</span>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-icon-wrap" style="background:rgba(45,155,78,0.1);">
                    <i class="fas fa-user-graduate" style="color:var(--color-accent-green);"></i>
                </div>
                <span class="stat-number d-block"><?= number_format($stats['etudiants']) ?></span>
                <span class="stat-label">Étudiants inscrits</span>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-icon-wrap" style="background:rgba(46,109,180,0.1);">
                    <i class="fas fa-building" style="color:var(--color-primary-light);"></i>
                </div>
                <span class="stat-number d-block"><?= number_format($stats['recruteurs']) ?></span>
                <span class="stat-label">Recruteurs</span>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-icon-wrap" style="background:rgba(245,166,35,0.12);">
                    <i class="fas fa-paper-plane" style="color:var(--color-accent-orange);"></i>
                </div>
                <span class="stat-number d-block"><?= number_format($stats['candidatures']) ?></span>
                <span class="stat-label">Candidatures envoyées</span>
            </div>
        </div>
    </div>
</section>

<!-- ====================== CATÉGORIES ====================== -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title center">Explorez par catégorie</h2>
            <p class="text-muted mt-4">Des missions dans tous les domaines, partout au Sénégal</p>
        </div>
        <div class="row g-3">
            <?php foreach ($categories as $cat): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="/missions.php?categorie=<?= $cat['id'] ?>" class="text-decoration-none d-block">
                        <div class="category-card">
                            <div class="icon-wrap">
                                <i class="fas <?= htmlspecialchars($cat['icone']) ?>"></i>
                            </div>
                            <h5><?= htmlspecialchars($cat['nom']) ?></h5>
                            <p class="count"><?= $cat['nb'] ?> mission<?= $cat['nb'] > 1 ? 's' : '' ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="/categories.php" class="btn btn-outline-custom">
                Toutes les catégories <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- ====================== DERNIÈRES MISSIONS ====================== -->
<section class="py-5" style="background:var(--color-white);">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-5 gap-3">
            <div>
                <h2 class="section-title mb-2">Dernières missions</h2>
                <p class="text-muted mb-0">Opportunités publiées récemment</p>
            </div>
            <a href="/missions.php" class="btn btn-outline-custom">
                Voir tout <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if (empty($dernieresMissions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-briefcase"></i></div>
                <h4>Aucune mission disponible</h4>
                <p>Soyez le premier à publier une mission !</p>
                <a href="/register.php?role=recruteur" class="btn btn-cta">Publier une mission</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($dernieresMissions as $m): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card-mission">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge-categorie">
                                        <i class="fas <?= htmlspecialchars($m['icone']) ?>"></i>
                                        <?= htmlspecialchars($m['cat_nom']) ?>
                                    </span>
                                    <span class="badge-active">Active</span>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($m['titre']) ?></h5>
                                <p class="card-text mb-3"><?= htmlspecialchars(mb_substr($m['description'], 0, 90)) ?>...</p>

                                <div class="d-flex flex-wrap gap-3 mb-3">
                                    <span class="localisation">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($m['localisation']) ?>
                                    </span>
                                    <span class="remuneration">
                                        <?= $m['remuneration'] ? number_format($m['remuneration'], 0, ',', ' ') . ' FCFA' : 'À négocier' ?>
                                    </span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-light text-muted" style="font-size:0.77rem;">
                                            <?= $m['type_mission'] === 'ponctuelle' ? 'Ponctuelle' : ($m['type_mission'] === 'temps_partiel' ? 'Temps partiel' : 'Stage') ?>
                                        </span>
                                    </div>
                                    <a href="/mission-detail.php?id=<?= $m['id'] ?>" class="btn btn-primary-custom btn-sm">
                                        Postuler <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ====================== COMMENT ÇA MARCHE ====================== -->
<section class="steps-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title center">Comment ça marche ?</h2>
            <p class="text-muted mt-4">3 étapes simples pour commencer</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <h5>Créez votre compte</h5>
                    <p>Inscrivez-vous gratuitement en tant qu'étudiant ou recruteur en moins de 2 minutes.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-item">
                    <div class="step-number">2</div>
                    <h5>Trouvez ou publiez</h5>
                    <p>Étudiants : cherchez parmi des dizaines de missions. Recruteurs : publiez facilement.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-item">
                    <div class="step-number">3</div>
                    <h5>Connectez-vous</h5>
                    <p>Postulez ou gérez les candidatures reçues directement depuis votre espace personnel.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====================== TÉMOIGNAGES ====================== -->
<section class="py-5" style="background:var(--color-white);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title center">Ils nous font confiance</h2>
        </div>
        <div class="row g-4">
            <?php
            $testimonials = [
                ['name' => 'Fatou Diallo', 'role' => 'Étudiante en Marketing', 'univ' => 'UCAD', 'text' => "J'ai trouvé une mission d'assistante événementielle en 2 jours grâce à SunuJob. La plateforme est simple et efficace !", 'initials' => 'FD'],
                ['name' => 'Moussa Seck', 'role' => 'Recruteur', 'univ' => 'StartupHub SN', 'text' => "Nous avons recruté 3 community managers étudiants pour nos événements. Un outil indispensable pour les recrutements rapides.", 'initials' => 'MS'],
                ['name' => 'Aminata Ba', 'role' => 'Étudiante en Informatique', 'univ' => 'ESP Dakar', 'text' => "SunuJob m'a permis de décrocher une mission de développement web tout en continuant mes cours. Je recommande vivement.", 'initials' => 'AB'],
            ];
            foreach ($testimonials as $t):
            ?>
                <div class="col-md-4">
                    <div class="card-dashboard p-4 h-100">
                        <div class="d-flex mb-3">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star" style="color:var(--color-accent-orange);font-size:0.85rem;"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="mb-4" style="color:var(--color-text-muted);font-size:0.9rem;line-height:1.7;">
                            "<?= $t['text'] ?>"
                        </p>
                        <div class="d-flex align-items-center gap-3 mt-auto">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                 style="width:44px;height:44px;background:var(--gradient-primary);color:#fff;font-size:0.9rem;">
                                <?= $t['initials'] ?>
                            </div>
                            <div>
                                <div class="fw-semibold" style="font-size:0.9rem;"><?= $t['name'] ?></div>
                                <div style="font-size:0.78rem;color:var(--color-text-muted);"><?= $t['role'] ?> · <?= $t['univ'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ====================== CTA FINAL ====================== -->
<section class="py-5">
    <div class="container">
        <div class="rounded-4 text-white text-center p-5" style="background:var(--gradient-hero);position:relative;overflow:hidden;">
            <div style="position:absolute;top:-60px;right:-60px;width:250px;height:250px;background:radial-gradient(circle,rgba(245,166,35,0.2) 0%,transparent 70%);"></div>
            <div style="position:relative;z-index:1;">
                <h2 style="color:#fff;font-size:2rem;margin-bottom:1rem;">Prêt à commencer ?</h2>
                <p class="mb-4" style="opacity:0.88;font-size:1.05rem;">
                    Rejoignez des milliers d'étudiants et recruteurs au Sénégal.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="/register.php?role=etudiant" class="btn btn-cta btn-lg">
                        <i class="fas fa-user-graduate me-2"></i>Inscription étudiant
                    </a>
                    <a href="/register.php?role=recruteur"
                       class="btn btn-lg" style="border:2px solid rgba(255,255,255,0.5);color:#fff;border-radius:var(--radius-md);">
                        <i class="fas fa-building me-2"></i>Inscription recruteur
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
