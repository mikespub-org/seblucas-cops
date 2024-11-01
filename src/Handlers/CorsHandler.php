<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

/**
 * Summary of CorsHandler
 */
class CorsHandler extends BaseHandler
{
    public const HANDLER = "cors";
    public const PREFIX = "";
    public const PARAMLIST = [];

    public static function getRoutes()
    {
        return [
            // @todo add cors options after the last handler or use middleware or...
            //'cors' => ['/{path:.*}', ['_handler' => 'TODO'], ['OPTIONS']],
        ];
    }

    public function handle($request)
    {
        return null;
    }
}
