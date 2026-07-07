<?php
/**
 * Paiement - SunuJob Étudiant
 */

$pageTitle = 'Paiement - SunuJob Étudiant';

require_once __DIR__ . '/includes/auth.php';
verifierSession();
require_once __DIR__ . '/includes/db.php';

$erreurs = [];
$success = null;
$form = [
    'montant' => '',
    'methode' => 'orange_money',
    'destinataire' => '',
    'reference' => '',
    'commentaire' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['montant'] = trim($_POST['montant'] ?? '');
    $form['methode'] = trim($_POST['methode'] ?? 'orange_money');
    $form['destinataire'] = trim($_POST['destinataire'] ?? '');
    $form['reference'] = trim($_POST['reference'] ?? '');
    $form['commentaire'] = trim($_POST['commentaire'] ?? '');

    if ($form['montant'] === '' || !is_numeric($form['montant']) || (float)$form['montant'] <= 0) {
        $erreurs[] = 'Le montant doit être un nombre positif.';
    }
    if (!in_array($form['methode'], ['orange_money', 'wave', 'virement'], true)) {
        $erreurs[] = 'La méthode de paiement sélectionnée est invalide.';
    }
    if ($form['destinataire'] === '') {
        $erreurs[] = 'Le numéro ou destinataire est requis.';
    }
    if ($form['reference'] === '') {
        $erreurs[] = 'La référence de paiement est requise.';
    }

    if (empty($erreurs)) {
        $stmt = $conn->prepare("INSERT INTO paiements (utilisateur_id, montant, methode, destinataire, reference, commentaire) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            'idssss',
            $_SESSION['user_id'],
            $form['montant'],
            $form['methode'],
            $form['destinataire'],
            $form['reference'],
            $form['commentaire']
        );

        if ($stmt->execute()) {
            $success = 'Votre demande de paiement a été enregistrée. Nous vous contacterons pour la confirmation.';
            $form = [
                'montant' => '',
                'methode' => 'orange_money',
                'destinataire' => '',
                'reference' => '',
                'commentaire' => ''
            ];
        } else {
            $erreurs[] = 'Impossible d’enregistrer la demande. Veuillez réessayer.';
        }
    }
}

$pageActive = 'paiement';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-credit-card me-2"></i>Paiement</h1>
        <p class="mb-0 text-white-50">Enregistrez votre demande de paiement via Orange Money, Wave ou virement bancaire.</p>
    </div>
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-dashboard p-4 shadow-lg" style="border:1px solid #e5e7eb; border-radius: 20px;">
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;background:linear-gradient(135deg,#22c55e,#16a34a);">
                        <i class="fas fa-mobile-alt fa-2x text-white"></i>
                    </div>
                    <h3 class="mb-1">Paiement mobile Wave</h3>
                    <p class="text-muted mb-0">Payez rapidement comme sur votre téléphone avec une expérience fluide et sécurisée.</p>
                </div>
                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger-custom mb-4">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?= htmlspecialchars($erreur) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success-custom mb-4">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/paiement.php">
                    <div class="mb-3">
                        <label for="montant" class="form-label">Montant (FCFA)</label>
                        <input type="number" step="0.01" min="0" id="montant" name="montant" class="form-control" value="<?= htmlspecialchars($form['montant']) ?>" placeholder="Ex : 25000" required>
                    </div>
                    <div class="mb-3">
                        <label for="methode" class="form-label">Méthode de paiement</label>
                        <select id="methode" name="methode" class="form-select" required>
                            <option value="orange_money" <?= $form['methode'] === 'orange_money' ? 'selected' : '' ?>>Orange Money</option>
                            <option value="wave" <?= $form['methode'] === 'wave' ? 'selected' : '' ?>>Wave</option>
                            <option value="virement" <?= $form['methode'] === 'virement' ? 'selected' : '' ?>>Virement bancaire</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="destinataire" class="form-label">Numéro / Destinataire</label>
                        <input type="text" id="destinataire" name="destinataire" class="form-control" value="<?= htmlspecialchars($form['destinataire']) ?>" placeholder="Ex : 77 123 45 67 ou nom du compte" required>
                    </div>
                    <div class="mb-3">
                        <label for="reference" class="form-label">Référence de paiement</label>
                        <input type="text" id="reference" name="reference" class="form-control" value="<?= htmlspecialchars($form['reference']) ?>" placeholder="Ex : PYMT-2026-001" required>
                    </div>
                    <div class="mb-3">
                        <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                        <textarea id="commentaire" name="commentaire" class="form-control" rows="3"><?= htmlspecialchars($form['commentaire']) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-cta btn-lg w-100"><i class="fas fa-paper-plane me-2"></i>Envoyer la demande</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php';
