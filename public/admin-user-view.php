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
    header('Location: admin-users.php');
    exit;
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

    header('Location: admin-user-view.php?id=' . rawurlencode((string) $id));
    exit;
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
                <h2><?= htmlspecialchars($user->getFullName(), ENT_QUOTES, 'UTF-8') ?></h2>
                <span class="badge <?= badge_class($user->getStatus()) ?>"><?= htmlspecialchars(status_label($user->getStatus()), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <ul class="info-list">
                <li><strong>Email :</strong> <?= htmlspecialchars($user->getEmail(), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Téléphone :</strong> <?= htmlspecialchars($user->getPhone(), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Adresse :</strong> <?= htmlspecialchars($user->getAddress(), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Rôle :</strong> <?= htmlspecialchars(role_label($user->getRole()), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Adhésion :</strong> <?= htmlspecialchars(membership_label($user->getMembershipType()), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Date de début :</strong> <?= htmlspecialchars(format_date_fr($user->getMembershipPaidAt()), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Date de fin :</strong> <?= htmlspecialchars(format_date_fr($user->getMembershipExpiresAt()), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Point de service :</strong> <?= htmlspecialchars($user->getMembershipBranchName() ?: '-', ENT_QUOTES, 'UTF-8') ?></li>
            </ul>
        </div>

        <div class="panel">
            <h2>Résumé</h2>
            <ul class="info-list">
                <li><strong>Emprunts en cours :</strong> <?= htmlspecialchars((string) count($currentBorrowings), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Emprunts passés :</strong> <?= htmlspecialchars((string) count($previousBorrowings), ENT_QUOTES, 'UTF-8') ?></li>
            </ul>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-head section-head-small">
        <h2>Gérer l'adhésion</h2>
        <p>Attribuez une formule mensuelle ou annuelle et fixez la date d'expiration.</p>
    </div>

    <form class="panel form-stack" method="post" action="<?= 'admin-user-view.php?id=' . rawurlencode((string) ($user->getId())) ?>" data-membership-form>
        <input type="hidden" name="action" value="membership_save">
        <label>Formule
            <select class="form-control" name="membership_type" required data-membership-type>
                <option value="none" <?= $user->getMembershipType() === 'none' ? 'selected' : '' ?>>Sans adhésion</option>
                <option value="monthly" <?= $user->getMembershipType() === 'monthly' ? 'selected' : '' ?>>Mensuelle</option>
                <option value="yearly" <?= $user->getMembershipType() === 'yearly' ? 'selected' : '' ?>>Annuelle</option>
            </select>
        </label>
        <label>Date de début
            <input class="form-control" type="date" name="membership_paid_at" value="<?= htmlspecialchars($user->getMembershipPaidAt() ?? '', ENT_QUOTES, 'UTF-8') ?>" data-membership-start>
        </label>
        <label>Date de fin
            <input class="form-control" type="date" name="membership_expires_at" value="<?= htmlspecialchars($user->getMembershipExpiresAt() ?? '', ENT_QUOTES, 'UTF-8') ?>" data-membership-end>
        </label>
        <label>Point de service de paiement
            <select class="form-control" name="membership_branch_id">
                <option value="">Choisir un point de service</option>
                <?php foreach ($branches as $branch): ?>
                    <option value="<?= htmlspecialchars((string) $branch->getId(), ENT_QUOTES, 'UTF-8') ?>" <?= (int) $user->getMembershipBranchId() === (int) $branch->getId() ? 'selected' : '' ?>>
                        <?= htmlspecialchars($branch->getNom(), ENT_QUOTES, 'UTF-8') ?>
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
                        data-search="<?= htmlspecialchars(strtolower('#' . $borrow->getId() . ' ' . $borrow->getLivreTitre() . ' ' . $borrow->getBibliothequeNom() . ' ' . status_label($borrow->getStatus())), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-ref="<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-book="<?= htmlspecialchars(strtolower($borrow->getLivreTitre()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-branch="<?= htmlspecialchars(strtolower($borrow->getBibliothequeNom()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-return="<?= htmlspecialchars($borrow->getReturnDate(), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <td>#<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($borrow->getLivreTitre(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($borrow->getBibliothequeNom(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(format_date_fr($borrow->getBorrowDate()), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(format_date_fr($borrow->getReturnDate()), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge <?= badge_class($borrow->getStatus()) ?>"><?= htmlspecialchars(status_label($borrow->getStatus()), ENT_QUOTES, 'UTF-8') ?></span></td>
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
                        data-search="<?= htmlspecialchars(strtolower('#' . $borrow->getId() . ' ' . $borrow->getLivreTitre() . ' ' . $borrow->getBibliothequeNom() . ' ' . status_label($borrow->getStatus())), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-ref="<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-book="<?= htmlspecialchars(strtolower($borrow->getLivreTitre()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-branch="<?= htmlspecialchars(strtolower($borrow->getBibliothequeNom()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-return="<?= htmlspecialchars($borrow->getReturnDate(), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <td>#<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($borrow->getLivreTitre(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($borrow->getBibliothequeNom(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(format_date_fr($borrow->getBorrowDate()), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(format_date_fr($borrow->getReturnDate()), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge <?= badge_class($borrow->getStatus()) ?>"><?= htmlspecialchars(status_label($borrow->getStatus()), ENT_QUOTES, 'UTF-8') ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
