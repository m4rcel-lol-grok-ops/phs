<?php
$code = '405';
$heading = 'Wrong way in.';
$message = 'That address exists, but it does not accept this kind of request. '
    . 'This usually means a stale bookmark or a form submitted twice.';
require __DIR__ . '/_error.php';
