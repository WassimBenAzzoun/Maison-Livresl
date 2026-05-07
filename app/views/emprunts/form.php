<section class="section">
    <div class="section-head">
        <h1>Demande d'emprunt</h1>
        <p>Remplissez le formulaire ci-dessous pour enregistrer votre demande.</p>
    </div>

    <div class="split-layout">
        <div class="panel">
            <img src="<?= e($livre->getCouverture() ?: 'assets/images/book-placeholder.svg') ?>" alt="<?= e($livre->getTitre()) ?>" class="book-cover">
            <h2><?= e($livre->getTitre()) ?></h2>
            <p><?= e($livre->getAuteur()) ?> · <?= e($livre->getCategorie()) ?></p>
            <p class="muted"><?= e((string) $livre->getAvailableExemplaires()) ?> exemplaire(s) disponible(s)</p>
            <?php if (!empty($membership)): ?>
                <div class="alert alert-info">
                    <strong>Adhésion :</strong> <?= e(membership_label($membership->getMembershipType())) ?> jusqu'au <?= e(format_date_fr($membership->getMembershipExpiresAt())) ?>.
                </div>
            <?php endif; ?>
        </div>

        <form class="panel form-stack" method="post" data-validate data-borrow-form>
            <label>Nom complet
                <input class="form-control" type="text" name="full_name" value="<?= e(old('full_name', $user->getFullName() ?? '')) ?>" required>
            </label>
            <label>Email
                <input class="form-control" type="email" name="email" value="<?= e(old('email', $user->getEmail() ?? '')) ?>" required>
            </label>
            <label>Téléphone
                <input class="form-control" type="text" name="phone" value="<?= e(old('phone', $user->getPhone() ?? '')) ?>" required>
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
