<?php
$code = '500';
$heading = 'Something broke on our end.';
$message = 'The error has been logged. Try again in a moment — if it keeps happening, '
    . 'the site operator can find details in <code>storage/logs</code>.';
require __DIR__ . '/_error.php';
