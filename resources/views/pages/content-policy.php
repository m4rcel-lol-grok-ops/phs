<?php ob_start(); ?>
<div class="container-sm mt-8 mb-8">
    <h1>Content Policy</h1>
    <div class="card mt-4" style="line-height: 1.8;">
        <h2 style="color: #ef4444;">No Explicit Content</h2>
        <p>This is a hard rule. Despite the branding and the aesthetic, <strong>no sexually explicit material, pornography, suggestive imagery, or NSFW content</strong> is allowed on this platform.</p>
        <p class="mt-4">We are a profile service. Any violation of this rule will result in immediate account termination without appeal.</p>
        <h2 class="mt-4">General Rules</h2>
        <ul style="margin-left: 1.5rem; margin-top: 1rem;">
            <li>No spam or misleading links.</li>
            <li>No harassment, bullying, or hate speech.</li>
            <li>Do not impersonate other people or brands.</li>
            <li>Do not infringe on copyright or trademarks.</li>
        </ul>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
