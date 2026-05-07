<section class="section">
    <div class="section-head">
        <h1>Statistiques</h1>
        <p>Les indicateurs sont préparés automatiquement pour offrir une vue claire de l'activité.</p>
    </div>

    <div class="grid cards-4">
        <article class="stat-card"><span>Livres</span><strong><?= e((string) $stats['total_books']) ?></strong></article>
        <article class="stat-card"><span>Disponibles</span><strong><?= e((string) $stats['available_books']) ?></strong></article>
        <article class="stat-card"><span>Emprunts</span><strong><?= e((string) $stats['total_borrowings']) ?></strong></article>
        <article class="stat-card"><span>Utilisateurs</span><strong><?= e((string) $stats['total_users']) ?></strong></article>
    </div>

    <div class="grid cards-3 mt-24">
        <article class="stat-card soft"><span>En attente</span><strong><?= e((string) $stats['pending_borrowings']) ?></strong></article>
        <article class="stat-card soft"><span>Confirmés</span><strong><?= e((string) $stats['confirmed_borrowings']) ?></strong></article>
        <article class="stat-card soft"><span>Retournés</span><strong><?= e((string) $stats['returned_borrowings']) ?></strong></article>
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
