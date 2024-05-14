<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for OPDS 2.0 feed (dev only)
 * URL format: opds.php{/route}?query={query} etc.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

if (!class_exists('\Kiwilan\Opds\OpdsResponse')) {
    echo 'This endpoint is an example for development only';
    return;
}

// try out route urls
Config::set('use_route_urls', true);

Framework::run('opds');
