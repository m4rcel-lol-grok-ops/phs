<?php ob_start(); ?>
<section class="section">
    <div class="container">
        <h1 class="section-title">Features</h1>
        <p class="section-sub">Everything you need for an extremely important internet presence.</p>
        <div class="features-grid">
            <div class="feature-card"><div class="icon">👤</div><h3>Custom Profiles</h3><p>Avatar, banner, bio, pronouns, location, and verification badge support.</p></div>
            <div class="feature-card"><div class="icon">🔗</div><h3>Bio Links</h3><p>Unlimited links with titles, descriptions, emojis, icons, and click tracking.</p></div>
            <div class="feature-card"><div class="icon">🎨</div><h3>Themes</h3><p>Hub, Midnight, Terminal, Corporate, Degenerate, Minimal — then customize further.</p></div>
            <div class="feature-card"><div class="icon">🌈</div><h3>Colors & Fonts</h3><p>Background, cards, accents, text, buttons. Built-in safe font choices.</p></div>
            <div class="feature-card"><div class="icon">🖼️</div><h3>Backgrounds</h3><p>Solid, gradient, uploaded image, or remote URL.</p></div>
            <div class="feature-card"><div class="icon">🎵</div><h3>Music Player</h3><p>Optional HTML5 audio with title, artist, play/pause. No autoplay.</p></div>
            <div class="feature-card"><div class="icon">✨</div><h3>Effects</h3><p>Particles, animated gradients, glow, snow, CRT, scanlines — off by default.</p></div>
            <div class="feature-card"><div class="icon">📈</div><h3>Statistics</h3><p>Profile views, link clicks, join date. Dashboard for owners only.</p></div>
            <div class="feature-card"><div class="icon">🔍</div><h3>Discover</h3><p>Browse public profiles. Search, sort by popular/new/random. Opt out anytime.</p></div>
            <div class="feature-card"><div class="icon">🛡️</div><h3>Moderation</h3><p>Report system, admin panel, content policy. No explicit content allowed.</p></div>
            <div class="feature-card"><div class="icon">🐳</div><h3>Docker Ready</h3><p>One command to start. Works behind Caddy or any reverse proxy.</p></div>
            <div class="feature-card"><div class="icon">📱</div><h3>Responsive</h3><p>Looks good on phones. Touch-friendly. Accessible focus states.</p></div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/resources/views/layouts/main.php';
