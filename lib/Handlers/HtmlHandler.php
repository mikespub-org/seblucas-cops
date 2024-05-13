<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\HtmlRenderer;
use SebLucas\Cops\Pages\PageId;
use Throwable;

/**
 * HTML main handler
 * URL format: index.php?page={page}&...
 */
class HtmlHandler extends PageHandler
{
    public const HANDLER = "index";

    public static function getRoutes()
    {
        return parent::getRoutes();
    }

    public function handle($request)
    {
        // If we detect that an OPDS reader try to connect try to redirect to feed.php
        if (preg_match('/(Librera|MantanoReader|FBReader|Stanza|Marvin|Aldiko|Moon\+ Reader|Chunky|AlReader|EBookDroid|BookReader|CoolReader|PageTurner|books\.ebook\.pdf\.reader|com\.hiwapps\.ebookreader|OpenBook)/', $_SERVER['HTTP_USER_AGENT'])) {
            header('Location: ' . Route::link("feed"));
            return;
        }

        $page     = $request->get('page');
        $database = $request->database();

        // Use the configured home page if needed
        if (!isset($page)) {
            $page = PageId::getHomePage();
            $request->set('page', $page);
        }

        // Access the database ASAP to be sure it's readable, redirect if that's not the case.
        // It has to be done before any header is sent.
        Database::checkDatabaseAvailability($database);

        if (Config::get('fetch_protect') == '1') {
            session_start();
            if (!isset($_SESSION['connected'])) {
                $_SESSION['connected'] = 0;
            }
        }

        header('Content-Type:text/html;charset=utf-8');

        $html = new HtmlRenderer();

        try {
            echo $html->render($request);
        } catch (Throwable $e) {
            error_log($e);
            echo $e->getMessage();
        }
    }
}
