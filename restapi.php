<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for REST API
 * URL format: restapi.php{/route}?db={db} etc.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

// try out route urls
Config::set('use_route_urls', true);

Framework::run('restapi');
