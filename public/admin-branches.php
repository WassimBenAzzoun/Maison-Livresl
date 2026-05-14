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

$pageTitle = 'Maison des Livres | Points de service';
$activePage = 'admin-branches';
$branches = (new Bibliotheque())->all();

require __DIR__ . '/partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Points de service</h1>
        <p>Ajoutez ou modifiez les lieux d’accueil et leurs coordonnées.</p>
        <a class="btn btn-primary" href="<?= 'admin-branch-form.php' ?>">Ajouter un point de service</a>
    </div>

    <div class="table-tools" data-table-tools data-table-target="branchesTable">
        <input class="form-control" type="search" placeholder="Rechercher un point de service" data-table-search>
        <select class="form-control" data-table-sort>
            <option value="">Trier par défaut</option>
            <option value="name:asc">Nom A-Z</option>
            <option value="name:desc">Nom Z-A</option>
            <option value="city:asc">Ville A-Z</option>
            <option value="books:desc">Plus de livres</option>
            <option value="borrowings:desc">Plus d'emprunts actifs</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="branchesTable">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Ville</th>
                    <th>Téléphone</th>
                    <th>Livres</th>
                    <th>Emprunts actifs</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($branches as $branch): ?>
                    <tr
                        data-search="<?= htmlspecialchars(strtolower($branch->getNom() . ' ' . $branch->getVille() . ' ' . $branch->getAdresse() . ' ' . $branch->getTelephone()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-name="<?= htmlspecialchars(strtolower($branch->getNom()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-city="<?= htmlspecialchars(strtolower($branch->getVille()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-books="<?= htmlspecialchars((string) $branch->getBookCount(), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-borrowings="<?= htmlspecialchars((string) $branch->getCurrentBorrowingsCount(), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <td><a class="table-link" href="<?= 'admin-branch-view.php?id=' . rawurlencode((string) ($branch->getId())) ?>"><?= htmlspecialchars($branch->getNom(), ENT_QUOTES, 'UTF-8') ?></a></td>
                        <td><?= htmlspecialchars($branch->getVille(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($branch->getTelephone(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $branch->getBookCount(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $branch->getCurrentBorrowingsCount(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $branch->getLatitude(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $branch->getLongitude(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="table-actions">
                            <a class="btn btn-sm btn-secondary" href="<?= 'admin-branch-form.php?id=' . rawurlencode((string) ($branch->getId())) ?>">Modifier</a>
                            <a class="btn btn-sm btn-danger" href="<?= 'admin-branch-delete.php?id=' . rawurlencode((string) ($branch->getId())) ?>" onclick="return confirm('Supprimer cette bibliothèque ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
