<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Response;

/**
 * Summary of AdminHandler - @todo
 */
class AdminHandler extends BaseHandler
{
    public const HANDLER = "admin";
    public const PREFIX = "/admin";
    public const PARAMLIST = ["more"];

    public static function getRoutes()
    {
        return [
            "admin-more" => ["/admin/{more:.*}"],
            "admin" => ["/admin"],
        ];
    }

    public function handle($request)
    {
        $admin = Config::get('enable_admin');
        if (empty($admin)) {
            return Response::sendError($request, 'Admin is not enabled');
        }
        if (is_string($admin) && $admin !== $request->getUserName()) {
            return Response::sendError($request, 'Admin is not enabled - invalid user');
        }
        if (is_array($admin) && !in_array($request->getUserName(), $admin)) {
            return Response::sendError($request, 'Admin is not enabled - invalid users');
        }
        // ...

        $more = $request->get('more');
        if ($more) {
            return $this->handleMore($request);
        }

        $message = 'Admin - TODO';

        $response = new Response('text/plain;charset=utf-8');
        return $response->setContent($message);
    }

    /**
     * Summary of clearThumbnailCache
     * @param mixed $request
     * @return void
     */
    public function clearThumbnailCache($request)
    {
        // ...
    }

    /**
     * Summary of handleMore
     * @param Request $request
     * @return Response
     */
    public function handleMore($request)
    {
        $message = 'Admin More - TODO';

        $response = new Response('text/plain;charset=utf-8');
        return $response->setContent($message);
    }
}
