<?php ob_start(); ?>
<div class="container-sm mt-8 mb-8">
    <h1>Privacy Policy</h1>
    <div class="card mt-4" style="line-height: 1.8;">
        <p>We collect minimal data to provide this service.</p>
        <h2 class="mt-4">What we collect</h2>
        <ul style="margin-left: 1.5rem; margin-top: 1rem;">
            <li>Your email address (for authentication and account recovery).</li>
            <li>Profile information you voluntarily provide (display name, bio, links).</li>
            <li>Basic analytics (page views and link clicks).</li>
        </ul>
        <h2 class="mt-4">What we do not do</h2>
        <ul style="margin-left: 1.5rem; margin-top: 1rem;">
            <li>We do not sell your personal data.</li>
            <li>We do not track you across other websites.</li>
        </ul>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
