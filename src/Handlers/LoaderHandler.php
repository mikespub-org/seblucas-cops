<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Response;
use Marsender\EPubLoader\RequestHandler;
use Marsender\EPubLoader\App\ExtraActions;
use Marsender\EPubLoader\Models\AuthorInfo;
use Marsender\EPubLoader\Models\BookInfo;
use Marsender\EPubLoader\Models\SeriesInfo;
use Marsender\EPubLoader\Workflows\Writers\CalibreWriter;

/**
 * Summary of LoaderHandler
 */
class LoaderHandler extends BaseHandler
{
    public const HANDLER = "loader";
    public const PREFIX = "/loader";

    protected Request $request;
    protected ?string $dbFileName = null;
    protected ?CalibreWriter $writer = null;

    public static function getRoutes()
    {
        return [
            "loader-action-dbNum-authorId-urlPath" => ["/loader/{action}/{dbNum:\d+}/{authorId:\w+}/{urlPath:.*}"],
            "loader-action-dbNum-authorId" => ["/loader/{action}/{dbNum:\d+}/{authorId:\w*}"],
            "loader-action-dbNum" => ["/loader/{action}/{dbNum:\d+}"],
            // with trailing / from templates
            "loader-action-" => ["/loader/{action}/"],
            "loader-action" => ["/loader/{action}"],
            "loader" => ["/loader"],
        ];
    }

    public function handle($request)
    {
        if (!class_exists('\Marsender\EPubLoader\RequestHandler')) {
            echo 'This handler is available in developer mode only (without --no-dev option):' . "<br/>\n";
            echo '$ composer install -o';
            return;
        }
        // get the global config for epub-loader from config/loader.php
        $gConfig = require dirname(__DIR__, 2) . '/config/loader.php';  // NOSONAR
        // adapt for use with COPS
        $gConfig['endpoint'] = self::link();
        $gConfig['app_name'] = 'COPS Loader';
        $gConfig['version'] = Config::VERSION;
        $gConfig['admin_email'] = '';
        $gConfig['create_db'] = false;
        $gConfig['databases'] = [];

        // specify a cache directory for any Google or Wikidata lookup
        $cacheDir = $gConfig['cache_dir'] ?? dirname(__DIR__, 2) . '/cache';
        if (!is_dir($cacheDir) && !mkdir($cacheDir, 0o777, true)) {
            echo 'Please make sure the cache directory can be created';
            return;
        }
        if (!is_writable($cacheDir)) {
            echo 'Please make sure the cache directory is writeable';
            return;
        }

        // get the current COPS calibre directories
        $calibreDir = Config::get('calibre_directory');
        if (!is_array($calibreDir)) {
            $calibreDir = ['COPS Database' => $calibreDir];
        }
        foreach ($calibreDir as $name => $path) {
            $gConfig['databases'][] = ['name' => $name, 'db_path' => rtrim((string) $path, '/'), 'epub_path' => '.'];
        }

        /**
         * Define callbacks to update information here
         */
        $gConfig['callbacks'] = [
            'setAuthorInfo' => [$this, 'setAuthorInfo'],  // $this->setAuthorInfo(...),
            'setSeriesInfo' => [$this, 'setSeriesInfo'],  // $this->setSeriesInfo(...),
            'setBookInfo' => [$this, 'setBookInfo'],  // $this->setBookInfo(...),
        ];
        $this->request = $request;

        $action = $request->get('action');
        $dbNum = $request->getId('dbNum');
        $itemId = $request->get('authorId');
        $urlPath = $request->get('urlPath');

        $urlParams = $request->urlParams;
        if (!is_null($dbNum) && !empty($gConfig['databases'][$dbNum])) {
            $this->dbFileName = $gConfig['databases'][$dbNum]['db_path'] . '/metadata.db';
        }

        // you can define extra actions for your app - see example.php
        $handler = new RequestHandler($gConfig, ExtraActions::class, $cacheDir);
        $result = $handler->request($action, $dbNum, $urlParams, $urlPath);

        if (method_exists($handler, 'isDone')) {
            if ($handler->isDone()) {
                return;
            }
        }

        // handle the result yourself or let epub-loader generate the output
        $result = array_merge($gConfig, $result);
        //$templateDir = 'templates/twigged/loader';  // if you want to use custom templates
        $templateDir = $gConfig['template_dir'] ?? null;
        $template = null;

        $response = new Response(Response::MIME_TYPE_HTML);
        return $response->setContent($handler->output($result, $templateDir, $template));
    }

    /**
     * Summary of getCalibreWriter
     * @return CalibreWriter|null
     */
    public function getCalibreWriter()
    {
        if (isset($this->writer) || empty($this->dbFileName)) {
            return $this->writer;
        }
        $this->writer = new CalibreWriter($this->dbFileName, false);
        return $this->writer;
    }

