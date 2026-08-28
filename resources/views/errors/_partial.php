<?php ob_start(); ?>
<div class="container-sm">
    <div class="empty-state mt-8">
        <h1><?= isset($error_code) ? e($error_code) : 'Error' ?></h1>
        <p><?= isset($error_message) ? e($error_message) : 'Something went wrong.' ?></p>
        <a href="<?= e(url('/')) ?>" class="btn">Go home</a>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
