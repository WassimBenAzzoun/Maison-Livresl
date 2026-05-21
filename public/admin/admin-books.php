<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/Livre.php';

require_admin_page();

$pageTitle = 'Maison des Livres | Gestion des livres';
$activePage = 'admin-books';
$livres = (new Livre())->all();

require __DIR__ . '/../partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Catalogue</h1>
        <p>Créer, modifier et supprimer les ouvrages proposés.</p>
        <a class="btn btn-primary" href="/admin/admin-book-form.php">Ajouter un ouvrage</a>
    </div>

    <div class="table-tools" data-table-tools data-table-target="adminBooksTable">
        <input class="form-control" type="search" placeholder="Rechercher un livre" data-table-search>
        <select class="form-control" data-table-sort>
            <option value="">Trier par défaut</option>
            <option value="title:asc">Titre A-Z</option>
            <option value="title:desc">Titre Z-A</option>
            <option value="author:asc">Auteur A-Z</option>
            <option value="category:asc">Catégorie A-Z</option>
            <option value="total:desc">Plus d'exemplaires</option>
            <option value="available:desc">Plus disponibles</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="adminBooksTable">
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
                    <tr
                        data-search="<?= htmlspecialchars(strtolower($livre->getTitre() . ' ' . $livre->getAuteur() . ' ' . $livre->getCategorie() . ' ' . ($livre->getBibliothequeNom() ?? '-')), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-title="<?= htmlspecialchars(strtolower($livre->getTitre()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-author="<?= htmlspecialchars(strtolower($livre->getAuteur()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-category="<?= htmlspecialchars(strtolower($livre->getCategorie()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-total="<?= htmlspecialchars((string) $livre->getTotalExemplaires(), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-available="<?= htmlspecialchars((string) $livre->getAvailableExemplaires(), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <td><?= htmlspecialchars($livre->getTitre(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($livre->getAuteur(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($livre->getCategorie(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $livre->getTotalExemplaires(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $livre->getAvailableExemplaires(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($livre->getBibliothequeNom() ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="table-actions">
                            <a class="btn btn-sm btn-secondary" href="/admin/admin-book-form.php?id=<?= rawurlencode((string) ($livre->getId())) ?>">Modifier</a>
                            <a class="btn btn-sm btn-danger" href="/admin/admin-book-delete.php?id=<?= rawurlencode((string) ($livre->getId())) ?>" onclick="return confirm('Supprimer ce livre ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>


