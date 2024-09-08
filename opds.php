<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for OPDS 2.0 feed (dev only)
 * URL format: opds.php{/route}?query={query} etc.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 * @deprecated 3.1.0 use index.php/opds instead
 */

if (!class_exists('\Kiwilan\Opds\OpdsResponse')) {
    echo 'This endpoint is an example for development only';
    return;
}

$link = str_replace('opds.php', 'index.php/opds', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
