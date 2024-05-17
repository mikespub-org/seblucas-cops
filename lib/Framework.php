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
    /**
     * Summary of handlers
     * @var array<string, mixed>
     */
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
    ];

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
        }
        // special case for json requests here
        if ($name == 'index' && $request->isJson()) {
            $name = 'json';
        }
        $handler = Framework::getHandler($name);
        $handler->handle($request);
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
        static::addRoutes();
        // fix PATH_INFO when accessed via traditional endpoint scripts
        if (!empty($name) && Input\Route::addPrefix($name)) {
            if (empty($_SERVER['PATH_INFO']) || $_SERVER['PATH_INFO'] == '/') {
                $_SERVER['PATH_INFO'] =  '/' . $name;
            } elseif (!str_starts_with($_SERVER['PATH_INFO'], '/' . $name . '/')) {
                $_SERVER['PATH_INFO'] =  '/' . $name . $_SERVER['PATH_INFO'];
                // @todo force parsing route urls here?
                Input\Config::set('use_route_urls', 1);
            }
        }
        // @todo special case for restapi
        return new Input\Request($parse);
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
