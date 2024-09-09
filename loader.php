<?php
/**
 * COPS (Calibre OPDS PHP Server) Epub Loader (example)
 * URL format: loader.php/{action}/{dbNum}/{authorId}?...
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

require_once __DIR__ . '/config/config.php';

if (!class_exists('\Marsender\EPubLoader\RequestHandler')) {
    echo 'This endpoint is an example for development only';
    return;
}

$link = str_replace('loader.php', 'index.php/loader', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
