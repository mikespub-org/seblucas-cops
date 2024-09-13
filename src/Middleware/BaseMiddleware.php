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
abstract class BaseMiddleware
{
    /**
     * @param Request $request
     * @param BaseHandler $handler
     * @return Response|void
     */
    abstract public function process($request, $handler);
}
