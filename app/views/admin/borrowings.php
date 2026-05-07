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
                        data-search="<?= e(strtolower('#' . $borrow->getId() . ' ' . $borrow->getUserName() . ' ' . $borrow->getLivreTitre() . ' ' . $borrow->getBibliothequeNom() . ' ' . status_label($borrow->getStatus()))) ?>"
                        data-sort-ref="<?= e((string) $borrow->getId()) ?>"
                        data-sort-user="<?= e(strtolower($borrow->getUserName() ?: $borrow->getFullName())) ?>"
                        data-sort-book="<?= e(strtolower($borrow->getLivreTitre())) ?>"
                        data-sort-branch="<?= e(strtolower($borrow->getBibliothequeNom())) ?>"
                        data-sort-status="<?= e(strtolower(status_label($borrow->getStatus()))) ?>"
                        data-sort-return="<?= e($borrow->getReturnDate()) ?>"
                    >
                        <td>#<?= e((string) $borrow->getId()) ?></td>
                        <td>
                            <?php if ($borrow->getUserId()): ?>
                                <a class="table-link" href="<?= url('admin-user-view', ['id' => $borrow->getUserId()]) ?>"><?= e($borrow->getUserName() ?: $borrow->getFullName()) ?></a>
                            <?php else: ?>
                                <?= e($borrow->getUserName() ?: $borrow->getFullName()) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= e($borrow->getLivreTitre()) ?></td>
                        <td>
                            <?php if ($borrow->getBibliothequeId()): ?>
                                <a class="table-link" href="<?= url('admin-branch-view', ['id' => $borrow->getBibliothequeId()]) ?>"><?= e($borrow->getBibliothequeNom()) ?></a>
                            <?php else: ?>
                                <?= e($borrow->getBibliothequeNom()) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= e(format_date_fr($borrow->getBorrowDate())) ?></td>
                        <td><?= e(format_date_fr($borrow->getReturnDate())) ?></td>
                        <td><span class="badge <?= badge_class($borrow->getStatus()) ?>"><?= e(status_label($borrow->getStatus())) ?></span></td>
                        <td class="table-actions">
                            <form method="post" class="inline-form">
                                <input type="hidden" name="id" value="<?= e((string) $borrow->getId()) ?>">
                                <input type="hidden" name="action" value="confirm">
                                <button class="btn btn-sm btn-primary" type="submit">Confirmer</button>
                            </form>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="id" value="<?= e((string) $borrow->getId()) ?>">
                                <input type="hidden" name="action" value="returned">
                                <button class="btn btn-sm btn-secondary" type="submit">Retourné</button>
                            </form>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="id" value="<?= e((string) $borrow->getId()) ?>">
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
