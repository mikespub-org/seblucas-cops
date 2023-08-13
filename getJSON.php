<?php
/**
 * COPS (Calibre OPDS PHP Server) HTML main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\JSONRenderer;

require_once __DIR__ . '/config.php';

$request = new Request();

header('Content-Type:application/json;charset=utf-8');

echo json_encode(JSONRenderer::getJson($request));
