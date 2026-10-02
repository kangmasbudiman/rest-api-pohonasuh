<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 * Standar Laravel 8 (hilang dari salinan project ini).
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Emulasi mod_rewrite Apache untuk PHP built-in server.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
