<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops;

use SebLucas\Cops\Output\Response;

/**
 * Minimal Framework
 */
class Framework
{
    /** @var array<string, class-string> */
    protected static $handlers = [
        "html" => Handlers\HtmlHandler::class,
        "feed" => Handlers\FeedHandler::class,
        "json" => Handlers\JsonHandler::class,
        "fetch" => Handlers\FetchHandler::class,
        "read" => Handlers\ReadHandler::class,
        "epubfs" => Handlers\EpubFsHandler::class,
        "restapi" => Handlers\RestApiHandler::class,
        "check" => Handlers\CheckHandler::class,
        "opds" => Handlers\OpdsHandler::class,
        "loader" => Handlers\LoaderHandler::class,
        "zipper" => Handlers\ZipperHandler::class,
        "calres" => Handlers\CalResHandler::class,
        "zipfs" => Handlers\ZipFsHandler::class,
        "mail" => Handlers\MailHandler::class,
        "graphql" => Handlers\GraphQLHandler::class,
        "tables" => Handlers\TableHandler::class,
        "error" => Handlers\ErrorHandler::class,
        "phpunit" => Handlers\TestHandler::class,
    ];
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
        $queue = new Handlers\QueueBasedHandler($handler);
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
     * @return Input\Request
     */
    public static function getRequest()
    {
        // initialize routes if needed
        self::init();
        // when using Apache .htaccess redirect
        if (empty($_SERVER['PATH_INFO']) && !empty($_SERVER['REDIRECT_PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = $_SERVER['REDIRECT_PATH_INFO'];
        }
        return new Input\Request();
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
        //Input\Route::load();
        Input\Route::init();
        // @todo add cors options after the last handler or use middleware or...
        //'cors' => ['/{path:.*}', ['_handler' => 'TODO'], ['OPTIONS']],
    }

    /**
     * Summary of getHandlers
     * @return array<string, class-string>
     */
    public static function getHandlers()
    {
        return self::$handlers;
    }

    /**
     * Create handler instance based on name or class-string
     * @param string|class-string $name
     * @param mixed $args
     * @return mixed
     */
    public static function createHandler($name, ...$args)
    {
        if (in_array($name, array_values(self::$handlers))) {
            return new $name(...$args);
        }
        if (!isset(self::$handlers[$name])) {
            // this will call exit()
            Response::sendError(null, "Invalid handler name '$name'");
        }
        return new self::$handlers[$name](...$args);
    }
}
