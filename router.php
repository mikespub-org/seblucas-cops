<?php
/**
 * COPS (Calibre OPDS PHP Server) router script for the PHP built-in web server @todo
 * with $config['cops_use_route_urls'] = '1' and no PHP script in URL (dev only)
 *
 * $ php -S 0.0.0.0:8080 router.php
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

if (php_sapi_name() !== 'cli-server') {
    echo 'This router is for the php development server only';
    return;
}

// @todo route to the the right endpoint if needed
//$path = urldecode($_SERVER['SCRIPT_NAME']);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!empty($path) && file_exists(__DIR__ . $path) && !is_dir(__DIR__ . $path)) {
    return false;
}
$script = urldecode($_SERVER['SCRIPT_NAME']);
if (str_contains($path, $script . '/') && file_exists(__DIR__ . $script)) {
    return false;
}

$_SERVER['SCRIPT_NAME'] = '/front.php';
$_SERVER['PATH_INFO'] ??= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// @todo do not use front.php here yet
include __DIR__ . '/front.php';
