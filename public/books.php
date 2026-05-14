<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Bibliotheque.php';
require_once __DIR__ . '/../app/models/Livre.php';

$pageTitle = 'Maison des Livres | Catalogue';
$activePage = 'books';

$selectedBranchId = isset($_GET['branch_id']) ? (int) $_GET['branch_id'] : 0;
$livreModel = new Livre();
$livres = $selectedBranchId > 0 ? $livreModel->findByBranch($selectedBranchId) : $livreModel->all();
$branches = (new Bibliotheque())->all();

require __DIR__ . '/partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Catalogue des livres</h1>
        <p>Filtrez instantanément par titre, auteur, catégorie, bibliothèque et disponibilité.</p>
    </div>

    <div class="filter-bar">
        <input type="text" class="form-control" data-filter-title placeholder="Rechercher par titre">
        <input type="text" class="form-control" data-filter-author placeholder="Rechercher par auteur">
        <input type="text" class="form-control" data-filter-category placeholder="Rechercher par catégorie">
        <select class="form-control" data-filter-branch>
            <option value="">Toutes les bibliothèques</option>
            <?php foreach ($branches as $branch): ?>
                <option value="<?= htmlspecialchars((string) $branch->getId(), ENT_QUOTES, 'UTF-8') ?>" <?= (int) $selectedBranchId === (int) $branch->getId() ? 'selected' : '' ?>>
                    <?= htmlspecialchars($branch->getNom(), ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="form-control" data-filter-availability>
            <option value="">Toutes les disponibilités</option>
            <option value="available">Disponible</option>
            <option value="unavailable">Indisponible</option>
        </select>
        <select class="form-control" data-book-sort>
            <option value="">Ordre par défaut</option>
            <option value="title:asc">Titre A-Z</option>
            <option value="title:desc">Titre Z-A</option>
            <option value="author:asc">Auteur A-Z</option>
            <option value="branch:asc">Bibliothèque A-Z</option>
            <option value="availability:desc">Disponibilité d'abord</option>
        </select>
    </div>

    <div class="hint mt-24">
        Affichage des livres par point de service. Un même livre peut apparaître dans plusieurs bibliothèques.
    </div>

    <div class="grid cards-3" data-books-grid>
        <?php foreach ($livres as $livre): ?>
            <?php $available = $livre->getAvailableExemplaires() > 0; ?>
            <article
                class="card book-card"
                data-book-card
                data-title="<?= htmlspecialchars(strtolower($livre->getTitre()), ENT_QUOTES, 'UTF-8') ?>"
                data-author="<?= htmlspecialchars(strtolower($livre->getAuteur()), ENT_QUOTES, 'UTF-8') ?>"
                data-category="<?= htmlspecialchars(strtolower($livre->getCategorie()), ENT_QUOTES, 'UTF-8') ?>"
                data-branch="<?= htmlspecialchars(implode(',', $livre->getBibliothequeIds()), ENT_QUOTES, 'UTF-8') ?>"
                data-branch-name="<?= htmlspecialchars(strtolower($livre->getBibliothequeNom() ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                data-availability="<?= $available ? 'available' : 'unavailable' ?>"
            >
                <img src="<?= htmlspecialchars($livre->getCouverture() ?: 'assets/images/book-placeholder.svg', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($livre->getTitre(), ENT_QUOTES, 'UTF-8') ?>" class="book-cover">
                <div class="card-body">
                    <div class="card-headline">
                        <span class="tag"><?= htmlspecialchars($livre->getCategorie(), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="badge <?= $available ? 'badge-success' : 'badge-danger' ?>">
                            <?= $available ? 'Disponible' : 'Épuisé' ?>
                        </span>
                    </div>
                    <h3><?= htmlspecialchars($livre->getTitre(), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($livre->getAuteur(), ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="muted"><?= htmlspecialchars((string) $livre->getAnneePublication(), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($livre->getBibliothequeNom() ?? 'Bibliothèque non définie', ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="card-actions">
                        <a class="btn btn-secondary" href="<?= 'book.php?id=' . rawurlencode((string) ($livre->getId())) ?>">Détails</a>
                        <a class="btn btn-primary" href="<?= 'borrow.php?id=' . rawurlencode((string) ($livre->getId())) ?>">Emprunter</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <p class="no-results hidden" data-no-results>Aucun livre ne correspond à votre recherche.</p>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
