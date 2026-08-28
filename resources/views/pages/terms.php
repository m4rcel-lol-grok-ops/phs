<?php ob_start(); ?>
<section class="section">
    <div class="container container-md">
        <div class="section-head">
            <h1 class="section-title">Terms of Service</h1>
            <p class="section-sub">The rules, briefly.</p>
        </div>

        <div class="card prose">
            <p class="lead">By using this site you agree to these terms and to the
                <a href="/content-policy">Content Policy</a>.</p>

            <h2>Your content</h2>
            <p>
                You are responsible for everything you publish and for every external site you link to.
                You keep ownership of your content; you grant this site only the permission needed to
                display it on your public page.
            </p>

            <h2>Prohibited use</h2>
            <p>
                Do not post content banned by the <a href="/content-policy">Content Policy</a>, impersonate
                other people, distribute malware or phishing links, or attempt to disrupt the service.
            </p>

            <h2>Enforcement</h2>
            <p>
                Accounts that break these rules may be disabled or removed, with or without notice.
                Reports are reviewed by the operators of this instance.
            </p>

            <h2>No warranty</h2>
            <p>
                The service is provided as-is. There is no guarantee of uptime, backups, or data
                permanence beyond whatever the operator of this instance has configured. Keep your own
                copy of anything you cannot afford to lose.
            </p>

            <h2>Your account</h2>
            <p>
                You may delete your account at any time from
                <a href="/dashboard/account">your account settings</a>.
            </p>

            <div class="notice">
                <strong>Parody notice.</strong>
                This is an independent humor project. It is not affiliated with, sponsored by, or endorsed
                by Pornhub, Aylo, or any related entity.
            </div>
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
