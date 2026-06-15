<?php
/**
 * Page Notifications — SunuJob Étudiant
 */

$pageTitle  = 'Notifications — SunuJob Étudiant';
$pageActive = '';

require_once 'includes/db.php';
require_once 'includes/auth.php';

verifierSession();

$userId = $_SESSION['user_id'];

// Marquer toutes comme lues si demandé
if (isset($_GET['markall'])) {
    $stmt = $conn->prepare("UPDATE notifications SET lu = 1 WHERE utilisateur_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    header('Location: /notifications.php');
    exit;
}

// Marquer une notif comme lue et rediriger
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $notifId = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE notifications SET lu = 1 WHERE id = ? AND utilisateur_id = ?");
    $stmt->bind_param("ii", $notifId, $userId);
    $stmt->execute();

    // Récupérer le lien associé
    $stmt = $conn->prepare("SELECT lien FROM notifications WHERE id = ? AND utilisateur_id = ?");
    $stmt->bind_param("ii", $notifId, $userId);
    $stmt->execute();
    $notif = $stmt->get_result()->fetch_assoc();

    if ($notif && $notif['lien']) {
        header('Location: ' . $notif['lien']);
        exit;
    }
    header('Location: /notifications.php');
    exit;
}

// Supprimer une notif
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notifId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND utilisateur_id = ?");
    $stmt->bind_param("ii", $notifId, $userId);
    $stmt->execute();
    header('Location: /notifications.php');
    exit;
}

// Récupérer toutes les notifications
$stmt = $conn->prepare("
    SELECT * FROM notifications
    WHERE utilisateur_id = ?
    ORDER BY created_at DESC
    LIMIT 50
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$nbNonLues = array_reduce($notifications, function($carry, $n) {
    return $carry + ($n['lu'] == 0 ? 1 : 0);
}, 0);

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-bell me-2"></i>Notifications
                    <?php if ($nbNonLues > 0): ?>
                        <span class="badge" style="background:var(--color-accent-orange);font-size:0.7rem;vertical-align:middle;"><?= $nbNonLues ?></span>
                    <?php endif; ?>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/index.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Notifications</li>
                    </ol>
                </nav>
            </div>
            <?php if ($nbNonLues > 0): ?>
                <a href="?markall=1" class="btn btn-outline-custom"
                   style="border-color:rgba(255,255,255,0.5);color:#fff;">
                    <i class="fas fa-check-double me-2"></i>Tout marquer lu
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <?php if (empty($notifications)): ?>
                <div class="empty-state py-5">
                    <div class="empty-state-icon"><i class="fas fa-bell-slash"></i></div>
                    <h4>Aucune notification</h4>
                    <p>Vous n'avez pas encore de notifications.</p>
                    <a href="/index.php" class="btn btn-primary-custom">Retour à l'accueil</a>
                </div>
            <?php else: ?>
                <div class="card-dashboard overflow-hidden">
                    <?php foreach ($notifications as $notif):
                        // Icône selon le type
                        $iconMap = [
                            'info'    => ['fa-info-circle',   'info'],
                            'alerte'  => ['fa-exclamation-triangle', 'warning'],
                            'success' => ['fa-check-circle',  'success'],
                            'erreur'  => ['fa-times-circle',  'error'],
                        ];
                        $ico = $iconMap[$notif['type']] ?? ['fa-bell', 'info'];
                    ?>
                        <div class="notification-item <?= $notif['lu'] == 0 ? 'unread' : '' ?>">
                            <div class="notif-icon <?= $ico[1] ?>">
                                <i class="fas <?= $ico[0] ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <strong style="font-size:0.9rem;color:var(--color-primary);">
                                            <?= htmlspecialchars($notif['titre']) ?>
                                        </strong>
                                        <?php if ($notif['lu'] == 0): ?>
                                            <span class="badge ms-2" style="background:var(--color-accent-orange);font-size:0.65rem;">Nouveau</span>
                                        <?php endif; ?>
                                        <p class="mb-1 mt-1" style="font-size:0.85rem;color:var(--color-text-muted);">
                                            <?= htmlspecialchars($notif['message']) ?>
                                        </p>
                                        <small style="color:var(--color-text-light);">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('d/m/Y à H:i', strtotime($notif['created_at'])) ?>
                                        </small>
                                    </div>
                                    <div class="d-flex gap-2 flex-shrink-0">
                                        <?php if ($notif['lien']): ?>
                                            <a href="/notifications.php?id=<?= $notif['id'] ?>"
                                               class="btn btn-sm btn-primary-custom">
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        <?php elseif ($notif['lu'] == 0): ?>
                                            <a href="/notifications.php?id=<?= $notif['id'] ?>"
                                               class="btn btn-sm btn-outline-custom" title="Marquer lu">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="/notifications.php?delete=<?= $notif['id'] ?>"
                                           class="btn btn-sm" style="border:1px solid #FECACA;color:#DC2626;"
                                           onclick="return confirm('Supprimer cette notification ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
