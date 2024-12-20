<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Framework;

use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\FastRouter;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Handlers\QueueBasedHandler;

/**
 * Minimal Framework
 */
class Framework
{
    /** @var class-string */
    protected static $routerClass = FastRouter::class;
    //protected static $routerClass = Routing::class;
    /** @var RouterInterface|null */
    protected static $router = null;
    /** @var HandlerManager|null */
    protected static $handlerManager = null;
    /** @var array<mixed> */
    protected static $middlewares = [];

    /**
     * Single request runner with optional handler name
     * @param string $name
     * @return void
     */
    public static function run($name = 'html')
    {
        $request = self::getRequest();
        if ($request->invalid) {
            $name = 'error';
            $handler = Framework::createHandler($name);
            $response = $handler->handle($request);
            if ($response instanceof Response) {
                //$response->prepare($request);
                $response->send();
            }
            return;
        }

        // route to the right handler if needed
        $name = $request->getHandler()::HANDLER;

        // special case for json requests here
        if ($name == 'html' && $request->isJson()) {
            $name = 'json';
        }
        $handler = Framework::createHandler($name);
        if (empty(self::$middlewares)) {
            $response = $handler->handle($request);
            if ($response instanceof Response) {
                //$response->prepare($request);
                $response->send();
            }
            return;
        }
        // @see https://www.php-fig.org/psr/psr-15/meta/#queue-based-request-handler
        $queue = new QueueBasedHandler($handler);
        foreach (self::$middlewares as $middleware) {
            $queue->add(new $middleware());
        }
        $response = $queue->handle($request);
        if ($response instanceof Response) {
            //$response->prepare($request);
            $response->send();
        }
    }

    /**
     * Get request instance
     * @return Request
     */
    public static function getRequest()
    {
        // initialize routes if needed
        self::init();
        // when using Apache .htaccess redirect
        if (empty($_SERVER['PATH_INFO']) && !empty($_SERVER['REDIRECT_PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = $_SERVER['REDIRECT_PATH_INFO'];
        }
        $request = new Request();
        // @todo move to RequestContext
        $request->matchRoute();
        // @todo set locale for Route Slugger - must be done after init() and Request()
        Route::setLocale($request->locale());
        return $request;
    }

    /**
     * Initialize framework
     * @return void
     */
    public static function init()
    {
        self::loadRoutes();
    }

    /**
     * Load routes for all handlers
     * @return void
     */
    public static function loadRoutes()
    {
        //Route::load();
        Route::init();
        // @todo add cors options after the last handler or use middleware or...
        //'cors' => ['/{path:.*}', ['_handler' => 'TODO'], ['OPTIONS']],
    }

    /**
     * Summary of getHandlers
     * @return array<string, class-string>
     */
    public static function getHandlers()
    {
        return self::getHandlerManager()->getHandlers();
    }

    /**
     * Create handler instance based on name or class-string
     * @param string|class-string $name
     * @return mixed
     */
    public static function createHandler($name)
    {
        return self::getHandlerManager()->createHandler($name);
    }

    /**
     * Summary of getHandlerManager
     * @return HandlerManager
     */
    public static function getHandlerManager()
    {
        if (!isset(self::$handlerManager)) {
            self::$handlerManager = new HandlerManager();
        }
        return self::$handlerManager;
    }

    /**
     * Summary of getRouter
     * @return RouterInterface
     */
    public static function getRouter()
    {
        if (!isset(self::$router)) {
            self::$router = new self::$routerClass();
        }
        return self::$router;
    }
}
