<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/Bibliotheque.php';
require_once __DIR__ . '/../../app/models/Livre.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$livre = (new Livre())->find($id);

if (!$livre) {
    flash_set('danger', 'Livre introuvable.');
    header('Location: /guest/books.php');
    exit;
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
require __DIR__ . '/../partials/header.php';
?>

<section class="section">
    <div class="detail-layout">
        <div class="detail-card">
            <?php
                $coverPath = $livre->getCouverture() ?: 'assets/images/book-placeholder.svg';
                $coverPath = preg_match('#^https?://#', $coverPath) ? $coverPath : '/' . ltrim($coverPath, '/');
            ?>
            <img src="<?= htmlspecialchars($coverPath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($livre->getTitre(), ENT_QUOTES, 'UTF-8') ?>" class="detail-cover">
        </div>

        <div class="detail-card">
            <span class="tag"><?= htmlspecialchars($livre->getCategorie(), ENT_QUOTES, 'UTF-8') ?></span>
            <h1><?= htmlspecialchars($livre->getTitre(), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="lead"><?= htmlspecialchars($livre->getAuteur(), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) $livre->getAnneePublication(), ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars($livre->getDescription(), ENT_QUOTES, 'UTF-8') ?></p>

            <ul class="info-list">
                <li><strong>Total exemplaires :</strong> <?= htmlspecialchars((string) $livre->getTotalExemplaires(), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Disponibles :</strong> <?= htmlspecialchars((string) $livre->getAvailableExemplaires(), ENT_QUOTES, 'UTF-8') ?></li>
            </ul>

            <div class="section-head section-head-small">
                <h2>Disponibilité par bibliothèque</h2>
            </div>

            <div class="table-tools" data-table-tools data-table-target="bookStocksTable">
                <input class="form-control" type="search" placeholder="Rechercher une bibliothèque" data-table-search>
                <select class="form-control" data-table-sort>
                    <option value="">Trier par défaut</option>
                    <option value="branch:asc">Bibliothèque A-Z</option>
                    <option value="branch:desc">Bibliothèque Z-A</option>
                    <option value="available:desc">Disponibles décroissant</option>
                    <option value="available:asc">Disponibles croissant</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="data-table" id="bookStocksTable">
                    <thead>
                        <tr>
                            <th>Bibliothèque</th>
                            <th>Adresse</th>
                            <th>Disponibles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stocks as $stock): ?>
                            <tr
                                data-search="<?= htmlspecialchars(strtolower(($stock['bibliotheque_nom'] ?? '-') . ' ' . ($stock['bibliotheque_adresse'] ?? '-')), ENT_QUOTES, 'UTF-8') ?>"
                                data-sort-branch="<?= htmlspecialchars(strtolower($stock['bibliotheque_nom'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>"
                                data-sort-available="<?= htmlspecialchars((string) ($stock['available_exemplaires'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <td><a class="table-link" href="/guest/books.php?branch_id=<?= htmlspecialchars((string) $stock['bibliotheque_id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($stock['bibliotheque_nom'] ?? '-', ENT_QUOTES, 'UTF-8') ?></a></td>
                                <td><?= htmlspecialchars($stock['bibliotheque_adresse'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($stock['available_exemplaires'] ?? 0), ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars((string) ($stock['total_exemplaires'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-actions">
                <a class="btn btn-primary" href="/user/borrow.php?id=<?= rawurlencode((string) ($livre->getId())) ?>">Emprunter ce livre</a>
                <a class="btn btn-secondary" href="/guest/books.php">Retour au catalogue</a>
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

<?php require __DIR__ . '/../partials/footer.php'; ?>


