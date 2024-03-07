<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for calres:// resource
 * URL format: calres.php/{db}/{alg}/{digest} with {hash} = {alg}:{digest}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Calibre\Resource;

require_once __DIR__ . '/config.php';

// don't try to match path params here
$request = new Request(false);
$path = $request->path();
if (empty($path) || $path == '/') {
    Request::notFound();
}
$path = substr($path, 1);
if (!preg_match('/^\d+\/\w+\/\w+$/', $path)) {
    Request::notFound();
}
[$database, $alg, $digest] = explode('/', $path);
$hash = $alg . ':' . $digest;
if (!Resource::sendImageResource($hash, null, intval($database))) {
    Request::notFound();
}
