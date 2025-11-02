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
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Response;

/**
 * Summary of ConnectMiddleware
 */
class ConnectMiddleware extends BaseMiddleware
{
    /**
     * @param Request $request
     * @param BaseHandler $handler
     * @return Response|void
     */
    public function process($request, $handler)
    {
        // do something with $request before $handler
        if (Config::get('fetch_protect') == '1') {
            $session = $handler->getContext()->getSession();
            $session->start();
            $connected = $session->get('connected');
            if (!isset($connected)) {
                $session->set('connected', 0);
            }
            $request->setSession($session);
        }
        // do something with $response after $handler
        return $handler->handle($request);
    }
}
