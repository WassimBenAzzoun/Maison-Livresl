<section class="section">
    <div class="section-head">
        <h1>Mon profil</h1>
        <p>Consultez et modifiez vos informations personnelles.</p>
    </div>

    <div class="split-layout">
        <div class="panel">
            <h2>Mes informations</h2>
            <ul class="info-list">
                <li><strong>Nom :</strong> <?= e($user->getFullName()) ?></li>
                <li><strong>Email :</strong> <?= e($user->getEmail()) ?></li>
                <li><strong>Téléphone :</strong> <?= e($user->getPhone()) ?></li>
                <li><strong>Adresse :</strong> <?= e($user->getAddress()) ?></li>
                <li><strong>Statut :</strong> <span class="badge <?= badge_class($user->getStatus()) ?>"><?= e(status_label($user->getStatus())) ?></span></li>
                <li><strong>Adhésion :</strong> <?= e(membership_label($user->getMembershipType())) ?></li>
                <li><strong>Expire le :</strong> <?= e(format_date_fr($user->getMembershipExpiresAt())) ?></li>
                <li><strong>Payée au :</strong> <?= e($user->getMembershipBranchName() ?: '-') ?></li>
            </ul>
        </div>

        <form class="panel form-stack" method="post" data-validate>
            <h2>Modifier le profil</h2>
            <label>Nom complet
                <input class="form-control" type="text" name="full_name" value="<?= e($user->getFullName()) ?>" required>
            </label>
            <label>Email
                <input class="form-control" type="email" name="email" value="<?= e($user->getEmail()) ?>" required>
            </label>
            <label>Téléphone
                <input class="form-control" type="text" name="phone" value="<?= e($user->getPhone()) ?>" required>
            </label>
            <label>Adresse
                <input class="form-control" type="text" name="address" value="<?= e($user->getAddress()) ?>">
            </label>
            <label>Nouveau mot de passe
                <input class="form-control" type="password" name="password" placeholder="Laisser vide pour ne pas changer">
            </label>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
        </form>
    </div>
</section>
