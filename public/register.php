<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    $userModel = new User();

    if ($fullName === '' || $email === '' || $phone === '' || $password === '') {
        flash_set('warning', 'Tous les champs obligatoires doivent être remplis.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('warning', 'Adresse email invalide.');
    } elseif ($password !== $confirm) {
        flash_set('warning', 'Les mots de passe ne correspondent pas.');
    } elseif ($userModel->findByEmail($email)) {
        flash_set('warning', 'Cet email est déjà utilisé.');
    } else {
        $userModel->create([
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'address' => $address,
            'status' => 'active',
            'role' => 'user',
        ]);

        flash_set('success', 'Compte créé avec succès. Vous pouvez vous connecter.');
        header('Location: login.php');
            exit;
    }
}

$pageTitle = 'Maison des Livres | Inscription';
$activePage = 'register';
require __DIR__ . '/partials/header.php';
?>

<section class="auth-layout">
    <form class="panel form-stack auth-form" method="post">
        <h1>Créer un accès</h1>
        <p>Rejoignez Maison des Livres pour réserver et gérer vos lectures.</p>
        <label>Nom complet
            <input class="form-control" type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </label>
        <label>Email
            <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </label>
        <label>Téléphone
            <input class="form-control" type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </label>
        <label>Adresse
            <input class="form-control" type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Mot de passe
            <input class="form-control" type="password" name="password" required>
        </label>
        <label>Confirmer le mot de passe
            <input class="form-control" type="password" name="password_confirm" required>
        </label>
        <button class="btn btn-primary" type="submit">Créer mon accès</button>
        <p class="muted">Déjà inscrit ? <a href="<?= 'login.php' ?>">Accéder à mon espace</a></p>
    </form>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
