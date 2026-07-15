<?php
/**
 * Header commun — SunuJob Étudiant
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer infos utilisateur connecté
$user = null;
$nbNotifs = 0;

if (isset($_SESSION['user_id'])) {
    $user = [
        'id'          => $_SESSION['user_id'],
        'nom'         => $_SESSION['user_nom'],
        'prenom'      => $_SESSION['user_prenom'],
        'email'       => $_SESSION['user_email'],
        'role'        => $_SESSION['user_role'],
        'photo'       => $_SESSION['user_photo'] ?? null,
        'localisation'=> $_SESSION['user_localisation'] ?? null
    ];

    // Compter notifications non lues
    if (isset($conn)) {
        $stmtN = $conn->prepare("SELECT COUNT(*) as nb FROM notifications WHERE utilisateur_id = ? AND lu = 0");
        $stmtN->bind_param("i", $user['id']);
        $stmtN->execute();
        $nbNotifs = (int)$stmtN->get_result()->fetch_assoc()['nb'];
    }
}

$pageActive = $pageActive ?? '';

// Choisir la source du logo - SVG privilégié (transparent, haute qualité)
$logoPath = '/assets/images/logo.svg';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SunuJob Étudiant — Plateforme de missions temporaires pour étudiants sénégalais">
    <title><?= $pageTitle ?? 'SunuJob Étudiant' ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS Custom -->
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg navbar-sunujob sticky-top">
    <div class="container">

        <a class="navbar-brand" href="/index.php">
            <img class="navbar-logo" src="<?= $logoPath ?>" alt="SunuJob Étudiant"
                 onerror="this.onerror=null; this.src='<?= str_replace('.png', '.svg', $logoPath) ?>';">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Nav centre (principal) -->
            <ul class="navbar-nav navbar-nav-center mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $pageActive === 'accueil' ? 'active' : '' ?>" href="/index.php">
                        <i class="fas fa-home me-1"></i> Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $pageActive === 'missions' ? 'active' : '' ?>" href="/missions.php">
                        <i class="fas fa-briefcase me-1"></i> Missions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $pageActive === 'categories' ? 'active' : '' ?>" href="/categories.php">
                        <i class="fas fa-th-large me-1"></i> Catégories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $pageActive === 'a-propos' ? 'active' : '' ?>" href="/a-propos.php">
                        <i class="fas fa-info-circle me-1"></i> À propos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $pageActive === 'contact' ? 'active' : '' ?>" href="/contact.php">
                        <i class="fas fa-envelope me-1"></i> Contact
                    </a>
                </li>
                <!-- 'Mes missions' removed from top navbar for recruiters -->
                <!-- Admin link removed from top nav (dashboard accessible via user menu) -->
            </ul>

            <!-- Nav droite -->
            <ul class="navbar-nav ms-auto align-items-center gap-1">
                <?php if ($user): ?>
                    <?php
                    $dashUrl  = $user['role'] === 'etudiant' ? '/pages/etudiant/dashboard.php'  : ($user['role'] === 'recruteur' ? '/pages/recruteur/dashboard.php' : '/pages/admin/dashboard.php');
                    $profilUrl= $user['role'] === 'etudiant' ? '/pages/etudiant/profil.php'     : '/pages/recruteur/profil.php';
                    $candUrl  = $user['role'] === 'etudiant' ? '/pages/etudiant/mes-candidatures.php' : '/pages/recruteur/candidatures.php';
                    ?>

                    <!-- Notifications -->
                    <li class="nav-item">
                        <a class="nav-link notif-badge position-relative" href="/notifications.php" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <?php if ($nbNotifs > 0): ?>
                                <span class="count"><?= $nbNotifs ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Menu utilisateur -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <?php if ($user['role'] !== 'admin'): ?>
                                <?php if ($user['photo']): ?>
                                    <img src="/uploads/photos/<?= htmlspecialchars($user['photo']) ?>"
                                         alt="Photo" class="rounded-circle"
                                         style="width:32px;height:32px;object-fit:cover;border:2px solid rgba(255,255,255,0.5);">
                                <?php else: ?>
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                                          style="width:32px;height:32px;background:rgba(255,255,255,0.2);font-size:0.85rem;font-weight:700;">
                                        <?= strtoupper(substr($user['prenom'],0,1)) ?>
                                    </span>
                                <?php endif; ?>
                                <span class="d-none d-lg-inline"><?= htmlspecialchars($user['prenom']) ?></span>
                            <?php else: ?>
                                <i class="fas fa-cog" style="font-size: 1.1rem;"></i>
                                <span class="d-none d-lg-inline">Admin</span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <div class="px-3 py-2 border-bottom">
                                    <div class="fw-semibold" style="color:var(--color-primary);">
                                        <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                                    </div>
                                    <small class="text-muted"><?= $user['role'] === 'etudiant' ? 'Étudiant' : ($user['role'] === 'recruteur' ? 'Recruteur' : 'Administrateur') ?></small>
                                </div>
                            </li>
                            <li><a class="dropdown-item" href="<?= $dashUrl ?>"><i class="fas fa-tachometer-alt me-2 text-muted"></i>Dashboard</a></li>
                            <?php if ($user['role'] !== 'admin'): ?>
                                <li><a class="dropdown-item" href="<?= $profilUrl ?>"><i class="fas fa-user me-2 text-muted"></i>Mon profil</a></li>
                                <li><a class="dropdown-item" href="<?= $candUrl ?>"><i class="fas fa-list me-2 text-muted"></i>
                                    <?= $user['role'] === 'etudiant' ? 'Mes candidatures' : 'Candidatures reçues' ?></a></li>
                                <?php if ($user['role'] === 'etudiant'): ?>
                                    <li><a class="dropdown-item" href="/pages/etudiant/abonnement.php"><i class="fas fa-crown me-2 text-muted"></i>Abonnement</a></li>
                                <?php endif; ?>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="/pages/admin/users.php"><i class="fas fa-users-cog me-2 text-muted"></i>Gérer les utilisateurs</a></li>
                                <li><a class="dropdown-item" href="/pages/admin/missions.php"><i class="fas fa-briefcase me-2 text-muted"></i>Gérer les missions</a></li>
                                <li><a class="dropdown-item" href="/pages/admin/categories.php"><i class="fas fa-tags me-2 text-muted"></i>Gérer les catégories</a></li>
                                <li><a class="dropdown-item" href="/pages/admin/abonnements.php"><i class="fas fa-crown me-2 text-muted"></i>Abonnements</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="/notifications.php"><i class="fas fa-bell me-2 text-muted"></i>Notifications
                                <?php if ($nbNotifs > 0): ?>
                                    <span class="badge bg-danger ms-1"><?= $nbNotifs ?></span>
                                <?php endif; ?>
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-cta btn-sm ms-2 px-4" href="/register.php">
                            <i class="fas fa-user-plus me-1"></i> S'inscrire
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash messages (Toast) -->
<?php if (isset($_SESSION['flash_message'])): ?>
<div id="flashToast" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show rounded-3" role="alert" style="min-width: 320px; max-width: 500px;">
        <i class="fas fa-<?= ($_SESSION['flash_type'] ?? 'info') === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
        <span><?= htmlspecialchars($_SESSION['flash_message']) ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flashToast = document.getElementById('flashToast');
        if (flashToast) {
            const alert = flashToast.querySelector('.alert');
            const bsAlert = new bootstrap.Alert(alert);
            setTimeout(() => {
                bsAlert.close();
            }, 4500);
        }
    });
</script>
<?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>
