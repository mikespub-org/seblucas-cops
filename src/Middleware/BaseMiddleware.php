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
use SebLucas\Cops\Input\HasContextInterface;
use SebLucas\Cops\Input\HasContextTrait;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Response;

/**
 * Summary of BaseMiddleware
 */
abstract class BaseMiddleware implements HasContextInterface
{
    use HasContextTrait;

    /**
     * @param Request $request
     * @param BaseHandler $handler
     * @return Response|void
     */
    abstract public function process($request, $handler);
}
