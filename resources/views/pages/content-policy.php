<?php ob_start(); ?>
<section class="section">
    <div class="container container-md">
        <h1 class="section-title">Content Disclosure</h1>
        <div class="card" style="margin-top:2rem">
            <p style="margin-bottom:1rem;color:var(--text-muted)">
                pornhub.singles is an independent humor and parody website designed around the concept of turning the familiar “Hub” aesthetic into a personal bio-link/profile platform.
            </p>
            <p style="margin-bottom:1rem;color:var(--text-muted)">
                Despite the domain name and parody aesthetic, pornhub.singles is not an adult-content hosting platform. Users may create profiles, biographies, social links, portfolios, and other ordinary personal content, but pornography and sexually explicit material are not permitted.
            </p>
            <p style="margin-bottom:1rem;color:var(--text-muted)">
                The site is intended for humor, parody, internet culture, and personal profile pages.
            </p>
            <p style="margin-bottom:1rem;color:var(--text-muted)">
                Users are responsible for the content they publish and the external websites they link to. Links to external websites are provided by users and are not necessarily operated, controlled, or endorsed by pornhub.singles.
            </p>
            <p style="margin-bottom:1.5rem;color:var(--text-muted)">
                Content that violates the site's rules may be removed and accounts may be suspended or terminated.
            </p>
            <h3 style="margin-bottom:0.75rem">Prohibited content includes:</h3>
            <ul style="color:var(--text-muted);padding-left:1.25rem;line-height:1.8">
                <li>Pornographic images/videos or explicit sexual material</li>
                <li>Sexual content involving minors</li>
                <li>Sexual exploitation or non-consensual material</li>
                <li>Doxxing, threats, harassment</li>
                <li>Malware, phishing, malicious links</li>
                <li>Illegal content, spam, impersonation</li>
            </ul>
            <p style="margin-top:1.5rem;color:var(--text-muted)">
                Use the report button on any public profile to flag violations.
            </p>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/resources/views/layouts/main.php';
