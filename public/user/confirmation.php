<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/Emprunt.php';

require_login_page();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$emprunt = (new Emprunt())->find($id);
if (!$emprunt) {
    flash_set('danger', 'Confirmation introuvable.');
    header('Location: /user/my-borrowings.php');
    exit;
}

$pageTitle = 'Maison des Livres | Confirmation';
$activePage = 'my-borrowings';
require __DIR__ . '/../partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Confirmation d'emprunt</h1>
        <p>Votre demande a bien été enregistrée. Le statut initial est <strong>En attente</strong>.</p>
    </div>

    <div class="panel confirmation-panel">
        <ul class="info-list">
            <li><strong>Référence :</strong> #<?= htmlspecialchars((string) $emprunt->getId(), ENT_QUOTES, 'UTF-8') ?></li>
            <li><strong>Livre :</strong> <?= htmlspecialchars($emprunt->getLivreTitre(), ENT_QUOTES, 'UTF-8') ?></li>
            <li><strong>Bibliothèque :</strong> <?= htmlspecialchars($emprunt->getBibliothequeNom(), ENT_QUOTES, 'UTF-8') ?></li>
            <li><strong>Nom :</strong> <?= htmlspecialchars($emprunt->getFullName(), ENT_QUOTES, 'UTF-8') ?></li>
            <li><strong>Email :</strong> <?= htmlspecialchars($emprunt->getEmail(), ENT_QUOTES, 'UTF-8') ?></li>
            <li><strong>Téléphone :</strong> <?= htmlspecialchars($emprunt->getPhone(), ENT_QUOTES, 'UTF-8') ?></li>
            <li><strong>Début :</strong> <?= htmlspecialchars(format_date_fr($emprunt->getBorrowDate()), ENT_QUOTES, 'UTF-8') ?></li>
            <li><strong>Retour :</strong> <?= htmlspecialchars(format_date_fr($emprunt->getReturnDate()), ENT_QUOTES, 'UTF-8') ?></li>
            <li><strong>Statut :</strong> <span class="badge <?= badge_class($emprunt->getStatus()) ?>"><?= htmlspecialchars(status_label($emprunt->getStatus()), ENT_QUOTES, 'UTF-8') ?></span></li>
        </ul>

        <div class="card-actions">
            <a class="btn btn-primary" href="/user/my-borrowings.php">Voir mes emprunts</a>
            <a class="btn btn-secondary" href="/guest/books.php">Retour au catalogue</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>


