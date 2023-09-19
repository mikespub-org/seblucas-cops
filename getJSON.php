<?php
/**
 * COPS (Calibre OPDS PHP Server) HTML main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\JSONRenderer;

require_once __DIR__ . '/config.php';

$request = new Request();
// @checkme set page based on path info here
$path = $request->path();
// make unexpected pathinfo visible for now...
if (!empty($path) && empty(Config::get('use_route_urls'))) {
    http_response_code(500);
    echo 'Unexpected PATH_INFO in request';
    return;
}

header('Content-Type:application/json;charset=utf-8');

echo json_encode(JSONRenderer::getJson($request));
