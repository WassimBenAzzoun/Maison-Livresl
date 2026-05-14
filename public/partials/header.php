<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Maison des Livres', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-<?= htmlspecialchars($activePage ?? 'home', ENT_QUOTES, 'UTF-8') ?>">
<header class="site-header">
    <div class="container nav-bar">
        <a class="brand" href="<?= 'home.php' ?>">
            <span class="brand-mark">MdL</span>
            <span>Maison des Livres</span>
        </a>

        <nav class="nav-links">
            <a class="<?= ($activePage ?? '') === 'home' ? 'active' : '' ?>" href="<?= 'home.php' ?>">Accueil</a>
            <a class="<?= ($activePage ?? '') === 'books' ? 'active' : '' ?>" href="<?= 'books.php' ?>">Livres</a>
            <?php if (!empty($_SESSION['admin'])): ?>
                <a class="<?= ($activePage ?? '') === 'admin-dashboard' ? 'active' : '' ?>" href="<?= 'admin-dashboard.php' ?>">Aperçu</a>
                <a class="<?= ($activePage ?? '') === 'admin-books' ? 'active' : '' ?>" href="<?= 'admin-books.php' ?>">Catalogue</a>
                <a class="<?= ($activePage ?? '') === 'admin-borrowings' ? 'active' : '' ?>" href="<?= 'admin-borrowings.php' ?>">Emprunts</a>
                <a class="<?= ($activePage ?? '') === 'admin-users' ? 'active' : '' ?>" href="<?= 'admin-users.php' ?>">Comptes</a>
                <a class="<?= ($activePage ?? '') === 'admin-branches' ? 'active' : '' ?>" href="<?= 'admin-branches.php' ?>">Points de service</a>
                <a class="<?= ($activePage ?? '') === 'admin-statistics' ? 'active' : '' ?>" href="<?= 'admin-statistics.php' ?>">Statistiques</a>
                <a href="<?= 'admin-logout.php' ?>">Déconnexion</a>
            <?php elseif (!empty($_SESSION['user'])): ?>
                <a class="<?= ($activePage ?? '') === 'profile' ? 'active' : '' ?>" href="<?= 'profile.php' ?>">Mon profil</a>
                <a class="<?= ($activePage ?? '') === 'my-borrowings' ? 'active' : '' ?>" href="<?= 'my-borrowings.php' ?>">Mes emprunts</a>
                <a href="<?= 'logout.php' ?>">Déconnexion</a>
            <?php else: ?>
                <a class="<?= ($activePage ?? '') === 'login' ? 'active' : '' ?>" href="<?= 'login.php' ?>">Connexion</a>
                <a class="<?= ($activePage ?? '') === 'register' ? 'active' : '' ?>" href="<?= 'register.php' ?>">Inscription</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php if ($flash = flash_get()): ?>
    <div class="container">
        <div class="alert alert-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
<?php endif; ?>

<main class="site-main">
    <div class="container">
