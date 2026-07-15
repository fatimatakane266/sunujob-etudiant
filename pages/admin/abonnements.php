<?php
/**
 * Gestion des abonnements et paiements Wave
 * SunuJob Étudiant
 */

$pageTitle = 'Abonnements - Admin';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
verifierSession('admin');
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    exigerCsrfPost('/pages/admin/abonnements.php');

    $action = $_POST['action'];

    if ($action === 'prolonger' && isset($_POST['abonnement_id'])) {
        $id = (int)$_POST['abonnement_id'];
        $stmt = $conn->prepare("UPDATE abonnements SET date_fin = DATE_ADD(GREATEST(date_fin, CURDATE()), INTERVAL 30 DAY), statut = 'actif' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Abonnement prolongé de 30 jours.";
            $_SESSION['flash_type'] = 'success';
        }
    }

    if ($action === 'desactiver' && isset($_POST['abonnement_id'])) {
        $id = (int)$_POST['abonnement_id'];
        $stmt = $conn->prepare("UPDATE abonnements SET statut = 'annule' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Abonnement désactivé.";
            $_SESSION['flash_type'] = 'success';
        }
    }

    if ($action === 'reactiver' && isset($_POST['abonnement_id'])) {
        $id = (int)$_POST['abonnement_id'];
        $stmt = $conn->prepare("UPDATE abonnements SET statut = 'actif', date_fin = GREATEST(date_fin, DATE_ADD(CURDATE(), INTERVAL 30 DAY)) WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Abonnement réactivé.";
            $_SESSION['flash_type'] = 'success';
        }
    }

    if ($action === 'enregistrer_lien_wave') {
        $lien = trim($_POST['wave_lien_partage'] ?? '');
        setParametre('wave_lien_partage', $lien);
        $_SESSION['flash_message'] = $lien !== '' ? "Lien de partage Wave enregistré." : "Lien de partage Wave effacé.";
        $_SESSION['flash_type'] = 'success';
    }

    if ($action === 'marquer_paye' && isset($_POST['paiement_id'])) {
        $id = (int)$_POST['paiement_id'];
        $stmt = $conn->prepare("SELECT * FROM paiements WHERE id = ? AND statut = 'en_attente'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $paiement = $stmt->get_result()->fetch_assoc();

        if ($paiement) {
            $plan = getPlanParMontant($paiement['montant']);
            if ($plan) {
                $abonnementId = creerAbonnement((int)$paiement['utilisateur_id'], $plan, $paiement['reference_wave']);
                $stmt = $conn->prepare("UPDATE paiements SET statut = 'paye', date_paiement = NOW(), abonnement_id = ? WHERE id = ?");
                $stmt->bind_param("ii", $abonnementId, $id);
                $stmt->execute();
                $_SESSION['flash_message'] = "Paiement marqué comme payé, abonnement activé.";
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = "Montant du paiement ne correspond à aucune offre connue.";
                $_SESSION['flash_type'] = 'danger';
            }
        }
    }

    header('Location: /pages/admin/abonnements.php');
    exit;
}

$waveLienPartage = getParametre('wave_lien_partage') ?? '';

// Filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statutFiltre = isset($_GET['statut']) && in_array($_GET['statut'], ['actif', 'expire', 'annule'], true) ? $_GET['statut'] : '';

