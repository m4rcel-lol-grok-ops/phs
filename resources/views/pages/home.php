<?php ob_start(); ?>
<section class="hero">
    <div class="container">
        <h1>Your links deserve <span class="accent">better</span>.</h1>
        <p class="subtitle">Build your own ridiculous little corner of the internet.</p>
        <div class="hero-actions">
            <a href="/register" class="btn btn-primary">Create Your Profile</a>
            <a href="/discover" class="btn btn-secondary">Browse Profiles</a>
        </div>
        <p class="hero-note">
            No subscriptions. No mysterious algorithms.<br>
            Just you, your links, and an unnecessarily dramatic amount of orange.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Why does this exist?</h2>
        <p class="section-sub">Honestly? Because the internet needed one more bio-link site with extra orange.</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon">🎨</div>
                <h3>Themes & Customization</h3>
                <p>Pick a theme, change colors, upload backgrounds, enable ridiculous effects.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🔗</div>
                <h3>Unlimited Links</h3>
                <p>Add as many links as you want. Reorder them. Track clicks. Feel powerful.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📊</div>
                <h3>Real Stats</h3>
                <p>Profile views and link clicks. Totally real visitors (we promise).</p>
            </div>
            <div class="feature-card">
                <div class="icon">🎵</div>
                <h3>Background Music</h3>
                <p>Attach a track. No autoplay. Your visitors decide if they want the vibe.</p>
            </div>
            <div class="feature-card">
                <div class="icon">✨</div>
                <h3>Visual Effects</h3>
                <p>Particles, glow, CRT, scanlines — optional, and respectful of reduced-motion.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🔒</div>
                <h3>Self-Hostable</h3>
                <p>Your data, your server. Docker-ready. No corporate overlords required.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" style="padding-top:0">
    <div class="container text-center">
        <h2 class="section-title">Become unnecessarily visible</h2>
        <p class="section-sub">It takes about 30 seconds. Your future self will either thank you or question everything.</p>
        <a href="/register" class="btn btn-primary">Create Your Profile</a>
    </div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/resources/views/layouts/main.php';
