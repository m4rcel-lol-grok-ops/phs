<?php ob_start(); ?>
<section class="section">
    <div class="container container-md">
        <h1 class="section-title">About</h1>
        <div class="card" style="margin-top:2rem">
            <h2 style="margin-bottom:1rem">Parody & Independence Notice</h2>
            <p style="margin-bottom:1rem;color:var(--text-muted)">
                pornhub.singles is a parody and humor project.
            </p>
            <p style="margin-bottom:1rem;color:var(--text-muted)">
                The website is independently created and is not affiliated with, sponsored by, endorsed by, or operated by Pornhub, Aylo, or any of their subsidiaries, partners, or associated entities.
            </p>
            <p style="margin-bottom:1rem;color:var(--text-muted)">
                The project's name, aesthetic, jokes, and overall concept are intended as humorous commentary and internet parody. The website is a personal-profile/bio-link service and does not represent itself as an official Pornhub service.
            </p>
            <p style="margin-bottom:1rem;color:var(--text-muted)">
                Any trademarks, names, or recognizable references belonging to third parties remain the property of their respective owners.
            </p>
            <p style="color:var(--text-muted)">
                The purpose of this project is entertainment, parody, and experimentation with web design — not to impersonate or represent the official services referenced by the parody.
            </p>
        </div>
        <div class="card" style="margin-top:1.5rem">
            <h2 style="margin-bottom:1rem">What is this actually?</h2>
            <p style="color:var(--text-muted)">
                A self-hostable bio-link / profile platform with a deliberately ridiculous aesthetic.
                Users create profiles, add links, customize themes, and share a single URL.
                No pornography is hosted. Explicit sexual content is not permitted.
            </p>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/resources/views/layouts/main.php';
