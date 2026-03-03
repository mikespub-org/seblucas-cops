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
 * Check for admin access - must be called after AuthMiddleware if not enabled for all
 * @see \SebLucas\Cops\Middleware\AuthMiddleware::checkUserAuthentication()
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
        $error = static::checkAdminAccess($request);
        if (isset($error)) {
            return Response::redirect(PageHandler::link(['admin' => $error]));
        }

        // do something with $response after $handler
        return $handler->handle($request);
    }

    /**
     * Summary of checkAdminAccess
     * @param Request $request
     * @return int|null
     */
    public static function checkAdminAccess($request)
    {
        $admin = Config::get('enable_admin', false);
        // admin is not enabled
        if (empty($admin)) {
            return 0;
        }
        $username = $request->getUserName();
        // current user is not admin user
        if (is_string($admin) && $admin !== $username) {
            return 1;
        }
        // current user is not in admin list
        if (is_array($admin) && !in_array($username, $admin)) {
            return 2;
        }
        // admin is enabled for all or matches current user
        return null;
    }
}
