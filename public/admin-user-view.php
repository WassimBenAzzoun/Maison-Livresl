<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Bibliotheque.php';
require_once __DIR__ . '/../app/models/Emprunt.php';
require_once __DIR__ . '/../app/models/User.php';

require_admin_page();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$userModel = new User();
$user = $userModel->findWithMembership($id);
if (!$user) {
    flash_set('danger', 'Utilisateur introuvable.');
    redirect_page('admin-users');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'membership_save') {
    $membershipType = $_POST['membership_type'] ?? 'none';
    $membershipPaidAt = trim($_POST['membership_paid_at'] ?? '');
    $membershipExpiresAt = trim($_POST['membership_expires_at'] ?? '');
    $membershipBranchId = (int) ($_POST['membership_branch_id'] ?? 0);

    if (!in_array($membershipType, ['none', 'monthly', 'yearly'], true)) {
        flash_set('warning', 'Type d\'adhésion invalide.');
    } else {
        if ($membershipType !== 'none') {
            $startDate = $membershipPaidAt !== '' ? $membershipPaidAt : date('Y-m-d');
            $start = new DateTime($startDate);
            $end = clone $start;
            $end->modify($membershipType === 'monthly' ? '+1 month' : '+1 year');
            $membershipPaidAt = $start->format('Y-m-d');
            $membershipExpiresAt = $end->format('Y-m-d');
        } else {
            $membershipPaidAt = null;
            $membershipExpiresAt = null;
        }

        $userModel->updateMembership($id, [
            'membership_type' => $membershipType,
            'membership_paid_at' => $membershipPaidAt,
            'membership_expires_at' => $membershipExpiresAt,
            'membership_branch_id' => $membershipBranchId > 0 ? $membershipBranchId : null,
        ]);

        flash_set('success', 'Adhésion mise à jour.');
    }

    redirect_page('admin-user-view', ['id' => $id]);
}

