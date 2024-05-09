<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for calres:// resource
 * URL format: calres.php/{db}/{alg}/{digest} with {hash} = {alg}:{digest}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

// don't try to match path params here
$request = Framework::getRequest(false);

$handler = Framework::getHandler('calres');
$handler->handle($request);
