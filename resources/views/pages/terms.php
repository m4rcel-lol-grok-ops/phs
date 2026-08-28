<?php ob_start(); ?>
<div class="container-sm mt-8 mb-8">
    <h1>Terms of Service</h1>
    <div class="card mt-4" style="line-height: 1.8;">
        <p>By using <?= e(site_name()) ?>, you agree to these terms.</p>
        <h2 class="mt-4">Acceptable Use</h2>
        <p>You agree to adhere strictly to our <a href="<?= e(url('/content-policy')) ?>">Content Policy</a>. Any violation may result in account termination.</p>
        <h2 class="mt-4">Disclaimer of Warranties</h2>
        <p>This service is provided "as is" and "as available". We make no warranties, express or implied, regarding the reliability or availability of the service.</p>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
