<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Output\RestApi;

/**
 * Handle datatables
 */
class TableHandler extends BaseHandler
{
    public const HANDLER = "tables";
    public static string $template = "templates/tables.html";

    public static function getRoutes()
    {
        return [
            //"/tables/{db:\d+}/{name:\w+}/{id}" => [static::PARAM => static::HANDLER],
            //"/tables/{db:\d+}/{name:\w+}" => [static::PARAM => static::HANDLER],
            //"/tables/{db:\d+}" => [static::PARAM => static::HANDLER],
            "/tables" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
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

        $response = new Response('text/html;charset=utf-8');
        return $response->setContent(Format::template($data, static::$template));
    }
}
