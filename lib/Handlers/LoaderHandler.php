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
    public const ENDPOINT = "loader";

    public static function getRoutes()
    {
        return [
            "/loader/{action}/{dbNum:\d+}/{authorId:\d+}" => [static::PARAM => static::ENDPOINT],
            "/loader/{action}/{dbNum:\d+}" => [static::PARAM => static::ENDPOINT],
            "/loader/{action}" => [static::PARAM => static::ENDPOINT],
            "/loader" => [static::PARAM => static::ENDPOINT],
        ];
    }

    public function handle($request)
    {
        // ...
    }
}
