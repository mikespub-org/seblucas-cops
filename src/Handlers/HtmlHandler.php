<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Middleware\ConnectMiddleware;
use SebLucas\Cops\Output\HtmlRenderer;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Pages\PageId;
use InvalidArgumentException;
use Throwable;

/**
 * HTML main handler
 * URL format: index.php?page={page}&...
 */
class HtmlHandler extends PageHandler
{
    public const HANDLER = "html";
    public const ROUTE_FEED = FeedHandler::HANDLER;

    public static function getRoutes()
    {
        return parent::getRoutes();
    }

    public static function getMiddleware()
    {
        return [
            ConnectMiddleware::class,
        ];
    }

    public function handle($request)
    {
        // If we detect that an OPDS reader try to connect try to redirect to index.php/feed
        if (preg_match('/(Librera|MantanoReader|FBReader|Stanza|Marvin|Aldiko|Moon\+ Reader|Chunky|AlReader|EBookDroid|BookReader|CoolReader|PageTurner|books\.ebook\.pdf\.reader|com\.hiwapps\.ebookreader|OpenBook)/', $request->agent())) {
            return Response::redirect(FeedHandler::route(self::ROUTE_FEED, ["db" => $request->database()]));
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

        // set session connected in ConnectMiddleware

        if (in_array($page, [PageId::CUSTOMIZE, PageId::FILTER])) {
            // @todo avoid double work with ConnectMiddleware
            $session = $this->getContext()->getSession();
            $session->start();
            $request->setSession($session);
        }

        $response = new Response(Response::MIME_TYPE_HTML);

        $html = new HtmlRenderer($request, $response);

        try {
            return $response->setContent($html->render($request));
        } catch (InvalidArgumentException $e) {
            return Response::notFound($request, $e->getMessage());
        } catch (Throwable $e) {
            error_log($e);
            return Response::sendError($request, $e->getMessage());
        }
    }
}
