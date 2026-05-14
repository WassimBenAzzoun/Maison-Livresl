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
        <a class="btn btn-primary" href="<?= url('admin-branch-form') ?>">Ajouter un point de service</a>
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
                        data-search="<?= e(strtolower($branch->getNom() . ' ' . $branch->getVille() . ' ' . $branch->getAdresse() . ' ' . $branch->getTelephone())) ?>"
                        data-sort-name="<?= e(strtolower($branch->getNom())) ?>"
                        data-sort-city="<?= e(strtolower($branch->getVille())) ?>"
                        data-sort-books="<?= e((string) $branch->getBookCount()) ?>"
                        data-sort-borrowings="<?= e((string) $branch->getCurrentBorrowingsCount()) ?>"
                    >
                        <td><a class="table-link" href="<?= url('admin-branch-view', ['id' => $branch->getId()]) ?>"><?= e($branch->getNom()) ?></a></td>
                        <td><?= e($branch->getVille()) ?></td>
                        <td><?= e($branch->getTelephone()) ?></td>
                        <td><?= e((string) $branch->getBookCount()) ?></td>
                        <td><?= e((string) $branch->getCurrentBorrowingsCount()) ?></td>
                        <td><?= e((string) $branch->getLatitude()) ?></td>
                        <td><?= e((string) $branch->getLongitude()) ?></td>
                        <td class="table-actions">
                            <a class="btn btn-sm btn-secondary" href="<?= url('admin-branch-form', ['id' => $branch->getId()]) ?>">Modifier</a>
                            <a class="btn btn-sm btn-danger" href="<?= url('admin-branch-delete', ['id' => $branch->getId()]) ?>" onclick="return confirm('Supprimer cette bibliothèque ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
