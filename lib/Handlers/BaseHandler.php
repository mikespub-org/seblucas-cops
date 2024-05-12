<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;

/**
 * Summary of BaseHandler
 */
abstract class BaseHandler
{
    public const PARAM = Route::HANDLER_PARAM;
    public const HANDLER = "";

    /**
     * @return array<string, mixed>
     */
    public static function getRoutes()
    {
        return [];
    }

    public function __construct()
    {
        // ...
    }

    /**
     * @param Request $request
     * @return void
     */
    abstract public function handle($request);
}
