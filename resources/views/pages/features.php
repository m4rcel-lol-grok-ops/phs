<?php ob_start(); ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Everything included</span>
            <h1 class="section-title">Features</h1>
            <p class="section-sub">Everything you need for an extremely important internet presence.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card"><div class="icon" aria-hidden="true">👤</div><h3>Custom profiles</h3><p>Avatar, banner, bio, pronouns, location, website, and an optional verification badge.</p></div>
            <div class="feature-card"><div class="icon" aria-hidden="true">🔗</div><h3>Bio links</h3><p>Unlimited links with titles, descriptions and emoji. Drag to reorder, hide without deleting.</p></div>
            <div class="feature-card"><div class="icon" aria-hidden="true">🎨</div><h3>Six themes</h3><p>Hub, Midnight, Terminal, Corporate, Degenerate, Minimal — each a full palette you can then override.</p></div>
            <div class="feature-card"><div class="icon" aria-hidden="true">🌈</div><h3>Colours &amp; fonts</h3><p>Background, card, accent, text and button colours, plus four safe font stacks.</p></div>
            <div class="feature-card"><div class="icon" aria-hidden="true">🖼️</div><h3>Backgrounds</h3><p>Theme default, custom gradient, an uploaded image, or a remote URL.</p></div>
            <div class="feature-card"><div class="icon" aria-hidden="true">🎵</div><h3>Music player</h3><p>Optional HTML5 audio with title and artist. No autoplay, ever.</p></div>
            <div class="feature-card"><div class="icon" aria-hidden="true">✨</div><h3>Effects</h3><p>Particles, animated gradient, glow, snow, CRT, scanlines. Off by default and motion-safe.</p></div>
            <div class="feature-card"><div class="icon" aria-hidden="true">📈</div><h3>Statistics</h3><p>Profile views and link clicks with a 14-day chart. Deduplicated, and visible only to you.</p></div>
            <div class="feature-card"><div class="icon" aria-hidden="true">🔍</div><h3>Discover</h3><p>Browse public profiles by popular, new or random. Opt out any time without losing your link.</p></div>
            <div class="feature-card"><div class="icon" aria-hidden="true">🛡️</div><h3>Moderation</h3><p>Report system, admin panel with an audit log, and a content policy that bans explicit material.</p></div>
            <div class="feature-card"><div class="icon" aria-hidden="true">🐳</div><h3>Docker ready</h3><p>One command to start. Works behind Caddy, nginx, or any reverse proxy.</p></div>
            <div class="feature-card"><div class="icon" aria-hidden="true">📱</div><h3>Accessible &amp; responsive</h3><p>Keyboard navigable, screen-reader labelled, and respectful of reduced-motion preferences.</p></div>
        </div>

        <?php if (!is_logged_in() && setting_bool('registration_enabled', true)): ?>
            <p class="text-center mt-4"><a href="/register" class="btn btn-primary btn-lg">Create your profile</a></p>
        <?php endif; ?>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
