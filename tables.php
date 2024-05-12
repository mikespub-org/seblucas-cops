<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for datatables (TODO)
 * URL format: tables.php
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\RestApi;

require_once __DIR__ . '/config.php';

header('Content-Type:text/html;charset=utf-8');

$data = ['link' => Route::link(RestApi::$handler)];
$data['thead'] = '<tr><th>Route</th><th>Description</th></tr>';
$data['tbody'] = '';
foreach (Route::getRoutes() as $route => $queryParams) {
    if (str_contains($route, '{')) {
        continue;
    }
    $data['tbody'] .= '<tr><td><a href="#" class="route">' . $route . '</a></td><td></td></tr>';
}
$data['tfoot'] = $data['thead'];
$template = __DIR__ . '/templates/tables.html';
echo Format::template($data, $template);
