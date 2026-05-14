<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Emprunt.php';
require_once __DIR__ . '/../app/models/Livre.php';
require_once __DIR__ . '/../app/models/User.php';

require_admin_page();

$livreModel = new Livre();
$empruntModel = new Emprunt();
$userModel = new User();

$stats = [
    'total_books' => $livreModel->countTotal(),
    'available_books' => $livreModel->countAvailable(),
    'total_borrowings' => $empruntModel->countTotal(),
    'pending_borrowings' => $empruntModel->countByStatus('pending'),
    'confirmed_borrowings' => $empruntModel->countByStatus('confirmed'),
    'returned_borrowings' => $empruntModel->countByStatus('returned'),
    'total_users' => $userModel->countTotal(),
    'by_category' => $empruntModel->countByCategory(),
    'by_branch' => $empruntModel->countByBranch(),
];

$pageTitle = 'Maison des Livres | Statistiques';
$activePage = 'admin-statistics';
require __DIR__ . '/partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Statistiques</h1>
        <p>Les indicateurs sont préparés automatiquement pour offrir une vue claire de l'activité.</p>
    </div>

    <div class="grid cards-4">
        <article class="stat-card"><span>Livres</span><strong><?= htmlspecialchars((string) $stats['total_books'], ENT_QUOTES, 'UTF-8') ?></strong></article>
        <article class="stat-card"><span>Disponibles</span><strong><?= htmlspecialchars((string) $stats['available_books'], ENT_QUOTES, 'UTF-8') ?></strong></article>
        <article class="stat-card"><span>Emprunts</span><strong><?= htmlspecialchars((string) $stats['total_borrowings'], ENT_QUOTES, 'UTF-8') ?></strong></article>
        <article class="stat-card"><span>Utilisateurs</span><strong><?= htmlspecialchars((string) $stats['total_users'], ENT_QUOTES, 'UTF-8') ?></strong></article>
    </div>

    <div class="grid cards-3 mt-24">
        <article class="stat-card soft"><span>En attente</span><strong><?= htmlspecialchars((string) $stats['pending_borrowings'], ENT_QUOTES, 'UTF-8') ?></strong></article>
        <article class="stat-card soft"><span>Confirmés</span><strong><?= htmlspecialchars((string) $stats['confirmed_borrowings'], ENT_QUOTES, 'UTF-8') ?></strong></article>
        <article class="stat-card soft"><span>Retournés</span><strong><?= htmlspecialchars((string) $stats['returned_borrowings'], ENT_QUOTES, 'UTF-8') ?></strong></article>
    </div>

    <div class="charts-grid">
        <div class="panel">
            <h2>Emprunts par catégorie</h2>
            <div id="categoryChart" class="bar-chart"></div>
        </div>
        <div class="panel">
            <h2>Emprunts par bibliothèque</h2>
            <div id="branchChart" class="bar-chart"></div>
        </div>
    </div>
</section>

<script>
window.libraryStats = <?= json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
