<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Bibliotheque.php';
require_once __DIR__ . '/../app/models/Emprunt.php';
require_once __DIR__ . '/../app/models/User.php';

require_login_page();

$sessionUser = $_SESSION['user'] ?? [];
$userModel = new User();
$user = $userModel->findWithMembership((int) ($sessionUser['id'] ?? 0));

if (!$user) {
    flash_set('danger', 'Utilisateur introuvable.');
    redirect_page('logout');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $existing = $userModel->findByEmail($email);

    if ($fullName === '' || $email === '' || $phone === '') {
        flash_set('warning', 'Les champs essentiels sont obligatoires.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('warning', 'Adresse email invalide.');
    } elseif ($existing && $existing->getId() !== $user->getId()) {
        flash_set('warning', 'Cet email est déjà utilisé par un autre compte.');
    } else {
        $userModel->updateProfile((int) $user->getId(), [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'password' => $password ? password_hash($password, PASSWORD_DEFAULT) : '',
        ]);

        $_SESSION['user'] = [
            'id' => $user->getId(),
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'status' => $user->getStatus(),
            'role' => $user->getRole(),
        ];
        if ($user->getRole() === 'admin') {
            $_SESSION['admin'] = $_SESSION['user'];
        }

        flash_set('success', 'Profil mis à jour.');
        redirect_page('profile');
    }
}

$currentBorrowings = (new Emprunt())->currentByUser((int) $user->getId());
$previousBorrowings = (new Emprunt())->previousByUser((int) $user->getId());
$pageTitle = 'Mon profil';
$activePage = 'profile';
require __DIR__ . '/partials/header.php';
?>

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

        <form class="panel form-stack" method="post" data-profile-form>
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

<?php require __DIR__ . '/partials/footer.php'; ?>
