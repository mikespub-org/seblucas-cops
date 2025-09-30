<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org//licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Handlers\HandlerManager;

/**
 * Summary of Route
 */
class Route
{
    public const HANDLER_PARAM = "_handler";
    public const ROUTE_PARAM = "_route";

    /** @var HandlerManager|null */
    protected static $manager = null;

    /**
     * Set handler manager from framework
     * @param HandlerManager $handlerManager
     * @return void
     */
    public static function setManager($handlerManager)
    {
        self::$manager = $handlerManager;
    }

    /**
     * Get handler class based on name
     * @param string|class-string $name
     * @return class-string<BaseHandler>
     */
    public static function getHandler($name)
    {
        self::$manager ??= new HandlerManager();
        return self::$manager->getHandlerClass($name);
    }
}