$where = [];
$params = [];
$types = '';
if ($search !== '') {
    $where[] = "(u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
    $types .= 'sss';
}
if ($statutFiltre !== '') {
    $where[] = "a.statut = ?";
    $params[] = $statutFiltre;
    $types .= 's';
}
$whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $conn->prepare("
    SELECT a.*, u.nom, u.prenom, u.email
    FROM abonnements a
    JOIN utilisateurs u ON u.id = a.utilisateur_id
    $whereClause
    ORDER BY a.created_at DESC
");
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$abonnements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Paiements récents
$paiements = $conn->query("
    SELECT p.*, u.nom, u.prenom, u.email
    FROM paiements p
    JOIN utilisateurs u ON u.id = p.utilisateur_id
    ORDER BY p.created_at DESC
    LIMIT 50
")->fetch_all(MYSQLI_ASSOC);

// Statistiques
$stats = [
    'total_abonnes' => 0,
    'actifs' => 0,
    'expires' => 0,
    'revenus_totaux' => 0,
    'revenus_mensuels' => 0,
    'paiements_reussis' => 0,
    'paiements_echoues' => 0,
];

$r = $conn->query("SELECT COUNT(DISTINCT utilisateur_id) as total FROM abonnements");
$stats['total_abonnes'] = (int)$r->fetch_assoc()['total'];

$r = $conn->query("SELECT COUNT(*) as total FROM abonnements WHERE statut = 'actif' AND date_fin >= CURDATE()");
$stats['actifs'] = (int)$r->fetch_assoc()['total'];

$r = $conn->query("SELECT COUNT(*) as total FROM abonnements WHERE statut = 'expire' OR (statut = 'actif' AND date_fin < CURDATE())");
$stats['expires'] = (int)$r->fetch_assoc()['total'];

$r = $conn->query("SELECT COALESCE(SUM(montant), 0) as total FROM paiements WHERE statut = 'paye'");
$stats['revenus_totaux'] = (float)$r->fetch_assoc()['total'];

$r = $conn->query("SELECT COALESCE(SUM(montant), 0) as total FROM paiements WHERE statut = 'paye' AND MONTH(date_paiement) = MONTH(CURDATE()) AND YEAR(date_paiement) = YEAR(CURDATE())");
$stats['revenus_mensuels'] = (float)$r->fetch_assoc()['total'];

$r = $conn->query("SELECT COUNT(*) as total FROM paiements WHERE statut = 'paye'");
$stats['paiements_reussis'] = (int)$r->fetch_assoc()['total'];

$r = $conn->query("SELECT COUNT(*) as total FROM paiements WHERE statut = 'echoue'");
$stats['paiements_echoues'] = (int)$r->fetch_assoc()['total'];

$pageActive = 'admin';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1>Abonnements &amp; paiements</h1>
                <p class="mb-0 text-white-50">Suivi des abonnements étudiants et des paiements Wave.</p>
            </div>
            <a href="/pages/admin/dashboard.php" class="btn btn-outline-header"><i class="fas fa-arrow-left me-2"></i>Retour au dashboard</a>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Configuration Wave -->
    <div class="card-dashboard p-4 mb-4">
        <h5 class="mb-3"><i class="fas fa-mobile-alt me-2"></i>Configuration du paiement Wave</h5>
        <p class="text-muted small mb-3">
            Colle ici ton lien de partage Wave personnel (menu "Partager mon lien" dans l'application Wave).
            C'est ce lien qui sera encodé dans le QR code présenté aux étudiants au moment de payer un abonnement.
            Wave ne prévenant pas automatiquement le site quand un paiement arrive, pense à vérifier ton compte Wave
            puis à valider manuellement le paiement correspondant ci-dessous (section "Paiements Wave récents").
        </p>
        <form method="POST" action="" class="row g-2 align-items-end">
            <?= champCsrf() ?>
            <input type="hidden" name="action" value="enregistrer_lien_wave">
            <div class="col-md-9">
                <label for="wave_lien_partage" class="form-label">Lien de partage Wave</label>
                <input type="url" class="form-control" id="wave_lien_partage" name="wave_lien_partage"
                       value="<?= htmlspecialchars($waveLienPartage) ?>" placeholder="https://pay.wave.com/...">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary-custom w-100">
                    <i class="fas fa-save me-1"></i>Enregistrer
                </button>
            </div>
        </form>
        <?php if ($waveLienPartage): ?>
            <p class="small text-success mt-2 mb-0"><i class="fas fa-check-circle me-1"></i>Lien actuellement configuré.</p>
        <?php else: ?>
            <p class="small text-muted mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>Aucun lien configuré pour le moment — les étudiants voient un QR code de démonstration.</p>
        <?php endif; ?>
    </div>

    <!-- Statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['total_abonnes'] ?></div>
                        <div class="stat-label">Abonnés (total)</div>
                    </div>
                    <i class="fas fa-users stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['actifs'] ?></div>
                        <div class="stat-label">Actifs</div>
                    </div>
                    <i class="fas fa-crown stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['expires'] ?></div>
                        <div class="stat-label">Expirés</div>
                    </div>
                    <i class="fas fa-hourglass-end stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= number_format($stats['revenus_totaux'], 0, ',', ' ') ?></div>
                        <div class="stat-label">Revenus totaux (FCFA)</div>
                    </div>
                    <i class="fas fa-coins stat-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4 col-6">
            <div class="stat-card primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= number_format($stats['revenus_mensuels'], 0, ',', ' ') ?></div>
                        <div class="stat-label">Revenus ce mois (FCFA)</div>
                    </div>
                    <i class="fas fa-calendar-alt stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['paiements_reussis'] ?></div>
                        <div class="stat-label">Paiements réussis</div>
                    </div>
                    <i class="fas fa-check-circle stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #991B1B, #DC2626);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?= $stats['paiements_echoues'] ?></div>
                        <div class="stat-label">Paiements échoués</div>
                    </div>
                    <i class="fas fa-times-circle stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <form method="GET" action="" novalidate class="row g-3 mb-4 align-items-end">
        <div class="col-md-5">
            <label for="search" class="form-label">Rechercher un étudiant</label>
            <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nom, prénom ou email...">
        </div>
        <div class="col-md-4">
            <label for="statut" class="form-label">Statut</label>
            <select class="form-select" id="statut" name="statut">
                <option value="">Tous les statuts</option>
                <option value="actif" <?= $statutFiltre === 'actif' ? 'selected' : '' ?>>Actif</option>
                <option value="expire" <?= $statutFiltre === 'expire' ? 'selected' : '' ?>>Expiré</option>
                <option value="annule" <?= $statutFiltre === 'annule' ? 'selected' : '' ?>>Annulé</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary-custom flex-fill">
                <i class="fas fa-filter me-1"></i>Filtrer
            </button>
            <a href="/pages/admin/abonnements.php" class="btn btn-outline-custom" title="Réinitialiser">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>

    <!-- Abonnements -->
    <div class="card-dashboard mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0" style="color: var(--color-primary);"><i class="fas fa-crown me-2"></i>Abonnements (<?= count($abonnements) ?>)</h5>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Offre</th>
                            <th>Prix</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($abonnements)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">Aucun abonnement trouvé.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($abonnements as $a): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($a['email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars(getPlanAbonnement($a['type_abonnement'])['label'] ?? ucfirst($a['type_abonnement'])) ?></td>
                                <td><?= number_format($a['prix'], 0, ',', ' ') ?> FCFA</td>
                                <td><?= date('d/m/Y', strtotime($a['date_debut'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($a['date_fin'])) ?></td>
                                <td><span class="badge badge-<?= badgeClassStatutAbonnement($a['statut']) ?>"><?= libelleStatutAbonnement($a['statut']) ?></span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <form method="POST" action="">
                                            <?= champCsrf() ?>
                                            <input type="hidden" name="action" value="prolonger">
                                            <input type="hidden" name="abonnement_id" value="<?= $a['id'] ?>">
                                            <button type="submit" class="btn btn-sm admin-action-btn btn-outline-custom" title="Prolonger de 30 jours">
                                                <i class="fas fa-calendar-plus"></i>
                                            </button>
                                        </form>
                                        <?php if ($a['statut'] === 'actif'): ?>
                                            <form method="POST" action="" onsubmit="return confirm('Désactiver cet abonnement ?');">
                                                <?= champCsrf() ?>
                                                <input type="hidden" name="action" value="desactiver">
                                                <input type="hidden" name="abonnement_id" value="<?= $a['id'] ?>">
                                                <button type="submit" class="btn btn-sm admin-action-btn btn-danger" title="Désactiver">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="">
                                                <?= champCsrf() ?>
                                                <input type="hidden" name="action" value="reactiver">
                                                <input type="hidden" name="abonnement_id" value="<?= $a['id'] ?>">
                                                <button type="submit" class="btn btn-sm admin-action-btn btn-outline-custom" title="Réactiver">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paiements -->
    <div class="card-dashboard">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0" style="color: var(--color-primary);"><i class="fas fa-mobile-alt me-2"></i>Paiements Wave récents</h5>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Montant</th>
                            <th>Référence</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paiements)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Aucun paiement enregistré.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($paiements as $p): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($p['email']) ?></small>
                                </td>
                                <td><?= number_format($p['montant'], 0, ',', ' ') ?> FCFA</td>
                                <td><small><?= htmlspecialchars($p['reference_wave'] ?? '—') ?></small></td>
                                <td>
                                    <?php
                                    $badgeMap = ['en_attente' => 'attente', 'paye' => 'acceptee', 'echoue' => 'refusee'];
                                    $labelMap = ['en_attente' => 'En attente', 'paye' => 'Payé', 'echoue' => 'Échoué'];
                                    ?>
                                    <span class="badge badge-<?= $badgeMap[$p['statut']] ?? 'attente' ?>"><?= $labelMap[$p['statut']] ?? ucfirst($p['statut']) ?></span>
                                </td>
                                <td><small><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <?php if ($p['statut'] === 'en_attente'): ?>
                                        <form method="POST" action="" onsubmit="return confirm('Marquer ce paiement comme payé et activer l\'abonnement ?');">
                                            <?= champCsrf() ?>
                                            <input type="hidden" name="action" value="marquer_paye">
                                            <input type="hidden" name="paiement_id" value="<?= $p['id'] ?>">
                                            <button title="Marquer payé" type="submit" class="btn btn-sm admin-action-btn btn-outline-custom" title="Marquer comme payé">
                                                <i class="fas fa-check"></i> 
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
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

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
