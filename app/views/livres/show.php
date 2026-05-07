<section class="section">
    <div class="detail-layout">
        <div class="detail-card">
            <img src="<?= e($livre->getCouverture() ?: 'assets/images/book-placeholder.svg') ?>" alt="<?= e($livre->getTitre()) ?>" class="detail-cover">
        </div>

        <div class="detail-card">
            <span class="tag"><?= e($livre->getCategorie()) ?></span>
            <h1><?= e($livre->getTitre()) ?></h1>
            <p class="lead"><?= e($livre->getAuteur()) ?> · <?= e((string) $livre->getAnneePublication()) ?></p>
            <p><?= e($livre->getDescription()) ?></p>

            <ul class="info-list">
                <li><strong>Total exemplaires :</strong> <?= e((string) $livre->getTotalExemplaires()) ?></li>
                <li><strong>Disponibles :</strong> <?= e((string) $livre->getAvailableExemplaires()) ?></li>
                <li><strong>Bibliothèque :</strong> <?php if ($bibliotheque): ?><a class="table-link" href="<?= url('books', ['branch_id' => $bibliotheque->getId()]) ?>"><?= e($bibliotheque->getNom()) ?></a><?php else: ?><?= e('Non définie') ?><?php endif; ?></li>
                <li><strong>Adresse :</strong> <?= e($bibliotheque ? $bibliotheque->getAdresse() : '-') ?></li>
                <li><strong>Ville :</strong> <?= e($bibliotheque ? $bibliotheque->getVille() : '-') ?></li>
            </ul>

            <div class="card-actions">
                <a class="btn btn-primary" href="<?= url('borrow', ['id' => $livre->getId()]) ?>">Emprunter ce livre</a>
                <a class="btn btn-secondary" href="<?= url('books') ?>">Retour au catalogue</a>
            </div>
        </div>
    </div>
</section>

<?php if ($bibliotheque): ?>
    <section class="section">
        <div class="section-head">
            <h2>Localisation de la bibliothèque</h2>
            <p>Carte de la succursale liée au livre.</p>
        </div>
        <div id="bookMap" class="map"></div>
    </section>

    <script>
    const bibliotheque = <?= json_encode([
        'nom' => $bibliotheque->getNom(),
        'adresse' => $bibliotheque->getAdresse(),
        'ville' => $bibliotheque->getVille(),
        'latitude' => $bibliotheque->getLatitude(),
        'longitude' => $bibliotheque->getLongitude(),
        'telephone' => $bibliotheque->getTelephone(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    document.addEventListener('DOMContentLoaded', function () {
        if (window.LibraryApp) {
            window.LibraryApp.initSingleBranchMap('bookMap', bibliotheque);
        }
    });
    </script>
<?php endif; ?>
