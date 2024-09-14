<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Middleware;

use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Response;

/**
 * Summary of BaseMiddleware
 */
class TestMiddleware extends BaseMiddleware
{
    public function __construct()
    {
        // ...
    }

    /**
     * @param Request $request
     * @param BaseHandler $handler
     * @return Response|void
     */
    public function process($request, $handler)
    {
        // do something with $request before $handler
        $request->set('hello', 'world');
        $response = $handler->handle($request);
        if ($response instanceof Response && !$response->isSent()) {
            // @todo do something with $response after $handler
            $response->setContent($response->getContent() . "\nGoodbye!");
        }
        return $response;
    }
}
