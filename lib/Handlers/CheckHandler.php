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
    public const HANDLER = "check";

    public static function getRoutes()
    {
        return [
            "/check" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        header('Content-Type: text/plain;charset=utf-8');

        echo date(DATE_COOKIE) . "\n\n";
        echo var_export($request, true);
    }
}
