    </div>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <p>Maison des Livres, un espace pour découvrir, réserver et suivre vos lectures.</p>
        <p>Des collections, des points de service et un accueil pensé pour les lecteurs.</p>
    </div>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>"></script>
<script>
if (document.body.classList.contains('page-home')) {
    if (typeof window.initHomeQuote === 'function') {
        window.initHomeQuote();
    }
}
</script>
</body>
</html>
