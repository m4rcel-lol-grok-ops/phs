<?php ob_start(); ?>
<section class="section">
    <div class="container container-md">
        <h1 class="section-title">Terms of Service</h1>
        <div class="card" style="margin-top:2rem;color:var(--text-muted);line-height:1.7">
            <p style="margin-bottom:1rem">By using pornhub.singles you agree to these terms and the Content Policy.</p>
            <p style="margin-bottom:1rem">You are responsible for the content you publish and the external links you share. Do not post prohibited content.</p>
            <p style="margin-bottom:1rem">We may suspend or terminate accounts that violate the rules.</p>
            <p style="margin-bottom:1rem">The service is provided as-is. We make no guarantees of uptime or data permanence on self-hosted instances beyond what the operator configures.</p>
            <p style="margin-bottom:1rem">This is an independent parody project. It is not affiliated with Pornhub or Aylo.</p>
            <p>Account deletion is available from your dashboard.</p>
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
