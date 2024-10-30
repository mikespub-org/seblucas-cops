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
 * Summary of CheckHandler
 */
class TestHandler extends BaseHandler
{
    public const HANDLER = "phpunit";

    public static function getRoutes()
    {
        return [];
    }

    public function handle($request)
    {
        return null;
    }
}
