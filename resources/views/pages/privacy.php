<?php ob_start(); ?>
<section class="section">
    <div class="container container-md">
        <div class="section-head">
            <h1 class="section-title">Privacy Policy</h1>
            <p class="section-sub">What this site stores, and what it does not.</p>
        </div>

        <div class="card prose">
            <p class="lead">Short version: we store what is needed to run a bio-link page, and nothing else.</p>

            <h2>What we collect</h2>
            <ul>
                <li>Your email address, username, and a hashed password.</li>
                <li>The profile content you choose to publish: display name, bio, links, images, appearance settings.</li>
                <li>Aggregate usage counters: profile views and link clicks.</li>
            </ul>

            <h2>Passwords</h2>
            <p>
                Passwords are hashed with PHP's <code>password_hash()</code>. Plaintext passwords are never
                stored and cannot be recovered — only reset.
            </p>

            <h2>IP addresses</h2>
            <p>
                Your IP address is recorded temporarily for rate limiting, abuse prevention, and to
                deduplicate view and click counts. It is not shown to other users, and it is not used to
                build a profile of you.
            </p>

            <h2>Third parties</h2>
            <p>
                No third-party analytics, advertising, or tracking scripts are included by default.
                Nothing is sold. Note that images and audio you link from other sites are loaded by your
                visitors' browsers directly from those sites.
            </p>

            <h2>Deleting your data</h2>
            <p>
                You can delete your account at any time from
                <a href="/dashboard/account">your account settings</a>. This permanently removes your
                profile, links, uploaded images, and statistics.
            </p>

            <div class="notice">
                <strong>Self-hosted instance?</strong>
                The operator of this particular server controls the database and server logs. Contact them
                for anything not covered here.
            </div>
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
