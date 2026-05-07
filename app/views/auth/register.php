<section class="auth-layout">
    <form class="panel form-stack auth-form" method="post" data-validate>
        <h1>Créer un accès</h1>
        <p>Rejoignez Maison des Livres pour réserver et gérer vos lectures.</p>
        <label>Nom complet
            <input class="form-control" type="text" name="full_name" value="<?= e(old('full_name')) ?>" required>
        </label>
        <label>Email
            <input class="form-control" type="email" name="email" value="<?= e(old('email')) ?>" required>
        </label>
        <label>Téléphone
            <input class="form-control" type="text" name="phone" value="<?= e(old('phone')) ?>" required>
        </label>
        <label>Adresse
            <input class="form-control" type="text" name="address" value="<?= e(old('address')) ?>">
        </label>
        <label>Mot de passe
            <input class="form-control" type="password" name="password" required>
        </label>
        <label>Confirmer le mot de passe
            <input class="form-control" type="password" name="password_confirm" required>
        </label>
        <button class="btn btn-primary" type="submit">Créer mon accès</button>
        <p class="muted">Déjà inscrit ? <a href="<?= url('login') ?>">Accéder à mon espace</a></p>
    </form>
</section>
