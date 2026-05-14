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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$livre = (new Livre())->find($id);

if (!$livre) {
    flash_set('danger', 'Livre introuvable.');
    redirect_page('books');
}

$stocks = $livre->getStocks();
$branches = [];
foreach ($stocks as $stock) {
    if (isset($stock['bibliotheque_id'])) {
        $branches[] = [
            'nom' => $stock['bibliotheque_nom'] ?? '',
            'adresse' => $stock['bibliotheque_adresse'] ?? '',
            'ville' => $stock['bibliotheque_ville'] ?? '',
            'latitude' => $stock['bibliotheque_latitude'] ?? 0,
            'longitude' => $stock['bibliotheque_longitude'] ?? 0,
        ];
    }
}

$pageTitle = 'Maison des Livres | ' . $livre->getTitre();
$activePage = 'books';
require __DIR__ . '/partials/header.php';
?>

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
            </ul>

            <div class="section-head section-head-small">
                <h2>Disponibilité par bibliothèque</h2>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bibliothèque</th>
                            <th>Adresse</th>
                            <th>Disponibles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stocks as $stock): ?>
                            <tr>
                                <td><a class="table-link" href="<?= url('books', ['branch_id' => $stock['bibliotheque_id']]) ?>"><?= e($stock['bibliotheque_nom'] ?? '-') ?></a></td>
                                <td><?= e($stock['bibliotheque_adresse'] ?? '-') ?></td>
                                <td><?= e((string) ($stock['available_exemplaires'] ?? 0)) ?>/<?= e((string) ($stock['total_exemplaires'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-actions">
                <a class="btn btn-primary" href="<?= url('borrow', ['id' => $livre->getId()]) ?>">Emprunter ce livre</a>
                <a class="btn btn-secondary" href="<?= url('books') ?>">Retour au catalogue</a>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($branches)): ?>
    <section class="section">
        <div class="section-head">
            <h2>Localisation des bibliothèques</h2>
            <p>Carte des points de service qui possèdent ce livre.</p>
        </div>
        <div id="bookMap" class="map"></div>
    </section>

    <script>
    const branches = <?= json_encode($branches, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    document.addEventListener('DOMContentLoaded', function () {
        if (window.LibraryApp) {
            window.LibraryApp.initBranchesMap('bookMap', branches);
        }
    });
    </script>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
