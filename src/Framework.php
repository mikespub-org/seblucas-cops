<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops;

/**
 * Minimal Framework
 */
class Framework
{
    /** @var array<string, mixed> */
    protected static $handlers = [
        "index" => Handlers\HtmlHandler::class,
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
    ];
    /** @var array<mixed> */
    protected static $middlewares = [];

    /**
     * Single request runner with optional handler name
     * @param string $name
     * @return void
     */
    public static function run($name = '')
    {
        $request = static::getRequest($name);

        // @todo route to the right handler if needed
        if (empty($name)) {
            $name = $request->getHandler();
        } elseif (Input\Config::get('use_route_urls')) {
            $name = $request->getHandler($name);
        }
        // special case for json requests here
        if ($name == 'index' && $request->isJson()) {
            $name = 'json';
        }
        $handler = Framework::getHandler($name);
        if (empty(static::$middlewares)) {
            $handler->handle($request);
            return;
        }
        // @see https://www.php-fig.org/psr/psr-15/meta/#queue-based-request-handler
        $queue = new Handlers\QueueBasedHandler($handler);
        foreach (static::$middlewares as $middleware) {
            $queue->add(new $middleware());
        }
        $queue->handle($request);
    }

    /**
     * Get request instance
     * @param string $name
     * @param bool $parse
     * @return Input\Request
     */
    public static function getRequest($name = '', $parse = true)
    {
        // initialize routes if needed
        static::init();
        // when using Apache .htaccess redirect
        if (empty($_SERVER['PATH_INFO']) && !empty($_SERVER['REDIRECT_PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = $_SERVER['REDIRECT_PATH_INFO'];
        }
        // @deprecated 3.1.0 use index.php/$name instead
        // fix PATH_INFO when accessed via traditional endpoint scripts
        if (!empty($name) && Input\Route::addPrefix($name)) {
            if (empty($_SERVER['PATH_INFO']) || $_SERVER['PATH_INFO'] == '/') {
                $_SERVER['PATH_INFO'] =  '/' . $name;
            } elseif (!str_starts_with((string) $_SERVER['PATH_INFO'], '/' . $name . '/')) {
                $_SERVER['PATH_INFO'] =  '/' . $name . $_SERVER['PATH_INFO'];
                // @todo force parsing route urls here?
                Input\Config::set('use_route_urls', 1);
            }
        }
        // @todo special case for restapi
        return new Input\Request($parse);
    }

    /**
     * Initialize framework
     * @return void
     */
    public static function init()
    {
        static::addRoutes();
    }

    /**
     * Add routes for all handlers
     * @return void
     */
    public static function addRoutes()
    {
        if (Input\Route::count() > 0) {
            return;
        }
        foreach (static::$handlers as $name => $handler) {
            Input\Route::addRoutes($handler::getRoutes());
        }
    }

    /**
     * Get handler instance based on name
     * @param string $name
     * @param mixed $args
     * @return mixed
     */
    public static function getHandler($name, ...$args)
    {
        return new static::$handlers[$name](...$args);
    }
}
