<section class="section">
    <div class="section-head">
        <h1>Mes emprunts</h1>
        <p>L'historique de vos demandes et de leurs statuts.</p>
    </div>

    <div class="table-tools" data-table-tools data-table-target="userBorrowingsTable">
        <input class="form-control" type="search" placeholder="Rechercher un emprunt" data-table-search>
        <select class="form-control" data-table-sort>
            <option value="">Trier par défaut</option>
            <option value="ref:desc">Réf. décroissante</option>
            <option value="book:asc">Livre A-Z</option>
            <option value="branch:asc">Bibliothèque A-Z</option>
            <option value="status:asc">Statut A-Z</option>
            <option value="return:asc">Retour croissant</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="userBorrowingsTable">
            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>Livre</th>
                    <th>Bibliothèque</th>
                    <th>Début</th>
                    <th>Retour</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($borrowings as $borrow): ?>
                    <tr
                        data-search="<?= e(strtolower('#' . $borrow->getId() . ' ' . $borrow->getLivreTitre() . ' ' . $borrow->getBibliothequeNom() . ' ' . status_label($borrow->getStatus()))) ?>"
                        data-sort-ref="<?= e((string) $borrow->getId()) ?>"
                        data-sort-book="<?= e(strtolower($borrow->getLivreTitre())) ?>"
                        data-sort-branch="<?= e(strtolower($borrow->getBibliothequeNom())) ?>"
                        data-sort-status="<?= e(strtolower(status_label($borrow->getStatus()))) ?>"
                        data-sort-return="<?= e($borrow->getReturnDate()) ?>"
                    >
                        <td>#<?= e((string) $borrow->getId()) ?></td>
                        <td><?= e($borrow->getLivreTitre()) ?></td>
                        <td><?= e($borrow->getBibliothequeNom()) ?></td>
                        <td><?= e(format_date_fr($borrow->getBorrowDate())) ?></td>
                        <td><?= e(format_date_fr($borrow->getReturnDate())) ?></td>
                        <td><span class="badge <?= badge_class($borrow->getStatus()) ?>"><?= e(status_label($borrow->getStatus())) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
