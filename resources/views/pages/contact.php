<?php ob_start(); ?>
<section class="section">
    <div class="container container-md">
        <div class="section-head">
            <h1 class="section-title">Contact</h1>
            <p class="section-sub">Questions, feedback, or reports of abuse.</p>
        </div>

        <div class="card mb-3">
            <h3 class="mb-2">Reporting content</h3>
            <p class="text-muted">
                Every public profile has a <strong>Report profile</strong> link at the bottom of the page.
                Reports go straight to the moderation queue in the admin panel — that is the fastest route
                for anything that breaks the <a href="/content-policy">Content Policy</a>.
            </p>
        </div>

        <div class="card mb-3">
            <h3 class="mb-2">Everything else</h3>
            <p class="text-muted">
                This software is self-hosted, so there is no central support desk. For account problems,
                takedown requests, or questions about how this instance is run, contact whoever operates
                the server you are currently looking at.
            </p>
        </div>

        <div class="notice">
            <strong>Running this instance?</strong>
            Put your own contact address here by editing
            <code>resources/views/pages/contact.php</code>.
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
