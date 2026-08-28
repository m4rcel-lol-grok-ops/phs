<?php ob_start(); ?>
<section class="section">
    <div class="container container-sm">
        <h1 class="section-title">Contact</h1>
        <p class="section-sub">Questions, feedback, or reports of abuse.</p>
        <div class="card">
            <p style="color:var(--text-muted);margin-bottom:1rem">
                For self-hosted instances, contact the site operator.
            </p>
            <p style="color:var(--text-muted)">
                For content reports, use the report button on any public profile — it goes straight to the admin panel.
            </p>
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
