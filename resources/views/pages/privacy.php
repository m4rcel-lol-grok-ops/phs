<?php ob_start(); ?>
<section class="section">
    <div class="container container-md">
        <h1 class="section-title">Privacy Policy</h1>
        <div class="card" style="margin-top:2rem;color:var(--text-muted);line-height:1.7">
            <p style="margin-bottom:1rem">We only collect information required to operate the service: email, username, password hash, profile content you provide, and basic usage stats (profile views, link clicks).</p>
            <p style="margin-bottom:1rem">Passwords are hashed with PHP's password_hash(). We never store plaintext passwords.</p>
            <p style="margin-bottom:1rem">IP addresses may be logged temporarily for rate limiting, abuse prevention, and view/click deduplication.</p>
            <p style="margin-bottom:1rem">We do not sell personal data. We do not use third-party analytics by default.</p>
            <p style="margin-bottom:1rem">You may delete your account at any time from the dashboard. This permanently removes your profile, links, and associated data.</p>
            <p>For questions, use the contact page.</p>
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
