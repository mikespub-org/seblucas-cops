<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\HasContextInterface;
use SebLucas\Cops\Input\HasContextTrait;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Middleware\BaseMiddleware;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\UriGenerator;

/**
 * Summary of BaseHandler
 */
abstract class BaseHandler implements HasContextInterface
{
    use HasContextTrait;

    public const HANDLER = "";
    public const PREFIX = "";
    public const PARAMLIST = [];
    public const GROUP_PARAM = "";

    /**
     * Array of path => params for this handler
     * Note: Route will add Request::HANDLER_PARAM => static::class to params
     * @return array<string, mixed>
     */
    public static function getRoutes()
    {
        return [];
    }

    /**
     * Array of middleware classes that should apply for this handler
     * @return array<class-string<BaseMiddleware>>
     */
    public static function getMiddleware()
    {
        return [];
    }

    /**
     * Get link for this specific handler and params (incl _route)
     * @param array<mixed> $params
     * @return string
     */
    public static function link($params = [])
    {
        // use this specific handler to find the route
        $params[Request::HANDLER_PARAM] = static::class;
        return UriGenerator::process($params);
    }

    /**
     * Get page link for this specific handler and params (incl _route)
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function page($page = null, $params = [])
    {
        if (!empty($page)) {
            $params['page'] = $page;
        }
        return static::link($params);
    }

    /**
     * Generate link based on pre-defined route name for this handler (make visible)
     * @param string $routeName
     * @param array<mixed> $params
     * @return string|null
     */
    public static function route($routeName, $params = [])
    {
        $params[Request::ROUTE_PARAM] = $routeName;
        return static::link($params);
    }

    /**
     * Find all routes for matching - include parent routes in page handler
     * @return array<string, mixed>
     */
    public static function findRoutes()
    {
        $routes = static::getRoutes();
        return $routes;
    }

    /**
     * Summary of request - not used except in tests - use Request::build instead?
     * @param array<mixed> $params
     * @param array<mixed> $server
     * @return Request
     */
    public static function request($params = [], $server = [])
    {
        return Request::build($params, static::class, $server);
    }

    /**
     * Summary of __construct
     * @param RequestContext $context
     */
    public function __construct($context)
    {
        $this->setContext($context);
    }

    /**
     * @param Request $request
     * @return Response|void
     */
    abstract public function handle($request);
}
