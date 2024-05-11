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
 * Summary of CheckHandler
 */
class CheckHandler extends BaseHandler
{
    public const ENDPOINT = "check";

    public static function getRoutes()
    {
        return [
            "/check" => [static::PARAM => static::ENDPOINT],
        ];
    }

    public function handle($request)
    {
        // ...
    }
}
