<?php
$error_code = '403';
if (isset($csrf_expired) && $csrf_expired) {
    $error_message = 'Your session expired. Please log in and retry.';
} else {
    $error_message = 'Forbidden. You do not have permission to access this.';
}
require __DIR__ . '/_partial.php';
