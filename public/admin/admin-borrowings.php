<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/Emprunt.php';
require_once __DIR__ . '/../../app/models/Livre.php';

require_admin_page();

$empruntModel = new Emprunt();
$livreModel = new Livre();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $emprunt = $empruntModel->find($id);

    if ($emprunt) {
        if ($action === 'confirm' && $emprunt->getStatus() === 'pending') {
            $livre = $livreModel->find((int) $emprunt->getLivreId());
            $stock = $livre && $emprunt->getBibliothequeId() ? $livreModel->stockByBibliothequeAndLivre((int) $emprunt->getBibliothequeId(), (int) $emprunt->getLivreId()) : null;

            if ($livre && $stock && (int) ($stock['available_exemplaires'] ?? 0) > 0) {
                $empruntModel->updateStatus($id, 'confirmed');
                $livreModel->decrementStock((int) $emprunt->getBibliothequeId(), (int) $emprunt->getLivreId());
                flash_set('success', 'Emprunt confirmé.');
            } else {
                flash_set('warning', 'Aucun exemplaire disponible pour confirmer cet emprunt.');
            }
        } elseif ($action === 'cancel' && in_array($emprunt->getStatus(), ['pending', 'confirmed'], true)) {
            if ($emprunt->getStatus() === 'confirmed' && $emprunt->getLivreId()) {
                $livreModel->incrementStock((int) $emprunt->getBibliothequeId(), (int) $emprunt->getLivreId());
            }
            $empruntModel->updateStatus($id, 'cancelled');
            flash_set('success', 'Emprunt annulé.');
        } elseif ($action === 'returned' && $emprunt->getStatus() === 'confirmed') {
            $empruntModel->updateStatus($id, 'returned');
            if ($emprunt->getLivreId()) {
                $livreModel->incrementStock((int) $emprunt->getBibliothequeId(), (int) $emprunt->getLivreId());
            }
            flash_set('success', 'Livre marqué comme retourné.');
        } else {
            flash_set('info', 'Aucune modification appliquée à cet emprunt.');
        }
    }

    header('Location: /admin/admin-borrowings.php');
    exit;
}

$pageTitle = 'Maison des Livres | Gestion des emprunts';
$activePage = 'admin-borrowings';
$emprunts = $empruntModel->allWithRelations();
require __DIR__ . '/../partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Gestion des emprunts</h1>
        <p>Confirmer, annuler ou marquer comme retourné.</p>
    </div>

    <div class="table-tools" data-table-tools data-table-target="borrowingsTable">
        <input class="form-control" type="search" placeholder="Rechercher un emprunt" data-table-search>
        <select class="form-control" data-table-sort>
            <option value="">Trier par défaut</option>
            <option value="ref:desc">Réf. décroissante</option>
            <option value="user:asc">Utilisateur A-Z</option>
            <option value="book:asc">Livre A-Z</option>
            <option value="branch:asc">Bibliothèque A-Z</option>
            <option value="status:asc">Statut A-Z</option>
            <option value="return:asc">Retour croissant</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="borrowingsTable">
            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>Utilisateur</th>
                    <th>Livre</th>
                    <th>Bibliothèque</th>
                    <th>Début</th>
                    <th>Retour</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emprunts as $borrow): ?>
                    <tr
                        data-search="<?= htmlspecialchars(strtolower('#' . $borrow->getId() . ' ' . $borrow->getUserName() . ' ' . $borrow->getLivreTitre() . ' ' . $borrow->getBibliothequeNom() . ' ' . status_label($borrow->getStatus())), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-ref="<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-user="<?= htmlspecialchars(strtolower($borrow->getUserName() ?: $borrow->getFullName()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-book="<?= htmlspecialchars(strtolower($borrow->getLivreTitre()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-branch="<?= htmlspecialchars(strtolower($borrow->getBibliothequeNom()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-status="<?= htmlspecialchars(strtolower(status_label($borrow->getStatus())), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-return="<?= htmlspecialchars($borrow->getReturnDate(), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <td>#<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if ($borrow->getUserId()): ?>
                                <a class="table-link" href="/admin/admin-user-view.php?id=<?= rawurlencode((string) ($borrow->getUserId())) ?>"><?= htmlspecialchars($borrow->getUserName() ?: $borrow->getFullName(), ENT_QUOTES, 'UTF-8') ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($borrow->getUserName() ?: $borrow->getFullName(), ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($borrow->getLivreTitre(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if ($borrow->getBibliothequeId()): ?>
                                <a class="table-link" href="/admin/admin-branch-view.php?id=<?= rawurlencode((string) ($borrow->getBibliothequeId())) ?>"><?= htmlspecialchars($borrow->getBibliothequeNom(), ENT_QUOTES, 'UTF-8') ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($borrow->getBibliothequeNom(), ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(format_date_fr($borrow->getBorrowDate()), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(format_date_fr($borrow->getReturnDate()), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge <?= badge_class($borrow->getStatus()) ?>"><?= htmlspecialchars(status_label($borrow->getStatus()), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td class="table-actions">
                            <form method="post" class="inline-form">
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="confirm">
                                <button class="btn btn-sm btn-primary" type="submit">Confirmer</button>
                            </form>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="returned">
                                <button class="btn btn-sm btn-secondary" type="submit">Retourné</button>
                            </form>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $borrow->getId(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button class="btn btn-sm btn-danger" type="submit">Annuler</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>