$empruntModel = new Emprunt();
$currentBorrowings = $empruntModel->currentByUser((int) $user->getId());
$previousBorrowings = $empruntModel->previousByUser((int) $user->getId());
$branches = (new Bibliotheque())->all();
$pageTitle = 'Maison des Livres | Fiche utilisateur';
$activePage = 'admin-users';
require __DIR__ . '/partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Profil utilisateur</h1>
        <p>Consultez ses coordonnées, ses emprunts en cours et son historique.</p>
    </div>

    <div class="split-layout">
        <div class="panel">
            <div class="panel-head">
                <h2><?= e($user->getFullName()) ?></h2>
                <span class="badge <?= badge_class($user->getStatus()) ?>"><?= e(status_label($user->getStatus())) ?></span>
            </div>
            <ul class="info-list">
                <li><strong>Email :</strong> <?= e($user->getEmail()) ?></li>
                <li><strong>Téléphone :</strong> <?= e($user->getPhone()) ?></li>
                <li><strong>Adresse :</strong> <?= e($user->getAddress()) ?></li>
                <li><strong>Rôle :</strong> <?= e(role_label($user->getRole())) ?></li>
                <li><strong>Adhésion :</strong> <?= e(membership_label($user->getMembershipType())) ?></li>
                <li><strong>Date de début :</strong> <?= e(format_date_fr($user->getMembershipPaidAt())) ?></li>
                <li><strong>Date de fin :</strong> <?= e(format_date_fr($user->getMembershipExpiresAt())) ?></li>
                <li><strong>Point de service :</strong> <?= e($user->getMembershipBranchName() ?: '-') ?></li>
            </ul>
        </div>

        <div class="panel">
            <h2>Résumé</h2>
            <ul class="info-list">
                <li><strong>Emprunts en cours :</strong> <?= e((string) count($currentBorrowings)) ?></li>
                <li><strong>Emprunts passés :</strong> <?= e((string) count($previousBorrowings)) ?></li>
            </ul>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-head section-head-small">
        <h2>Gérer l'adhésion</h2>
        <p>Attribuez une formule mensuelle ou annuelle et fixez la date d'expiration.</p>
    </div>

    <form class="panel form-stack" method="post" action="<?= url('admin-user-view', ['id' => $user->getId()]) ?>" data-membership-form>
        <input type="hidden" name="action" value="membership_save">
        <label>Formule
            <select class="form-control" name="membership_type" required data-membership-type>
                <option value="none" <?= $user->getMembershipType() === 'none' ? 'selected' : '' ?>>Sans adhésion</option>
                <option value="monthly" <?= $user->getMembershipType() === 'monthly' ? 'selected' : '' ?>>Mensuelle</option>
                <option value="yearly" <?= $user->getMembershipType() === 'yearly' ? 'selected' : '' ?>>Annuelle</option>
            </select>
        </label>
        <label>Date de début
            <input class="form-control" type="date" name="membership_paid_at" value="<?= e($user->getMembershipPaidAt() ?? '') ?>" data-membership-start>
        </label>
        <label>Date de fin
            <input class="form-control" type="date" name="membership_expires_at" value="<?= e($user->getMembershipExpiresAt() ?? '') ?>" data-membership-end>
        </label>
        <label>Point de service de paiement
            <select class="form-control" name="membership_branch_id">
                <option value="">Choisir un point de service</option>
                <?php foreach ($branches as $branch): ?>
                    <option value="<?= e((string) $branch->getId()) ?>" <?= (int) $user->getMembershipBranchId() === (int) $branch->getId() ? 'selected' : '' ?>>
                        <?= e($branch->getNom()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn btn-primary" type="submit">Enregistrer l'adhésion</button>
    </form>
</section>

<section class="section">
    <div class="section-head section-head-small">
        <h2>Emprunts en cours</h2>
    </div>

    <div class="table-tools" data-table-tools data-table-target="currentBorrowingsTable">
        <input class="form-control" type="search" placeholder="Rechercher un emprunt" data-table-search>
        <select class="form-control" data-table-sort>
            <option value="">Trier par défaut</option>
            <option value="ref:desc">Réf. décroissante</option>
            <option value="book:asc">Livre A-Z</option>
            <option value="branch:asc">Bibliothèque A-Z</option>
            <option value="return:asc">Retour croissant</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="currentBorrowingsTable">
            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>Livre</th>
                    <th>Bibliothèque</th>
                    <th>Début</th>
                    <th>Retour</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($currentBorrowings as $borrow): ?>
                    <tr
                        data-search="<?= e(strtolower('#' . $borrow->getId() . ' ' . $borrow->getLivreTitre() . ' ' . $borrow->getBibliothequeNom() . ' ' . status_label($borrow->getStatus()))) ?>"
                        data-sort-ref="<?= e((string) $borrow->getId()) ?>"
                        data-sort-book="<?= e(strtolower($borrow->getLivreTitre())) ?>"
                        data-sort-branch="<?= e(strtolower($borrow->getBibliothequeNom())) ?>"
                        data-sort-return="<?= e($borrow->getReturnDate()) ?>"
                    >
                        <td>#<?= e((string) $borrow->getId()) ?></td>
                        <td><?= e($borrow->getLivreTitre()) ?></td>
                        <td><?= e($borrow->getBibliothequeNom()) ?></td>
                        <td><?= e(format_date_fr($borrow->getBorrowDate())) ?></td>
                        <td><?= e(format_date_fr($borrow->getReturnDate())) ?></td>
                        <td><span class="badge <?= badge_class($borrow->getStatus()) ?>"><?= e(status_label($borrow->getStatus())) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="section">
    <div class="section-head section-head-small">
        <h2>Historique</h2>
    </div>

    <div class="table-tools" data-table-tools data-table-target="previousBorrowingsTable">
        <input class="form-control" type="search" placeholder="Rechercher dans l'historique" data-table-search>
        <select class="form-control" data-table-sort>
            <option value="">Trier par défaut</option>
            <option value="ref:desc">Réf. décroissante</option>
            <option value="book:asc">Livre A-Z</option>
            <option value="branch:asc">Bibliothèque A-Z</option>
            <option value="return:desc">Retour décroissant</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="previousBorrowingsTable">
            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>Livre</th>
                    <th>Bibliothèque</th>
                    <th>Début</th>
                    <th>Retour</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($previousBorrowings as $borrow): ?>
                    <tr
                        data-search="<?= e(strtolower('#' . $borrow->getId() . ' ' . $borrow->getLivreTitre() . ' ' . $borrow->getBibliothequeNom() . ' ' . status_label($borrow->getStatus()))) ?>"
                        data-sort-ref="<?= e((string) $borrow->getId()) ?>"
                        data-sort-book="<?= e(strtolower($borrow->getLivreTitre())) ?>"
                        data-sort-branch="<?= e(strtolower($borrow->getBibliothequeNom())) ?>"
                        data-sort-return="<?= e($borrow->getReturnDate()) ?>"
                    >
                        <td>#<?= e((string) $borrow->getId()) ?></td>
                        <td><?= e($borrow->getLivreTitre()) ?></td>
                        <td><?= e($borrow->getBibliothequeNom()) ?></td>
                        <td><?= e(format_date_fr($borrow->getBorrowDate())) ?></td>
                        <td><?= e(format_date_fr($borrow->getReturnDate())) ?></td>
                        <td><span class="badge <?= badge_class($borrow->getStatus()) ?>"><?= e(status_label($borrow->getStatus())) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
