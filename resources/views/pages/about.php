<?php ob_start(); ?>
<div class="container-sm mt-8 mb-8">
    <h1>About <?= e(site_name()) ?></h1>
    <div class="card mt-4" style="line-height: 1.8;">
        <p>We built this because we thought it was funny. A link-in-bio site wrapped in the aesthetic of a very different industry.</p>
        <p class="mt-4">Please note that this is a <strong>parody</strong>. We are not affiliated with Pornhub, Aylo, or any other adult entertainment company. We do not host explicit content.</p>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
