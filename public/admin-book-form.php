<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Bibliotheque.php';
require_once __DIR__ . '/../app/models/Livre.php';

require_admin_page();

const MAX_COVER_UPLOAD_SIZE = 5242880;

function handle_cover_upload(?array $file): string|false|null
{
    if (!$file || !isset($file['error'])) {
        return null;
    }

    if ((int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        flash_set('warning', 'Le téléversement de la couverture a échoué.');
        return false;
    }

    if ((int) ($file['size'] ?? 0) > MAX_COVER_UPLOAD_SIZE) {
        flash_set('warning', 'L\'image de couverture ne doit pas dépasser 5 Mo.');
        return false;
    }

    $tmpName = $file['tmp_name'] ?? '';
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        flash_set('warning', 'Fichier de couverture invalide.');
        return false;
    }

    $mimeType = mime_content_type($tmpName) ?: '';
    $allowedExtensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!isset($allowedExtensions[$mimeType])) {
        flash_set('warning', 'Formats acceptés : JPG, PNG, WEBP ou GIF.');
        return false;
    }

    $uploadDir = __DIR__ . '/assets/uploads/books';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        flash_set('warning', 'Impossible de créer le dossier des couvertures.');
        return false;
    }

    $filename = sprintf('book-%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(4)), $allowedExtensions[$mimeType]);
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        flash_set('warning', 'Impossible d\'enregistrer l\'image de couverture.');
        return false;
    }

    return 'assets/uploads/books/' . $filename;
}

function save_library_stocks(Livre $model, int $livreId, array $libraryStocks): void
{
    $model->deleteStocksByLivreId($livreId);

    foreach ($libraryStocks as $libraryStock) {
        $bibliothequeId = (int) ($libraryStock['bibliotheque_id'] ?? 0);
        $totalExemplaires = (int) ($libraryStock['total_exemplaires'] ?? 0);
        $availableExemplaires = (int) ($libraryStock['available_exemplaires'] ?? 0);

        if ($bibliothequeId > 0 && $totalExemplaires > 0) {
            $model->addStock($livreId, $bibliothequeId, $totalExemplaires, min($availableExemplaires, $totalExemplaires));
        }
    }
}

$model = new Livre();
$bibliotheques = (new Bibliotheque())->all();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
    $livre = $model->find($id);
    if (!$livre) {
        flash_set('danger', 'Livre introuvable.');
        redirect_page('admin-books');
    }
} else {
    $livre = new Livre();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre' => trim($_POST['titre'] ?? ''),
        'auteur' => trim($_POST['auteur'] ?? ''),
        'categorie' => trim($_POST['categorie'] ?? ''),
        'annee_publication' => (int) ($_POST['annee_publication'] ?? 0),
        'description' => trim($_POST['description'] ?? ''),
        'couverture' => trim($_POST['couverture'] ?? ''),
    ];
    $libraryStocks = $_POST['library_stocks'] ?? [];
    $uploadedCover = handle_cover_upload($_FILES['cover_upload'] ?? null);

    if ($data['titre'] === '' || $data['auteur'] === '' || $data['categorie'] === '') {
        flash_set('warning', 'Veuillez remplir les champs obligatoires.');
    } elseif ($uploadedCover === false) {
        redirect_page('admin-book-form', $id > 0 ? ['id' => $id] : []);
    } elseif ($id > 0) {
        if (is_string($uploadedCover)) {
            $data['couverture'] = $uploadedCover;
        }
        $model->update($id, $data);
        save_library_stocks($model, $id, $libraryStocks);
        flash_set('success', 'Livre mis à jour avec succès.');
    } else {
        if (is_string($uploadedCover)) {
            $data['couverture'] = $uploadedCover;
        }
        $newId = $model->create($data);
        save_library_stocks($model, $newId, $libraryStocks);
        flash_set('success', 'Livre ajouté avec succès.');
    }

    redirect_page('admin-books');
}

$pageTitle = $id ? 'Maison des Livres | Modifier un livre' : 'Maison des Livres | Ajouter un livre';
$activePage = 'admin-books';
require __DIR__ . '/partials/header.php';
?>

<?php $isEdit = !empty($livre->getId()); ?>
<?php $stocks = $isEdit ? $livre->getStocks() : []; ?>
<section class="section">
    <div class="section-head">
        <h1><?= $isEdit ? 'Modifier le livre' : 'Ajouter un livre' ?></h1>
        <p>Formulaire simple avec stockage PDO et inventaire par bibliothèque.</p>
    </div>

    <form class="panel form-stack" method="post" enctype="multipart/form-data">
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
            <input class="form-control" type="text" name="couverture" value="<?= e($livre->getCouverture()) ?>" placeholder="https://... ou assets/uploads/books/mon-livre.jpg">
        </label>
        <label>Importer une image de couverture
            <input class="form-control" type="file" name="cover_upload" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">
            <small class="muted">L'import remplace le lien si les deux sont fournis.</small>
        </label>
        <?php if ($livre->getCouverture() !== ''): ?>
            <div class="detail-card">
                <p class="muted">Couverture actuelle</p>
                <img src="<?= e($livre->getCouverture()) ?>" alt="<?= e($livre->getTitre() ?: 'Couverture du livre') ?>" class="detail-cover">
            </div>
        <?php endif; ?>
        <div class="section-head section-head-small">
            <h2>Stock par bibliothèque</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Bibliothèque</th>
                        <th>Exemplaires totaux</th>
                        <th>Exemplaires disponibles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bibliotheques as $branch): ?>
                        <?php
                            $stock = null;
                            foreach ($stocks as $row) {
                                if ((int) ($row['bibliotheque_id'] ?? 0) === (int) $branch->getId()) {
                                    $stock = $row;
                                    break;
                                }
                            }
                        ?>
                        <tr>
                            <td><?= e($branch->getNom()) ?></td>
                            <td><input class="form-control" type="number" min="0" name="library_stocks[<?= e((string) $branch->getId()) ?>][total_exemplaires]" value="<?= e((string) ($stock['total_exemplaires'] ?? 0)) ?>"></td>
                            <td><input class="form-control" type="number" min="0" name="library_stocks[<?= e((string) $branch->getId()) ?>][available_exemplaires]" value="<?= e((string) ($stock['available_exemplaires'] ?? 0)) ?>"></td>
                            <input type="hidden" name="library_stocks[<?= e((string) $branch->getId()) ?>][bibliotheque_id]" value="<?= e((string) $branch->getId()) ?>">
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Mettre à jour' : 'Créer le livre' ?></button>
    </form>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
