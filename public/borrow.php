<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Bibliotheque.php';
require_once __DIR__ . '/../app/models/Emprunt.php';
require_once __DIR__ . '/../app/models/Livre.php';
require_once __DIR__ . '/../app/models/User.php';

require_login_page();

$sessionUser = $_SESSION['user'] ?? [];
$userModel = new User();
$livreModel = new Livre();
$bibliothequeModel = new Bibliotheque();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$livre = $livreModel->find($id);
if (!$livre) {
    flash_set('danger', 'Livre introuvable.');
    header('Location: books.php');
    exit;
}

$userRecord = $userModel->findWithMembership((int) ($sessionUser['id'] ?? 0));
if (!$userRecord) {
    flash_set('danger', 'Utilisateur introuvable.');
    header('Location: logout.php');
    exit;
}

if (!$userRecord->hasActiveMembership()) {
    flash_set('warning', 'Une adhésion valide est requise avant de pouvoir emprunter.');
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bibliothequeId = (int) ($_POST['bibliotheque_id'] ?? 0);
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $borrowDate = $_POST['borrow_date'] ?? '';
    $returnDate = $_POST['return_date'] ?? '';
    $stock = $bibliothequeId > 0 ? $livreModel->stockByBibliothequeAndLivre($bibliothequeId, $livre->getId() ?? 0) : null;

    if ($bibliothequeId <= 0 || !$stock) {
        flash_set('warning', 'Veuillez choisir un point de service valide.');
    } elseif ($fullName === '' || $email === '' || $phone === '' || $borrowDate === '' || $returnDate === '') {
        flash_set('warning', 'Veuillez remplir tous les champs.');
    } elseif (strtotime($returnDate) <= strtotime($borrowDate)) {
        flash_set('warning', 'La date de retour doit être postérieure à la date d\'emprunt.');
    } elseif ((int) ($stock['available_exemplaires'] ?? 0) <= 0) {
        flash_set('danger', 'Ce livre n\'est plus disponible.');
    } else {
        $bibliotheque = $bibliothequeModel->find($bibliothequeId);
        $empruntModel = new Emprunt();
        $empruntId = $empruntModel->create([
            'user_id' => $sessionUser['id'],
            'livre_id' => $livre->getId(),
            'bibliotheque_id' => $bibliothequeId,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'borrow_date' => $borrowDate,
            'return_date' => $returnDate,
            'status' => 'pending',
            'livre_titre' => $livre->getTitre(),
            'livre_categorie' => $livre->getCategorie(),
            'bibliotheque_nom' => $bibliotheque ? $bibliotheque->getNom() : '',
        ]);

        $livreModel->decrementStock($bibliothequeId, (int) $livre->getId());

        flash_set('success', 'Votre demande d\'emprunt a été enregistrée.');
        header('Location: confirmation.php?id=' . rawurlencode((string) $empruntId));
        exit;
    }
}

$pageTitle = 'Maison des Livres | Emprunter un livre';
$activePage = 'books';
$stocks = $livre->getStocks();
$user = $userRecord;
require __DIR__ . '/partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Demande d'emprunt</h1>
        <p>Remplissez le formulaire ci-dessous pour enregistrer votre demande.</p>
    </div>

    <div class="split-layout">
        <div class="panel">
            <img src="<?= htmlspecialchars($livre->getCouverture() ?: 'assets/images/book-placeholder.svg', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($livre->getTitre(), ENT_QUOTES, 'UTF-8') ?>" class="book-cover">
            <h2><?= htmlspecialchars($livre->getTitre(), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($livre->getAuteur(), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($livre->getCategorie(), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="muted"><?= htmlspecialchars((string) $livre->getAvailableExemplaires(), ENT_QUOTES, 'UTF-8') ?> exemplaire(s) disponible(s) dans toutes les bibliothèques</p>
            <div class="table-responsive mt-24">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bibliothèque</th>
                            <th>Disponibles</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stocks as $stock): ?>
                            <tr>
                                <td><?= htmlspecialchars($stock['bibliotheque_nom'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($stock['available_exemplaires'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($stock['total_exemplaires'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($membership)): ?>
                <div class="alert alert-info">
                    <strong>Adhésion :</strong> <?= htmlspecialchars(membership_label($membership->getMembershipType()), ENT_QUOTES, 'UTF-8') ?> jusqu'au <?= htmlspecialchars(format_date_fr($membership->getMembershipExpiresAt()), ENT_QUOTES, 'UTF-8') ?>.
                </div>
            <?php endif; ?>
        </div>

        <form class="panel form-stack" method="post" data-borrow-form>
            <label>Bibliothèque
                <select class="form-control" name="bibliotheque_id" required>
                    <option value="">Choisir une bibliothèque</option>
                    <?php foreach ($stocks as $stock): ?>
                        <?php if ((int) ($stock['available_exemplaires'] ?? 0) > 0): ?>
                            <option value="<?= htmlspecialchars((string) $stock['bibliotheque_id'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($stock['bibliotheque_nom'] ?? '-', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) ($stock['available_exemplaires'] ?? 0), ENT_QUOTES, 'UTF-8') ?> dispo)
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Nom complet
                <input class="form-control" type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? ($user->getFullName() ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </label>
            <label>Email
                <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? ($user->getEmail() ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </label>
            <label>Téléphone
                <input class="form-control" type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? ($user->getPhone() ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </label>
            <label>Date d'emprunt
                <input class="form-control" type="date" name="borrow_date" data-borrow-start required>
            </label>
            <label>Date de retour
                <input class="form-control" type="date" name="return_date" data-borrow-end required>
            </label>
            <div class="hint">
                Durée estimée : <strong data-borrow-duration>-</strong>
            </div>
            <button class="btn btn-primary" type="submit">Confirmer la demande</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
