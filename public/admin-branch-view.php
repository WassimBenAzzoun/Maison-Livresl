<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Bibliotheque.php';

require_admin_page();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$model = new Bibliotheque();
$branch = $model->find($id);

if (!$branch) {
    flash_set('danger', 'Point de service introuvable.');
    header('Location: admin-branches.php');
    exit;
}

$books = $model->booksById($id);
$currentBorrowings = $model->currentBorrowingsById($id);
$pageTitle = 'Maison des Livres | Fiche point de service';
$activePage = 'admin-branches';
require __DIR__ . '/partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Fiche point de service</h1>
        <p>Vue complète avec les livres rattachés et les emprunts en cours.</p>
    </div>

    <div class="split-layout">
        <div class="panel">
            <div class="panel-head">
                <h2><?= htmlspecialchars($branch->getNom(), ENT_QUOTES, 'UTF-8') ?></h2>
                <span class="badge badge-info"><?= htmlspecialchars($branch->getVille(), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <ul class="info-list">
                <li><strong>Adresse :</strong> <?= htmlspecialchars($branch->getAdresse(), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Téléphone :</strong> <?= htmlspecialchars($branch->getTelephone() ?: '-', ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Livres :</strong> <?= htmlspecialchars((string) $branch->getBookCount(), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Emprunts actifs :</strong> <?= htmlspecialchars((string) $branch->getCurrentBorrowingsCount(), ENT_QUOTES, 'UTF-8') ?></li>
            </ul>
            <p><?= htmlspecialchars($branch->getDescription() ?: 'Aucune description renseignée.', ENT_QUOTES, 'UTF-8') ?></p>
            <div class="card-actions">
                <a class="btn btn-primary" href="<?= 'admin-branch-form.php?id=' . rawurlencode((string) ($branch->getId())) ?>">Modifier</a>
                <a class="btn btn-secondary" href="<?= 'admin-branches.php' ?>">Retour à la liste</a>
            </div>
        </div>

        <div class="panel">
            <h2>Localisation</h2>
            <div id="branchMap" class="map"></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-head section-head-small">
        <h2>Livres du point de service</h2>
    </div>

    <div class="table-tools" data-table-tools data-table-target="branchBooksTable">
        <input class="form-control" type="search" placeholder="Rechercher un livre" data-table-search>
        <select class="form-control" data-table-sort>
            <option value="">Trier par défaut</option>
            <option value="title:asc">Titre A-Z</option>
            <option value="title:desc">Titre Z-A</option>
            <option value="author:asc">Auteur A-Z</option>
            <option value="available:desc">Disponibilité décroissante</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="branchBooksTable">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Catégorie</th>
                    <th>Année</th>
                    <th>Disponibles</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <?php $available = $book->getAvailableExemplaires() > 0; ?>
                    <tr
                        data-search="<?= htmlspecialchars(strtolower($book->getTitre() . ' ' . $book->getAuteur() . ' ' . $book->getCategorie()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-title="<?= htmlspecialchars(strtolower($book->getTitre()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-author="<?= htmlspecialchars(strtolower($book->getAuteur()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-available="<?= htmlspecialchars((string) $book->getAvailableExemplaires(), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <td><a class="table-link" href="<?= 'book.php?id=' . rawurlencode((string) ($book->getId())) ?>"><?= htmlspecialchars($book->getTitre(), ENT_QUOTES, 'UTF-8') ?></a></td>
                        <td><?= htmlspecialchars($book->getAuteur(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($book->getCategorie(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $book->getAnneePublication(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge <?= $available ? 'badge-success' : 'badge-danger' ?>"><?= htmlspecialchars((string) $book->getAvailableExemplaires(), ENT_QUOTES, 'UTF-8') ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="section">
    <div class="section-head section-head-small">
        <h2>Emprunts en cours</h2>
    </div>

    <div class="table-tools" data-table-tools data-table-target="branchBorrowingsTable">
        <input class="form-control" type="search" placeholder="Rechercher un emprunt" data-table-search>
        <select class="form-control" data-table-sort>
            <option value="">Trier par défaut</option>
            <option value="ref:desc">Réf. décroissante</option>
            <option value="user:asc">Utilisateur A-Z</option>
            <option value="book:asc">Livre A-Z</option>
            <option value="return:asc">Retour croissant</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="branchBorrowingsTable">
            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>Utilisateur</th>
                    <th>Livre</th>
                    <th>Début</th>
                    <th>Retour</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($currentBorrowings as $borrow): ?>
                    <tr
                        data-search="<?= htmlspecialchars(strtolower('#' . $borrow->getId() . ' ' . $borrow->getUserName() . ' ' . $borrow->getLivreTitre() . ' ' . status_label($borrow->getStatus())), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-ref="<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-user="<?= htmlspecialchars(strtolower($borrow->getUserName() ?: $borrow->getFullName()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-book="<?= htmlspecialchars(strtolower($borrow->getLivreTitre()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-return="<?= htmlspecialchars($borrow->getReturnDate(), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <td>#<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($borrow->getUserName() ?: $borrow->getFullName(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($borrow->getLivreTitre(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(format_date_fr($borrow->getBorrowDate()), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(format_date_fr($borrow->getReturnDate()), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge <?= badge_class($borrow->getStatus()) ?>"><?= htmlspecialchars(status_label($borrow->getStatus()), ENT_QUOTES, 'UTF-8') ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
const bibliotheque = <?= json_encode([
    'nom' => $branch->getNom(),
    'adresse' => $branch->getAdresse(),
    'ville' => $branch->getVille(),
    'latitude' => $branch->getLatitude(),
    'longitude' => $branch->getLongitude(),
    'telephone' => $branch->getTelephone(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
document.addEventListener('DOMContentLoaded', function () {
    if (window.LibraryApp) {
        window.LibraryApp.initSingleBranchMap('branchMap', bibliotheque);
    }
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
