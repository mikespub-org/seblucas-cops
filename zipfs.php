<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for epubjs-reader
 * URL format: zipfs.php/{db}/{data}/{comp}
 *
 * @author mikespub
 * @deprecated 3.1.0 use index.php/zipfs instead
 */

$link = str_replace('zipfs.php', 'index.php/zipfs', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
