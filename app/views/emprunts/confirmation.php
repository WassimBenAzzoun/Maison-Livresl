<section class="section">
    <div class="section-head">
        <h1>Confirmation d'emprunt</h1>
        <p>Votre demande a bien été enregistrée. Le statut initial est <strong>En attente</strong>.</p>
    </div>

    <div class="panel confirmation-panel">
        <ul class="info-list">
            <li><strong>Référence :</strong> #<?= e((string) $emprunt->getId()) ?></li>
            <li><strong>Livre :</strong> <?= e($emprunt->getLivreTitre()) ?></li>
            <li><strong>Bibliothèque :</strong> <?= e($emprunt->getBibliothequeNom()) ?></li>
            <li><strong>Nom :</strong> <?= e($emprunt->getFullName()) ?></li>
            <li><strong>Email :</strong> <?= e($emprunt->getEmail()) ?></li>
            <li><strong>Téléphone :</strong> <?= e($emprunt->getPhone()) ?></li>
            <li><strong>Début :</strong> <?= e(format_date_fr($emprunt->getBorrowDate())) ?></li>
            <li><strong>Retour :</strong> <?= e(format_date_fr($emprunt->getReturnDate())) ?></li>
            <li><strong>Statut :</strong> <span class="badge <?= badge_class($emprunt->getStatus()) ?>"><?= e(status_label($emprunt->getStatus())) ?></span></li>
        </ul>

        <div class="card-actions">
            <a class="btn btn-primary" href="<?= url('my-borrowings') ?>">Voir mes emprunts</a>
            <a class="btn btn-secondary" href="<?= url('books') ?>">Retour au catalogue</a>
        </div>
    </div>
</section>
