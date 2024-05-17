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
use SebLucas\Cops\Input\Config;

require_once __DIR__ . '/config.php';

// tell COPS we use front controller here
Config::set('use_front_controller', true);
// by default when using front controller
Config::set('use_route_urls', true);

// when using Apache .htaccess redirect
if (empty($_SERVER['PATH_INFO']) && !empty($_SERVER['REDIRECT_PATH_INFO'])) {
    $_SERVER['PATH_INFO'] = $_SERVER['REDIRECT_PATH_INFO'];
}

$request = Framework::getRequest();

// @todo route to the right handler if needed
$name = $request->getHandler();
// special case for json requests here
if ($name == 'index' && $request->isJson()) {
    $name = 'json';
}
// @todo special case for restapi

$handler = Framework::getHandler($name);
$handler->handle($request);
//var_dump($_SERVER);
