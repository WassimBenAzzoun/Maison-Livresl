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
require_once __DIR__ . '/../app/models/Livre.php';
require_once __DIR__ . '/../app/models/User.php';

require_admin_page();

$livreModel = new Livre();
$empruntModel = new Emprunt();
$userModel = new User();
$bibliothequeModel = new Bibliotheque();

$pageTitle = 'Maison des Livres | Aperçu de gestion';
$activePage = 'admin-dashboard';
$stats = [
    'total_books' => $livreModel->countTotal(),
    'available_books' => $livreModel->countAvailable(),
    'total_borrowings' => $empruntModel->countTotal(),
    'pending_borrowings' => $empruntModel->countByStatus('pending'),
    'confirmed_borrowings' => $empruntModel->countByStatus('confirmed'),
    'returned_borrowings' => $empruntModel->countByStatus('returned'),
    'total_users' => $userModel->countTotal(),
    'total_branches' => count($bibliothequeModel->all()),
];
$latestBorrowings = $empruntModel->latest(5);

require __DIR__ . '/partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Aperçu de gestion</h1>
        <p>Suivi global des livres, réservations, comptes et points de service.</p>
    </div>

    <div class="grid cards-4">
        <article class="stat-card"><span>Livres</span><strong><?= e((string) $stats['total_books']) ?></strong></article>
        <article class="stat-card"><span>Disponibles</span><strong><?= e((string) $stats['available_books']) ?></strong></article>
        <article class="stat-card"><span>Réservations</span><strong><?= e((string) $stats['total_borrowings']) ?></strong></article>
        <article class="stat-card"><span>Comptes</span><strong><?= e((string) $stats['total_users']) ?></strong></article>
    </div>

    <div class="grid cards-4 mt-24">
        <article class="stat-card soft"><span>En attente</span><strong><?= e((string) $stats['pending_borrowings']) ?></strong></article>
        <article class="stat-card soft"><span>Confirmées</span><strong><?= e((string) $stats['confirmed_borrowings']) ?></strong></article>
        <article class="stat-card soft"><span>Retournées</span><strong><?= e((string) $stats['returned_borrowings']) ?></strong></article>
        <article class="stat-card soft"><span>Points de service</span><strong><?= e((string) $stats['total_branches']) ?></strong></article>
    </div>

    <div class="section-head section-head-small">
        <h2>Dernières réservations</h2>
    </div>

    <div class="table-tools" data-table-tools data-table-target="dashboardBorrowingsTable">
        <input class="form-control" type="search" placeholder="Rechercher une réservation" data-table-search>
        <select class="form-control" data-table-sort>
            <option value="">Trier par défaut</option>
            <option value="ref:desc">Réf. décroissante</option>
            <option value="book:asc">Livre A-Z</option>
            <option value="status:asc">Statut A-Z</option>
            <option value="return:asc">Retour croissant</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="dashboardBorrowingsTable">
            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>Livre</th>
                    <th>Statut</th>
                    <th>Retour</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestBorrowings as $borrow): ?>
                    <tr
                        data-search="<?= e(strtolower('#' . $borrow->getId() . ' ' . $borrow->getLivreTitre() . ' ' . status_label($borrow->getStatus()))) ?>"
                        data-sort-ref="<?= e((string) $borrow->getId()) ?>"
                        data-sort-book="<?= e(strtolower($borrow->getLivreTitre())) ?>"
                        data-sort-status="<?= e(strtolower(status_label($borrow->getStatus()))) ?>"
                        data-sort-return="<?= e($borrow->getReturnDate()) ?>"
                    >
                        <td>#<?= e((string) $borrow->getId()) ?></td>
                        <td><?= e($borrow->getLivreTitre()) ?></td>
                        <td><span class="badge <?= badge_class($borrow->getStatus()) ?>"><?= e(status_label($borrow->getStatus())) ?></span></td>
                        <td><?= e(format_date_fr($borrow->getReturnDate())) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
