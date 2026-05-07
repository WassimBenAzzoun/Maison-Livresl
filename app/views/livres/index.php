<section class="section">
    <div class="section-head">
        <h1>Catalogue des livres</h1>
        <p>Filtrez instantanément par titre, auteur, catégorie, bibliothèque et disponibilité.</p>
    </div>

    <div class="filter-bar">
        <input type="text" class="form-control" data-filter-title placeholder="Rechercher par titre">
        <input type="text" class="form-control" data-filter-author placeholder="Rechercher par auteur">
        <input type="text" class="form-control" data-filter-category placeholder="Rechercher par catégorie">
        <select class="form-control" data-filter-branch>
            <option value="">Toutes les bibliothèques</option>
            <?php foreach ($branches as $branch): ?>
                <option value="<?= e((string) $branch->getId()) ?>" <?= (int) $selectedBranchId === (int) $branch->getId() ? 'selected' : '' ?>>
                    <?= e($branch->getNom()) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="form-control" data-filter-availability>
            <option value="">Toutes les disponibilités</option>
            <option value="available">Disponible</option>
            <option value="unavailable">Indisponible</option>
        </select>
        <select class="form-control" data-book-sort>
            <option value="">Ordre par défaut</option>
            <option value="title:asc">Titre A-Z</option>
            <option value="title:desc">Titre Z-A</option>
            <option value="author:asc">Auteur A-Z</option>
            <option value="branch:asc">Bibliothèque A-Z</option>
            <option value="availability:desc">Disponibilité d'abord</option>
        </select>
    </div>

    <div class="grid cards-3" data-books-grid>
        <?php foreach ($livres as $livre): ?>
            <?php $available = $livre->getAvailableExemplaires() > 0; ?>
            <article
                class="card book-card"
                data-book-card
                data-title="<?= e(strtolower($livre->getTitre())) ?>"
                data-author="<?= e(strtolower($livre->getAuteur())) ?>"
                data-category="<?= e(strtolower($livre->getCategorie())) ?>"
                data-branch="<?= e((string) $livre->getBibliothequeId()) ?>"
                data-branch-name="<?= e(strtolower($livre->getBibliothequeNom() ?? '')) ?>"
                data-availability="<?= $available ? 'available' : 'unavailable' ?>"
            >
                <img src="<?= e($livre->getCouverture() ?: 'assets/images/book-placeholder.svg') ?>" alt="<?= e($livre->getTitre()) ?>" class="book-cover">
                <div class="card-body">
                    <div class="card-headline">
                        <span class="tag"><?= e($livre->getCategorie()) ?></span>
                        <span class="badge <?= $available ? 'badge-success' : 'badge-danger' ?>">
                            <?= $available ? 'Disponible' : 'Épuisé' ?>
                        </span>
                    </div>
                    <h3><?= e($livre->getTitre()) ?></h3>
                    <p><?= e($livre->getAuteur()) ?></p>
                    <p class="muted"><?= e((string) $livre->getAnneePublication()) ?> · <?= e($livre->getBibliothequeNom() ?? 'Bibliothèque non définie') ?></p>
                    <div class="card-actions">
                        <a class="btn btn-secondary" href="<?= url('book', ['id' => $livre->getId()]) ?>">Détails</a>
                        <a class="btn btn-primary" href="<?= url('borrow', ['id' => $livre->getId()]) ?>">Emprunter</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <p class="no-results hidden" data-no-results>Aucun livre ne correspond à votre recherche.</p>
</section>
