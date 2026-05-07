<section class="hero">
    <div class="hero-content">
        <span class="eyebrow">Maison des Livres</span>
        <h1>Des livres inspirants, des espaces accueillants et un service simple pour vos lectures.</h1>
        <p>Découvrez une maison dédiée à la lecture, avec un catalogue soigné, des points de service pratiques et un accompagnement fluide pour vos emprunts.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<?= url('books') ?>">Découvrir le catalogue</a>
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

<section class="section">
    <div class="section-head">
        <h2>Suggestions du moment</h2>
        <p>Quelques ouvrages choisis pour commencer votre prochaine lecture.</p>
    </div>

    <div class="grid cards-3">
        <?php foreach ($livres as $livre): ?>
            <article class="card book-card">
                <img src="<?= e($livre->getCouverture() ?: 'assets/images/book-placeholder.svg') ?>" alt="<?= e($livre->getTitre()) ?>" class="book-cover">
                <div class="card-body">
                    <span class="tag"><?= e($livre->getCategorie()) ?></span>
                    <h3><?= e($livre->getTitre()) ?></h3>
                    <p><?= e($livre->getAuteur()) ?> · <?= e((string) $livre->getAnneePublication()) ?></p>
                    <p class="muted"><?= e($livre->getBibliothequeNom() ?? 'Point de service non défini') ?></p>
                    <a class="btn btn-link" href="<?= url('book', ['id' => $livre->getId()]) ?>">Détails</a>
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
                    <h3><?= e($bibliotheque['nom']) ?></h3>
                    <p><?= e($bibliotheque['adresse']) ?>, <?= e($bibliotheque['ville']) ?></p>
                    <p><?= e($bibliotheque['telephone']) ?></p>
                    <p><?= e((string) ($bibliotheque['book_count'] ?? 0)) ?> ouvrage(s)</p>
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
