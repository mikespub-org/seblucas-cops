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
 * Summary of TestHandler
 */
class TestHandler extends BaseHandler
{
    public const HANDLER = "phpunit";
    public const PREFIX = "/test";
    public const PARAMLIST = [];

    public static function getRoutes()
    {
        return [
            "test" => ["/test"],
            //"other" => ["/{path:.*}"],
        ];
    }

    public function handle($request)
    {
        return null;
    }
}
