<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/Bibliotheque.php';
require_once __DIR__ . '/../app/models/Livre.php';

$pageTitle = 'Maison des Livres | Accueil';
$activePage = 'home';

$bibliotheques = (new Bibliotheque())->allWithBookCounts();
$livres = (new Livre())->featured(3);

require __DIR__ . '/partials/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <span class="eyebrow">Maison des Livres</span>
        <h1>Des livres inspirants, des espaces accueillants et un service simple pour vos lectures.</h1>
        <p>Découvrez une maison dédiée à la lecture, avec un catalogue soigné, des points de service pratiques et un accompagnement fluide pour vos emprunts.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="/guest/books.php">Découvrir le catalogue</a>
            <a class="btn btn-secondary" href="#carte-bibliotheques">Voir nos points de service</a>
        </div>
    </div>
    <div class="hero-panel">
        <h2>Pourquoi venir chez nous</h2>
        <ul class="feature-list">
            <li>Explorer une sélection de livres variée</li>
            <li>Trouver rapidement une lecture selon vos envies</li>
            <li>Réserver un ouvrage depuis votre espace personnel</li>
            <li>Retrouver facilement nos points de service</li>
        </ul>
    </div>
</section>

<section class="section quote-section">
    <div class="section-head">
        <h2>Citation du jour</h2>
        <p>Une pause inspirante pour accompagner votre visite à la bibliothèque.</p>
    </div>

    <article class="quote-card" data-home-quote aria-live="polite" aria-busy="true">
        <div class="quote-card-top">
            <span class="tag">Quotes API</span>
            <button class="btn btn-secondary btn-sm" type="button" data-home-quote-refresh>Nouvelle citation</button>
        </div>

        <p class="quote-status" data-home-quote-status>Chargement de la citation...</p>
        <blockquote class="quote-text" data-home-quote-text>« Une citation arrive dans un instant. »</blockquote>
        <div class="quote-footer">
            <span class="quote-author" data-home-quote-author>Maison des Livres</span>
        </div>
    </article>
</section>

<section class="section">
    <div class="section-head">
        <h2>Suggestions du moment</h2>
        <p>Quelques ouvrages choisis pour commencer votre prochaine lecture.</p>
    </div>

    <div class="grid cards-3">
        <?php foreach ($livres as $livre): ?>
            <?php
                $coverPath = $livre->getCouverture() ?: 'assets/images/book-placeholder.svg';
                $coverPath = preg_match('#^https?://#', $coverPath) ? $coverPath : '/' . ltrim($coverPath, '/');
            ?>
            <article class="card book-card">
                <img src="<?= htmlspecialchars($coverPath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($livre->getTitre(), ENT_QUOTES, 'UTF-8') ?>" class="book-cover">
                <div class="card-body">
                    <span class="tag"><?= htmlspecialchars($livre->getCategorie(), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($livre->getTitre(), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($livre->getAuteur(), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) $livre->getAnneePublication(), ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="muted"><?= htmlspecialchars($livre->getBibliothequeNom() ?? 'Point de service non défini', ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="btn btn-link" href="/guest/book.php?id=<?= rawurlencode((string) ($livre->getId())) ?>">Détails</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section" id="carte-bibliotheques">
    <div class="section-head">
        <h2>Nos points de service</h2>
        <p>Retrouvez facilement chaque lieu de retrait et de consultation sur la carte.</p>
    </div>

    <div class="map-grid">
        <div id="homeMap" class="map"></div>
        <div class="panel">
            <?php foreach ($bibliotheques as $bibliotheque): ?>
                <article class="mini-location">
                    <h3><?= htmlspecialchars($bibliotheque['nom'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($bibliotheque['adresse'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($bibliotheque['ville'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><?= htmlspecialchars($bibliotheque['telephone'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><?= htmlspecialchars((string) ($bibliotheque['book_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?> ouvrage(s)</p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
const bibliotheques = <?= json_encode($bibliotheques, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
document.addEventListener('DOMContentLoaded', function () {
    if (window.LibraryApp) {
        window.LibraryApp.initBranchesMap('homeMap', bibliotheques);
    }
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
