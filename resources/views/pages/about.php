<?php ob_start(); ?>
<section class="section">
    <div class="container container-md">
        <div class="section-head">
            <span class="eyebrow">Independent project</span>
            <h1 class="section-title">About</h1>
            <p class="section-sub">What this is, and — more importantly — what it is not.</p>
        </div>

        <div class="card prose mb-3">
            <h2 style="margin-top:0">Parody &amp; independence notice</h2>
            <p><strong>pornhub.singles is a parody and humor project.</strong></p>
            <p>
                It is independently created and is <strong>not</strong> affiliated with, sponsored by,
                endorsed by, or operated by Pornhub, Aylo, or any of their subsidiaries, partners, or
                associated entities.
            </p>
            <p>
                The project's name, aesthetic, jokes, and overall concept are intended as humorous
                commentary and internet parody. It is a personal-profile / bio-link service and does not
                represent itself as an official service of anyone.
            </p>
            <p>
                Any trademarks, names, or recognizable references belonging to third parties remain the
                property of their respective owners.
            </p>
            <p>
                The purpose of this project is entertainment, parody, and experimentation with web
                design — not to impersonate the services it references.
            </p>
        </div>

        <div class="card prose mb-3">
            <h2 style="margin-top:0">What is this actually?</h2>
            <p>
                A self-hostable bio-link platform with a deliberately ridiculous aesthetic. You create a
                profile, add your links, pick a theme, and share one URL. That is the whole idea.
            </p>
            <p>
                <strong>No pornography is hosted here.</strong> Explicit sexual material is prohibited by
                the <a href="/content-policy">Content Policy</a> and is removed when reported.
            </p>
        </div>

        <div class="card prose">
            <h2 style="margin-top:0">Technically speaking</h2>
            <p>
                Plain PHP, MariaDB, and no JavaScript framework. It runs from a single Docker Compose file
                behind whatever reverse proxy you already have. The source is MIT licensed, so you can
                read every line that touches your data — and change any of it.
            </p>
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
