<?php
/**
 * Shared error page body. Error views are reached via `require` from anywhere
 * (router, middleware, exception handler), so they cannot assume any variable
 * has been set — every value has a fallback.
 *
 * @var string $code
 * @var string $heading
 * @var string $message
 */
$title = $code . ' — ' . site_name();
ob_start();
?>
<div class="error-page">
    <div>
        <div class="error-code"><?= e($code) ?></div>
        <h1><?= e($heading) ?></h1>
        <p><?= $message ?></p>
        <div class="error-actions">
            <a href="/" class="btn btn-primary">Go home</a>
            <?php if (!empty($secondary)): ?>
                <a href="<?= e($secondary['href']) ?>" class="btn btn-secondary"><?= e($secondary['label']) ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
