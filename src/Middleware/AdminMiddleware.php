<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Middleware;

use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Handlers\PageHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Response;

/**
 * Summary of AdminMiddleware
 */
class AdminMiddleware extends BaseMiddleware
{
    /**
     * @param Request $request
     * @param BaseHandler $handler
     * @return Response|void
     */
    public function process($request, $handler)
    {
        // do something with $request before $handler
        $admin = Config::get('enable_admin', false);
        if (empty($admin)) {
            return Response::redirect(PageHandler::link(['admin' => 0]));
        }
        if (is_string($admin) && $admin !== $request->getUserName()) {
            return Response::redirect(PageHandler::link(['admin' => 1]));
        }
        if (is_array($admin) && !in_array($request->getUserName(), $admin)) {
            return Response::redirect(PageHandler::link(['admin' => 2]));
        }

        // do something with $response after $handler
        return $handler->handle($request);
    }
}
