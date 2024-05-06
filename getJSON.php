<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for JSON ajax requests
 * URL format: getJSON.php?page={page}&...
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\JsonRenderer;

require_once __DIR__ . '/config.php';

$request = new Request();

header('Content-Type:application/json;charset=utf-8');

echo json_encode(JsonRenderer::getJson($request));
