<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Maison des Livres') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-<?= e($activePage ?? 'home') ?>">
<header class="site-header">
    <div class="container nav-bar">
        <a class="brand" href="<?= url('home') ?>">
            <span class="brand-mark">MdL</span>
            <span>Maison des Livres</span>
        </a>

        <nav class="nav-links">
            <a class="<?= ($activePage ?? '') === 'home' ? 'active' : '' ?>" href="<?= url('home') ?>">Accueil</a>
            <a class="<?= ($activePage ?? '') === 'books' ? 'active' : '' ?>" href="<?= url('books') ?>">Livres</a>
            <?php if (!empty($_SESSION['admin'])): ?>
                <a class="<?= ($activePage ?? '') === 'admin-dashboard' ? 'active' : '' ?>" href="<?= url('admin-dashboard') ?>">Aperçu</a>
                <a class="<?= ($activePage ?? '') === 'admin-books' ? 'active' : '' ?>" href="<?= url('admin-books') ?>">Catalogue</a>
                <a class="<?= ($activePage ?? '') === 'admin-borrowings' ? 'active' : '' ?>" href="<?= url('admin-borrowings') ?>">Emprunts</a>
                <a class="<?= ($activePage ?? '') === 'admin-users' ? 'active' : '' ?>" href="<?= url('admin-users') ?>">Comptes</a>
                <a class="<?= ($activePage ?? '') === 'admin-branches' ? 'active' : '' ?>" href="<?= url('admin-branches') ?>">Points de service</a>
                <a class="<?= ($activePage ?? '') === 'admin-statistics' ? 'active' : '' ?>" href="<?= url('admin-statistics') ?>">Statistiques</a>
                <a href="<?= url('admin-logout') ?>">Déconnexion</a>
            <?php elseif (!empty($_SESSION['user'])): ?>
                <a class="<?= ($activePage ?? '') === 'profile' ? 'active' : '' ?>" href="<?= url('profile') ?>">Mon profil</a>
                <a class="<?= ($activePage ?? '') === 'my-borrowings' ? 'active' : '' ?>" href="<?= url('my-borrowings') ?>">Mes emprunts</a>
                <a href="<?= url('logout') ?>">Déconnexion</a>
            <?php else: ?>
                <a class="<?= ($activePage ?? '') === 'login' ? 'active' : '' ?>" href="<?= url('login') ?>">Connexion</a>
                <a class="<?= ($activePage ?? '') === 'register' ? 'active' : '' ?>" href="<?= url('register') ?>">Inscription</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php if (!empty($_SESSION['flash'])): ?>
    <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="container">
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    </div>
<?php endif; ?>

<main class="site-main">
    <div class="container">
