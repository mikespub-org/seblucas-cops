<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Output\Response;

/**
 * Summary of CheckHandler
 */
class CheckHandler extends BaseHandler
{
    public const HANDLER = "check";

    public static function getRoutes()
    {
        return [
            "/check/{more:.*}" => [static::PARAM => static::HANDLER],
            "/check" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        $message = date(DATE_COOKIE) . "\n\n";
        $message .= var_export($request, true);

        $response = new Response('text/plain;charset=utf-8');
        $response->sendData($message);
    }
}
