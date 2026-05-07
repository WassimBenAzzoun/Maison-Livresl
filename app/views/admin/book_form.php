<?php $isEdit = !empty($livre->getId()); ?>
<section class="section">
    <div class="section-head">
        <h1><?= $isEdit ? 'Modifier le livre' : 'Ajouter un livre' ?></h1>
        <p>Formulaire simple avec stockage PDO.</p>
    </div>

    <form class="panel form-stack" method="post" data-validate>
        <label>Bibliothèque
            <select class="form-control" name="bibliotheque_id">
                <option value="">Choisir une bibliothèque</option>
                <?php foreach ($bibliotheques as $branch): ?>
                    <option value="<?= e((string) $branch->getId()) ?>" <?= (int) $livre->getBibliothequeId() === (int) $branch->getId() ? 'selected' : '' ?>>
                        <?= e($branch->getNom()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Titre
            <input class="form-control" type="text" name="titre" value="<?= e($livre->getTitre()) ?>" required>
        </label>
        <label>Auteur
            <input class="form-control" type="text" name="auteur" value="<?= e($livre->getAuteur()) ?>" required>
        </label>
        <label>Catégorie
            <input class="form-control" type="text" name="categorie" value="<?= e($livre->getCategorie()) ?>" required>
        </label>
        <label>Année de publication
            <input class="form-control" type="number" name="annee_publication" value="<?= e((string) $livre->getAnneePublication()) ?>">
        </label>
        <label>Description
            <textarea class="form-control" name="description" rows="5"><?= e($livre->getDescription()) ?></textarea>
        </label>
        <label>Lien de couverture
            <input class="form-control" type="text" name="couverture" value="<?= e($livre->getCouverture()) ?>" placeholder="assets/images/book-placeholder.svg">
        </label>
        <div class="grid cards-2">
            <label>Exemplaires totaux
                <input class="form-control" type="number" name="total_exemplaires" value="<?= e((string) $livre->getTotalExemplaires()) ?>">
            </label>
            <label>Exemplaires disponibles
                <input class="form-control" type="number" name="available_exemplaires" value="<?= e((string) $livre->getAvailableExemplaires()) ?>">
            </label>
        </div>
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Mettre à jour' : 'Créer le livre' ?></button>
    </form>
</section>
