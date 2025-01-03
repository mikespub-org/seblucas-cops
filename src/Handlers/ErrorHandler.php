<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Output\Response;

/**
 * Summary of ErrorHandler
 */
class ErrorHandler extends BaseHandler
{
    public const HANDLER = "error";

    public static function getRoutes()
    {
        return [];
    }

    public function handle($request)
    {
        if ($request->getHandler() == HtmlHandler::class && $request->path() != '' && !$request->isJson()) {
            $error = "Invalid request path '" . $request->path() . "'";
            $ref = $request->server('HTTP_REFERER');
            if ($ref) {
                $error .= ' from ' . $ref;
            }
            // this will call exit()
            Response::sendError($request, $error, ["db" => $request->database()]);
        }
        if ($request->getHandler() == TestHandler::class) {
            return new Response();
        }
        Response::notFound($request);
    }
}
