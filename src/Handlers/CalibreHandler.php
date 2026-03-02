<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Note;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Output\Response;

/**
 * Handle calibre:// links for Calibre comments, notes etc.
 * URL format: index.php/calibre/{action}/{library}/{details}
 * @see https://manual.calibre-ebook.com/url_scheme.html
 */
class CalibreHandler extends BaseHandler
{
    public const HANDLER = "calibre";
    public const PREFIX = "/calibre";
    public const PARAMLIST = ["action", "library", "details"];
    public const URL_SCHEME = "calibre";

    public static function getRoutes()
    {
        return [
            "calibre-details" => ["/calibre/{action}/{library}/{details:.*}"],
            "calibre-library" => ["/calibre/{action}/{library}"],
        ];
    }

    public function handle($request)
    {
        $action = $request->get('action');
        $library = $request->get('library');
        $details = $request->get('details');

        $database = self::findDatabaseId($library, $request->database());

        switch ($action) {
            case 'switch-library':
                return Response::redirect(PageHandler::link(['db' => $database]));
            case 'show-book':
                // show book details here - fall through
            case 'book-details':
                if (is_numeric($details)) {
                    $book = Book::getBookById((int) $details, $database);
                    if (!empty($book)) {
                        // use html handler by default here
                        $book->setHandler(HtmlHandler::class);
                        return Response::redirect($book->getUri(['redirected' => 1]));
                    }
                }
                return Response::notFound($request, 'Invalid Book');
            case 'show-note':
                // @see https://github.com/kovidgoyal/calibre/blob/9e7ec507e7234dc1aa2621b8c8b506b58bb2044f/src/calibre/gui2/ui.py#L725
                [$type, $item] = explode('/', $details);
                if (str_starts_with($type, '_')) {
                    // @todo custom column
                    return Response::sendError($request, 'Invalid Note Type');
                } else {
                    // calibre field
                }
                if (str_starts_with($item, 'id_')) {
                    $item = (int) substr($item, 3);
                    $note = Note::getInstanceByTypeItem($type, $item, $database);
                } elseif (str_starts_with($item, 'hex_')) {
                    $name = hex2bin(substr($item, 4));
                    $note = Note::getInstanceByTypeName($type, $name, $database);
                } elseif (str_starts_with($item, 'val_')) {
                    $name = rawurldecode(substr($item, 4));
                    $note = Note::getInstanceByTypeName($type, $name, $database);
                } else {
                    return Response::notFound($request, 'Invalid Note Item');
                }
                if (empty($note)) {
                    return Response::notFound($request, 'Invalid Note');
                }
                return Response::redirect($note->getUri());
            case 'view-book':
                // @todo use reader depending on format
                break;
            case 'search':
                // @todo search in library + use virtual_library
                break;
            default:
                return Response::sendError($request, 'Invalid Action');
        }
        $response = new Response(Response::MIME_TYPE_TEXT);
        $content = 'TODO: handle calibre link "' . htmlspecialchars($action) . '" in library "' . htmlspecialchars($library) . '" (' . $database . ') for "' . htmlspecialchars($details) . '"';

        return $response->setContent($content);
    }

    /**
     * Summary of findDatabaseId
     * @param string $library
     * @param ?int $database
     * @return int|null
     */
    public static function findDatabaseId($library, $database = null)
    {
        // use current database
        if (empty($library) || $library == '_') {
            return $database;
        }
        // only one database configured for COPS - assume this one
        if (!Database::isMultipleDatabaseEnabled()) {
            return $database;
        }
        // /calibre/book-details/_hex_-4261736557697468536f6d65426f6f6b73/17
        if (str_starts_with($library, '_hex_-')) {
            $library = hex2bin(substr($library, 6));
        }
        $database = Database::findDatabaseId($library);
        // Library names are the folder name of the library folder with spaces replaced by underscores
        // /calibre/book-details/Library_Name/17
        if (is_null($database) && str_contains($library, '_')) {
            $library = str_replace('_', ' ', $library);
            $database = Database::findDatabaseId($library);
        }
        return $database;
    }
}
