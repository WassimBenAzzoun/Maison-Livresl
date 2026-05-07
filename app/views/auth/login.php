<section class="auth-layout">
    <form class="panel form-stack auth-form" method="post" data-validate>
        <h1>Accès à votre espace</h1>
        <p>Connectez-vous pour suivre vos réservations et votre profil.</p>
        <label>Email
            <input class="form-control" type="email" name="email" required>
        </label>
        <label>Mot de passe
            <input class="form-control" type="password" name="password" required>
        </label>
        <button class="btn btn-primary" type="submit">Entrer</button>
        <p class="muted">Pas encore de compte ? <a href="<?= url('register') ?>">Créer un accès</a></p>
    </form>
</section>
