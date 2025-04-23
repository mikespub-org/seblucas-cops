<?php

/**
 * COPS (Calibre OPDS PHP Server) router script for the PHP built-in web server
 * with route urls (always enabled in 3.4.+) and no PHP script in URL (dev only)
 *
 * $ php -S 0.0.0.0:8080 router.php
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

if (php_sapi_name() !== 'cli-server') {
    echo 'This router is for the php development server only';
    return;
}

// check if the requested path actually exists
$path = parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
// parse_url() does not decode URL-encoded characters in the path
$path = rawurldecode((string) $path);
if (!empty($path) && file_exists(__DIR__ . $path) && !is_dir(__DIR__ . $path)) {
    return false;
}
// route to the right PHP endpoint if needed
$script = rawurldecode((string) $_SERVER['SCRIPT_NAME']);
if (str_contains($path, $script . '/') && file_exists(__DIR__ . $script)) {
    return false;
}

// set environment vars for the front controller
$_SERVER['SCRIPT_NAME'] = '/index.php';
// parse_url() does not decode URL-encoded characters in the path
$_SERVER['PATH_INFO'] ??= parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);

// use index.php as front controller
include __DIR__ . '/index.php';  // NOSONAR
