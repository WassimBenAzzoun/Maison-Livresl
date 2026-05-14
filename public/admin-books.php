<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Livre.php';

require_admin_page();

$pageTitle = 'Maison des Livres | Gestion des livres';
$activePage = 'admin-books';
$livres = (new Livre())->all();

require __DIR__ . '/partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Catalogue</h1>
        <p>Créer, modifier et supprimer les ouvrages proposés.</p>
        <a class="btn btn-primary" href="<?= url('admin-book-form') ?>">Ajouter un ouvrage</a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Catégorie</th>
                    <th>Total</th>
                    <th>Disponibles</th>
                    <th>Bibliothèques</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($livres as $livre): ?>
                    <tr>
                        <td><?= e($livre->getTitre()) ?></td>
                        <td><?= e($livre->getAuteur()) ?></td>
                        <td><?= e($livre->getCategorie()) ?></td>
                        <td><?= e((string) $livre->getTotalExemplaires()) ?></td>
                        <td><?= e((string) $livre->getAvailableExemplaires()) ?></td>
                        <td><?= e($livre->getBibliothequeNom() ?? '-') ?></td>
                        <td class="table-actions">
                            <a class="btn btn-sm btn-secondary" href="<?= url('admin-book-form', ['id' => $livre->getId()]) ?>">Modifier</a>
                            <a class="btn btn-sm btn-danger" href="<?= url('admin-book-delete', ['id' => $livre->getId()]) ?>" onclick="return confirm('Supprimer ce livre ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
