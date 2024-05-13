<?php
/**
 * COPS (Calibre OPDS PHP Server) front-end controller (dev only) @todo
 * with $config['cops_use_route_urls'] = '1' and no PHP script in URL
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

$request = Framework::getRequest();

// @todo route to the right handler if needed
$name = $request->getHandler();
$handler = Framework::getHandler($name);
//$handler->handle($request);
var_dump($_SERVER);
