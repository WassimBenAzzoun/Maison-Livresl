<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/Bibliotheque.php';
require_once __DIR__ . '/../../app/models/Livre.php';

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

    $uploadDir = __DIR__ . '/../../assets/uploads/books';
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

    return '/assets/uploads/books/' . $filename;
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
        header('Location: /admin/admin-books.php');
        exit;
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
        $query = $id > 0 ? '?id=' . rawurlencode((string) $id) : '';
        header('Location: /admin/admin-book-form.php' . $query);
        exit;
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

    header('Location: /admin/admin-books.php');
    exit;
}

$pageTitle = $id ? 'Maison des Livres | Modifier un livre' : 'Maison des Livres | Ajouter un livre';
$activePage = 'admin-books';
require __DIR__ . '/../partials/header.php';
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
            <input class="form-control" type="text" name="titre" value="<?= htmlspecialchars($livre->getTitre(), ENT_QUOTES, 'UTF-8') ?>" required>
        </label>
        <label>Auteur
            <input class="form-control" type="text" name="auteur" value="<?= htmlspecialchars($livre->getAuteur(), ENT_QUOTES, 'UTF-8') ?>" required>
        </label>
        <label>Catégorie
            <input class="form-control" type="text" name="categorie" value="<?= htmlspecialchars($livre->getCategorie(), ENT_QUOTES, 'UTF-8') ?>" required>
        </label>
        <label>Année de publication
            <input class="form-control" type="number" name="annee_publication" value="<?= htmlspecialchars((string) $livre->getAnneePublication(), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Description
            <textarea class="form-control" name="description" rows="5"><?= htmlspecialchars($livre->getDescription(), ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>
        <label>Lien de couverture
            <input class="form-control" type="text" name="couverture" value="<?= htmlspecialchars($livre->getCouverture(), ENT_QUOTES, 'UTF-8') ?>" placeholder="https://... ou assets/uploads/books/mon-livre.jpg">
        </label>
        <label>Importer une image de couverture
            <input class="form-control" type="file" name="cover_upload" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">
            <small class="muted">L'import remplace le lien si les deux sont fournis.</small>
        </label>
        <?php if ($livre->getCouverture() !== ''): ?>
            <div class="detail-card">
                <p class="muted">Couverture actuelle</p>
                <?php
                    $coverPath = $livre->getCouverture() ?: 'assets/images/book-placeholder.svg';
                    $coverPath = preg_match('#^https?://#', $coverPath) ? $coverPath : '/' . ltrim($coverPath, '/');
                ?>
                <img src="<?= htmlspecialchars($coverPath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($livre->getTitre() ?: 'Couverture du livre', ENT_QUOTES, 'UTF-8') ?>" class="detail-cover">
            </div>
        <?php endif; ?>
        <div class="section-head section-head-small">
            <h2>Stock par bibliothèque</h2>
        </div>
        <div class="table-tools" data-table-tools data-table-target="bookFormStocksTable">
            <input class="form-control" type="search" placeholder="Rechercher un point de service" data-table-search>
            <select class="form-control" data-table-sort>
                <option value="">Trier par défaut</option>
                <option value="branch:asc">Bibliothèque A-Z</option>
                <option value="branch:desc">Bibliothèque Z-A</option>
                <option value="total:desc">Plus d'exemplaires</option>
                <option value="available:desc">Plus disponibles</option>
            </select>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="bookFormStocksTable">
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
                        <tr
                            data-search="<?= htmlspecialchars(strtolower($branch->getNom()), ENT_QUOTES, 'UTF-8') ?>"
                            data-sort-branch="<?= htmlspecialchars(strtolower($branch->getNom()), ENT_QUOTES, 'UTF-8') ?>"
                            data-sort-total="<?= htmlspecialchars((string) ($stock['total_exemplaires'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"
                            data-sort-available="<?= htmlspecialchars((string) ($stock['available_exemplaires'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"
                        >
                            <td><?= htmlspecialchars($branch->getNom(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><input class="form-control" type="number" min="0" name="library_stocks[<?= htmlspecialchars((string) $branch->getId(), ENT_QUOTES, 'UTF-8') ?>][total_exemplaires]" value="<?= htmlspecialchars((string) ($stock['total_exemplaires'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input class="form-control" type="number" min="0" name="library_stocks[<?= htmlspecialchars((string) $branch->getId(), ENT_QUOTES, 'UTF-8') ?>][available_exemplaires]" value="<?= htmlspecialchars((string) ($stock['available_exemplaires'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <input type="hidden" name="library_stocks[<?= htmlspecialchars((string) $branch->getId(), ENT_QUOTES, 'UTF-8') ?>][bibliotheque_id]" value="<?= htmlspecialchars((string) $branch->getId(), ENT_QUOTES, 'UTF-8') ?>">
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Mettre à jour' : 'Créer le livre' ?></button>
    </form>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>


