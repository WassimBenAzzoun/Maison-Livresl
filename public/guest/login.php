<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = (new User())->authenticate($email, $password);

    if ($user) {
        $_SESSION['user'] = [
            'id' => $user->getId(),
            'full_name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'address' => $user->getAddress(),
            'status' => $user->getStatus(),
            'role' => $user->getRole(),
        ];

        if ($user->getRole() === 'admin') {
            $_SESSION['admin'] = $_SESSION['user'];
        }

        session_regenerate_id(true);
        flash_set('success', 'Connexion réussie.');
        header('Location: ' . ($user->getRole() === 'admin' ? '/admin/admin-dashboard.php' : '/user/profile.php'));
        exit;
    }

    flash_set('danger', 'Identifiants invalides ou compte inactif.');
}

$pageTitle = 'Maison des Livres | Connexion';
$activePage = 'login';
require __DIR__ . '/../partials/header.php';
?>

<section class="auth-layout">
    <form class="panel form-stack auth-form" method="post">
        <h1>Accès à votre espace</h1>
        <p>Connectez-vous pour suivre vos réservations et votre profil.</p>
        <label>Email
            <input class="form-control" type="email" name="email" required>
        </label>
        <label>Mot de passe
            <input class="form-control" type="password" name="password" required>
        </label>
        <button class="btn btn-primary" type="submit">Entrer</button>
        <p class="muted">Pas encore de compte ? <a href="/guest/register.php">Créer un accès</a></p>
    </form>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>


