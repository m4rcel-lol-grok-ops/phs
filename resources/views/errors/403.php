<?php
$code = '403';
if (!empty($csrf_expired)) {
    $heading = 'Your session expired.';
    $message = 'For your safety we could not verify that form. Log in again and retry — '
        . 'nothing was changed.';
    $secondary = ['href' => '/login', 'label' => 'Log in'];
} else {
    $heading = 'Not for you.';
    $message = 'You do not have permission to view this page. If you think that is wrong, '
        . 'log in with an account that does.';
    $secondary = is_logged_in() ? ['href' => '/dashboard', 'label' => 'Dashboard'] : ['href' => '/login', 'label' => 'Log in'];
}
require __DIR__ . '/_error.php';
