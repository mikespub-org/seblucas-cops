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

/**
 * Handle datatables
 */
class TableHandler extends BaseHandler
{
    public const HANDLER = "tables";
    public const PREFIX = "/tables";
    public const PARAMLIST = ["db", "name", "id"];

    public static string $template = "templates/tables.html";

    public static function getRoutes()
    {
        return [
            //"tables-db-name-id" => ["/tables/{db:\d+}/{name:\w+}/{id}"],
            //"tables-db-name" => ["/tables/{db:\d+}/{name:\w+}"],
            //"tables-db" => ["/tables/{db:\d+}"],
            "tables" => ["/tables"],
        ];
    }

    public function handle($request)
    {
        $data = ['link' => RestApiHandler::getBaseUrl()];
        $data['thead'] = '<tr><th>Route</th><th>Description</th></tr>';
        $data['tbody'] = '';
        foreach (Route::getRoutes() as $name => $route) {
            $path = reset($route);
            if (str_contains($path, '{')) {
                continue;
            }
            $data['tbody'] .= '<tr><td><a href="#" class="route">' . $path . '</a></td><td></td></tr>';
        }
        $data['tfoot'] = $data['thead'];

        $response = new Response('text/html;charset=utf-8');
        return $response->setContent(Format::template($data, self::$template));
    }
}
