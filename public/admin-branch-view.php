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
    redirect_page('admin-branches');
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
                <h2><?= e($branch->getNom()) ?></h2>
                <span class="badge badge-info"><?= e($branch->getVille()) ?></span>
            </div>
            <ul class="info-list">
                <li><strong>Adresse :</strong> <?= e($branch->getAdresse()) ?></li>
                <li><strong>Téléphone :</strong> <?= e($branch->getTelephone() ?: '-') ?></li>
                <li><strong>Livres :</strong> <?= e((string) $branch->getBookCount()) ?></li>
                <li><strong>Emprunts actifs :</strong> <?= e((string) $branch->getCurrentBorrowingsCount()) ?></li>
            </ul>
            <p><?= e($branch->getDescription() ?: 'Aucune description renseignée.') ?></p>
            <div class="card-actions">
                <a class="btn btn-primary" href="<?= url('admin-branch-form', ['id' => $branch->getId()]) ?>">Modifier</a>
                <a class="btn btn-secondary" href="<?= url('admin-branches') ?>">Retour à la liste</a>
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
                        data-search="<?= e(strtolower($book->getTitre() . ' ' . $book->getAuteur() . ' ' . $book->getCategorie())) ?>"
                        data-sort-title="<?= e(strtolower($book->getTitre())) ?>"
                        data-sort-author="<?= e(strtolower($book->getAuteur())) ?>"
                        data-sort-available="<?= e((string) $book->getAvailableExemplaires()) ?>"
                    >
                        <td><a class="table-link" href="<?= url('book', ['id' => $book->getId()]) ?>"><?= e($book->getTitre()) ?></a></td>
                        <td><?= e($book->getAuteur()) ?></td>
                        <td><?= e($book->getCategorie()) ?></td>
                        <td><?= e((string) $book->getAnneePublication()) ?></td>
                        <td><span class="badge <?= $available ? 'badge-success' : 'badge-danger' ?>"><?= e((string) $book->getAvailableExemplaires()) ?></span></td>
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
                        data-search="<?= e(strtolower('#' . $borrow->getId() . ' ' . $borrow->getUserName() . ' ' . $borrow->getLivreTitre() . ' ' . status_label($borrow->getStatus()))) ?>"
                        data-sort-ref="<?= e((string) $borrow->getId()) ?>"
                        data-sort-user="<?= e(strtolower($borrow->getUserName() ?: $borrow->getFullName())) ?>"
                        data-sort-book="<?= e(strtolower($borrow->getLivreTitre())) ?>"
                        data-sort-return="<?= e($borrow->getReturnDate()) ?>"
                    >
                        <td>#<?= e((string) $borrow->getId()) ?></td>
                        <td><?= e($borrow->getUserName() ?: $borrow->getFullName()) ?></td>
                        <td><?= e($borrow->getLivreTitre()) ?></td>
                        <td><?= e(format_date_fr($borrow->getBorrowDate())) ?></td>
                        <td><?= e(format_date_fr($borrow->getReturnDate())) ?></td>
                        <td><span class="badge <?= badge_class($borrow->getStatus()) ?>"><?= e(status_label($borrow->getStatus())) ?></span></td>
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
