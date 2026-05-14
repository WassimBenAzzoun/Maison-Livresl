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

$model = new Bibliotheque();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
    $branch = $model->find($id);
    if (!$branch) {
        flash_set('danger', 'Bibliothèque introuvable.');
        redirect_page('admin-branches');
    }
} else {
    $branch = new Bibliotheque();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => trim($_POST['nom'] ?? ''),
        'adresse' => trim($_POST['adresse'] ?? ''),
        'ville' => trim($_POST['ville'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'latitude' => (float) ($_POST['latitude'] ?? 0),
        'longitude' => (float) ($_POST['longitude'] ?? 0),
    ];

    if ($data['nom'] === '' || $data['ville'] === '') {
        flash_set('warning', 'Le nom et la ville sont obligatoires.');
    } elseif ($id > 0) {
        $model->update($id, $data);
        flash_set('success', 'Bibliothèque mise à jour.');
    } else {
        $model->create($data);
        flash_set('success', 'Bibliothèque ajoutée.');
    }

    redirect_page('admin-branches');
}

$pageTitle = $id ? 'Maison des Livres | Modifier un point de service' : 'Maison des Livres | Ajouter un point de service';
$activePage = 'admin-branches';
require __DIR__ . '/partials/header.php';
?>

<?php $isEdit = !empty($branch->getId()); ?>
<section class="section">
    <div class="section-head">
        <h1><?= $isEdit ? 'Modifier le point de service' : 'Ajouter un point de service' ?></h1>
        <p>Complétez les informations du lieu pour l'affichage sur la carte.</p>
    </div>

    <form class="panel form-stack" method="post" data-branch-form>
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

<?php require __DIR__ . '/partials/footer.php'; ?>
