<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/Emprunt.php';

require_login_page();

$sessionUser = $_SESSION['user'] ?? [];
$borrowings = (new Emprunt())->byUser((int) ($sessionUser['id'] ?? 0));
$pageTitle = 'Mes emprunts';
$activePage = 'my-borrowings';
require __DIR__ . '/../partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Mes emprunts</h1>
        <p>L'historique de vos demandes et de leurs statuts.</p>
    </div>

    <div class="table-tools" data-table-tools data-table-target="userBorrowingsTable">
        <input class="form-control" type="search" placeholder="Rechercher un emprunt" data-table-search>
        <select class="form-control" data-table-sort>
            <option value="">Trier par défaut</option>
            <option value="ref:desc">Réf. décroissante</option>
            <option value="book:asc">Livre A-Z</option>
            <option value="branch:asc">Bibliothèque A-Z</option>
            <option value="status:asc">Statut A-Z</option>
            <option value="return:asc">Retour croissant</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="userBorrowingsTable">
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
                <?php foreach ($borrowings as $borrow): ?>
                    <tr
                        data-search="<?= htmlspecialchars(strtolower('#' . $borrow->getId() . ' ' . $borrow->getLivreTitre() . ' ' . $borrow->getBibliothequeNom() . ' ' . status_label($borrow->getStatus())), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-ref="<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-book="<?= htmlspecialchars(strtolower($borrow->getLivreTitre()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-branch="<?= htmlspecialchars(strtolower($borrow->getBibliothequeNom()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-status="<?= htmlspecialchars(strtolower(status_label($borrow->getStatus())), ENT_QUOTES, 'UTF-8') ?>"
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

<?php require __DIR__ . '/../partials/footer.php'; ?>


