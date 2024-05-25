<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

/**
 * Summary of LoaderHandler
 */
class LoaderHandler extends BaseHandler
{
    public const HANDLER = "loader";

    public static function getRoutes()
    {
        return [
            "/loader/{action}/{dbNum:\d+}/{authorId:\d+}" => [static::PARAM => static::HANDLER],
            "/loader/{action}/{dbNum:\d+}" => [static::PARAM => static::HANDLER],
            "/loader/{action}/" => [static::PARAM => static::HANDLER],
            "/loader/{action}" => [static::PARAM => static::HANDLER],
            "/loader" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        // ...
    }
}
