<?php $isEdit = !empty($branch->getId()); ?>
<section class="section">
    <div class="section-head">
        <h1><?= $isEdit ? 'Modifier le point de service' : 'Ajouter un point de service' ?></h1>
        <p>Complétez les informations du lieu pour l'affichage sur la carte.</p>
    </div>

    <form class="panel form-stack" method="post" data-validate>
        <label>Nom du point de service
            <input class="form-control" type="text" name="nom" value="<?= e($branch->getNom()) ?>" required>
        </label>
        <label>Adresse
            <input class="form-control" type="text" name="adresse" value="<?= e($branch->getAdresse()) ?>" required>
        </label>
        <label>Ville
            <input class="form-control" type="text" name="ville" value="<?= e($branch->getVille()) ?>" required>
        </label>
        <label>Téléphone
            <input class="form-control" type="text" name="telephone" value="<?= e($branch->getTelephone()) ?>">
        </label>
        <label>Description
            <textarea class="form-control" name="description" rows="5"><?= e($branch->getDescription()) ?></textarea>
        </label>
        <div class="grid cards-2">
            <label>Latitude
                <input class="form-control" type="text" name="latitude" value="<?= e((string) $branch->getLatitude()) ?>" required>
            </label>
            <label>Longitude
                <input class="form-control" type="text" name="longitude" value="<?= e((string) $branch->getLongitude()) ?>" required>
            </label>
        </div>
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Mettre à jour' : 'Créer le point de service' ?></button>
    </form>
</section>
