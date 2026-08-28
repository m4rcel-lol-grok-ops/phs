<?php ob_start(); ?>
<section class="section">
    <div class="container container-md">
        <div class="section-head">
            <h1 class="section-title">Content Policy</h1>
            <p class="section-sub">Content disclosure and the rules for this site.</p>
        </div>

        <div class="notice mb-3">
            <strong>Despite the name, this is not an adult-content platform.</strong>
            pornhub.singles is a parody bio-link service. Pornography and sexually explicit material
            are not permitted here.
        </div>

        <div class="card prose mb-3">
            <h2 style="margin-top:0">What this site is for</h2>
            <p>
                Profiles, biographies, social links, portfolios, gaming and dev content, memes, jokes —
                ordinary personal content. The aesthetic is the parody; the service underneath is a
                normal link-in-bio page.
            </p>
            <p>
                Users are responsible for the content they publish and for the external sites they link
                to. External links are supplied by users and are not operated, controlled, or endorsed by
                this site.
            </p>
        </div>

        <div class="card prose mb-3">
            <h2 style="margin-top:0">Prohibited content</h2>
            <ul>
                <li>Pornographic images or video, and sexually explicit material of any kind</li>
                <li><strong>Any sexual content involving minors</strong></li>
                <li>Sexual exploitation or non-consensual material, including intimate images shared without consent</li>
                <li>Doxxing, threats, targeted harassment, or incitement to violence</li>
                <li>Malware, phishing, or otherwise malicious links</li>
                <li>Content that is illegal in the jurisdiction where this instance is operated</li>
                <li>Impersonation of other people or organisations</li>
                <li>Spam and bulk-created accounts</li>
            </ul>
        </div>

        <div class="card prose">
            <h2 style="margin-top:0">Reporting and enforcement</h2>
            <p>
                Every public profile has a <strong>Report profile</strong> link at the bottom of the page.
                Reports go to the moderation queue and are reviewed by the operators of this instance.
            </p>
            <p>
                Content that breaks these rules may be removed, and accounts may be disabled or deleted,
                with or without notice.
            </p>
            <div class="notice notice-danger">
                <strong>Content involving minors</strong> is reported to the relevant authorities and the
                account is removed immediately. There is no appeal.
            </div>
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
