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
                    <th>Disponibles</th>
                    <th>Bibliothèque</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($livres as $livre): ?>
                    <tr>
                        <td><?= e($livre->getTitre()) ?></td>
                        <td><?= e($livre->getAuteur()) ?></td>
                        <td><?= e($livre->getCategorie()) ?></td>
                        <td><?= e((string) $livre->getAvailableExemplaires()) ?>/<?= e((string) $livre->getTotalExemplaires()) ?></td>
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
