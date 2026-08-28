<?php ob_start(); ?>
<div class="container-sm mt-8 mb-8">
    <h1>Contact</h1>
    <div class="card mt-4" style="line-height: 1.8;">
        <p>Need help? Found a bug? Want to report a profile?</p>
        <p class="mt-4">For profile reports, please use the "Report profile" button on the specific profile page.</p>
        <p class="mt-4">For all other inquiries, please email the site administrator.</p>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