    /**
     * Callback function for Loader to set author info here
     * @param int $authorId
     * @param AuthorInfo $authorInfo
     * @return bool
     */
    public function setAuthorInfo($authorId, $authorInfo)
    {
        $database = $this->request->database();
        $instance = Author::getInstanceById($authorId, $database);
        if (empty($instance->id)) {
            return false;
        }
        $writer = $this->getCalibreWriter();
        if (empty($writer)) {
            return false;
        }

        $result = true;
        if (!empty($authorInfo->image) && str_contains($authorInfo->image, '://')) {
            $image = true;
            if (empty(Config::get('thumbnail_handling')) ||
                Config::get('thumbnail_handling') == "1") {
                $imageField = Config::get('calibre_database_field_image', '');
                $image = $writer->setAuthorImage($authorInfo, $authorId, $imageField);
            }
            $result = $result && ($image ? true : false);
        }
        if (!empty($authorInfo->link) && str_contains($authorInfo->link, '://')) {
            // check for duplicate links
            if (empty($instance->link) || $instance->link != $authorInfo->link) {
                $link = $writer->setAuthorLink($authorInfo, $authorId);
            } else {
                $link = true;
            }
            $result = $result && ($link ? true : false);
        }
        if (!empty($authorInfo->note) && !empty($authorInfo->note->doc)) {
            $root = self::link();
            $dbNum = $this->request->getId('dbNum');
            $urlPrefix = $root . self::PREFIX . '/resource/' . $dbNum;
            $content = $authorInfo->note->parseHtml($urlPrefix);
            $curNote = $instance->getNote();
            if (empty($curNote) || $curNote->doc != $content) {
                $note = $writer->addNote($authorInfo->note);
            } else {
                $note = true;
            }
            $result = $result && ($note ? true : false);
        }
        if (!empty($authorInfo->books)) {
            // ...
        }
        if (!empty($authorInfo->series)) {
            // ...
        }
        return $result;
    }

    /**
     * Callback function for Loader to set series info here
     * @param int $seriesId
     * @param SeriesInfo $seriesInfo
     * @return bool
     */
    public function setSeriesInfo($seriesId, $seriesInfo)
    {
        $database = $this->request->database();
        $instance = Serie::getInstanceById($seriesId, $database);
        if (empty($instance->id)) {
            return false;
        }
        $writer = $this->getCalibreWriter();
        if (empty($writer)) {
            return false;
        }

        $result = true;
        if (!empty($seriesInfo->image) && str_contains($seriesInfo->image, '://')) {
            $image = true;
            if (empty(Config::get('thumbnail_handling')) ||
                Config::get('thumbnail_handling') == "1") {
                $imageField = Config::get('calibre_database_field_image', '');
                $image = $writer->setSeriesImage($seriesInfo, $seriesId, $imageField);
            }
            $result = $result && ($image ? true : false);
        }
        if (!empty($seriesInfo->link) && str_contains($seriesInfo->link, '://')) {
            // check for duplicate links
            if (empty($instance->link) || $instance->link != $seriesInfo->link) {
                $link = $writer->setSeriesLink($seriesInfo, $seriesId);
            } else {
                $link = true;
            }
            $result = $result && ($link ? true : false);
        }
        if (!empty($seriesInfo->note) && !empty($seriesInfo->note->doc)) {
            $root = self::link();
            $dbNum = $this->request->getId('dbNum');
            $urlPrefix = $root . self::PREFIX . '/resource/' . $dbNum;
            $content = $seriesInfo->note->parseHtml($urlPrefix);
            $curNote = $instance->getNote();
            if (empty($curNote) || $curNote->doc != $content) {
                $note = $writer->addNote($seriesInfo->note);
            } else {
                $note = true;
            }
            $result = $result && ($note ? true : false);
        }
        if (!empty($seriesInfo->books)) {
            // ...
        }
        if (!empty($seriesInfo->authors)) {
            // ...
        }
        return $result;
    }

    /**
     * Callback function for Loader to set book info here
     * @param int $bookId
     * @param BookInfo $bookInfo
     * @return bool
     */
    public function setBookInfo($bookId, $bookInfo)
    {
        $database = $this->request->database();
        $book = Book::getBookById($bookId, $database);
        if (empty($book) || empty($book->id)) {
            return false;
        }
        $writer = $this->getCalibreWriter();
        if (empty($writer)) {
            return false;
        }

        $result = true;
        if (!empty($bookInfo->cover) && str_contains($bookInfo->cover, '://')) {
            $image = true;
            if (empty(Config::get('thumbnail_handling')) ||
                Config::get('thumbnail_handling') == "1") {
                $coverField = Config::get('calibre_database_field_cover', '');
                $image = $writer->setBookCover($bookInfo, $bookId, $coverField);
            }
            $result = $result && ($image ? true : false);
        }
        if (!empty($bookInfo->uri) && str_contains($bookInfo->uri, '://')) {
            // check for duplicate links
            $links = array_filter($book->getIdentifiers(), function ($identifier) use ($bookInfo) {
                return $identifier->getLink() == $bookInfo->uri;
            });
            if (empty($links)) {
                $link = $writer->setBookUri($bookInfo, $bookId);
            } else {
                $link = true;
            }
            $result = $result && ($link ? true : false);
        }
        if (!empty($bookInfo->description)) {
            $content = $bookInfo->description;
            if (empty($book->comment) || $book->comment != $content) {
                $writer->addBookComments($bookInfo, $bookId);
                $note = true;
            } else {
                $note = true;
            }
            $result = $result && ($note ? true : false);
        }
        if (!empty($bookInfo->identifiers)) {
            // ...
        }
        if (!empty($bookInfo->authors)) {
            // ...
        }
        if (!empty($bookInfo->series)) {
            // ...
        }
        return $result;
    }
}
