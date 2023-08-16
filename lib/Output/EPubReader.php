<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Format;
use SebLucas\EPubMeta\EPub;
use SebLucas\Template\doT;

/**
 * EPub Reader based on Monocle
 */
class EPubReader
{
    public static string $endpoint = Config::ENDPOINT["epubfs"];
    public static string $template = "templates/epubreader.html";

    /**
     * Summary of getComponentContent
     * @param EPub $book
     * @param string $component
     * @param string $add
     * @return string|null
     */
    public static function getComponentContent($book, $component, $add)
    {
        $data = $book->component($component);
        $endpoint = Config::ENDPOINT["epubfs"];

        $callback = function ($m) use ($book, $component, $add, $endpoint) {
            $method = $m[1];
            $path = $m[2];
            $end = '';
            if (preg_match('/^src\s*:/', $method)) {
                $end = ')';
            }
            if (preg_match('/^#/', $path)) {
                return $method . "'" . $path . "'" . $end;
            }
            $hash = '';
            if (preg_match('/^(.+)#(.+)$/', $path, $matches)) {
                $path = $matches[1];
                $hash = '#' . $matches[2];
            }
            $comp = $book->getComponentName($component, $path);
            if (!$comp) {
                return $method . "'#'" . $end;
            }
            $out = $method . "'" . $endpoint . "?" . $add . '&comp=' . $comp . $hash . "'" . $end;
            if ($end) {
                return $out;
            }
            return str_replace('&', '&amp;', $out);
        };

        $data = preg_replace_callback("/(src=)[\"']([^:]*?)[\"']/", $callback, $data);
        $data = preg_replace_callback("/(href=)[\"']([^:]*?)[\"']/", $callback, $data);
        $data = preg_replace_callback("/(\@import\s+)[\"'](.*?)[\"'];/", $callback, $data);
        $data = preg_replace_callback('/(src\s*:\s*url\()(.*?)\)/', $callback, $data);

        return $data;
    }

    /**
     * Summary of getContent
     * @param integer $idData
     * @param string $component
     * @param Request $request
     * @return string
     */
    public static function getContent($idData, $component, $request)
    {
        /** @var Book */
        $book = Book::getBookByDataId($idData);
        $add = 'data=' . $idData;
        $add = Format::addDatabaseParam($add, $book->getDatabaseId());

        $epub = new EPub($book->getFilePath('EPUB', $idData));
        $epub->initSpineComponent();

        $data = self::getComponentContent($epub, $component, $add);

        header('Content-Type: ' . $epub->componentContentType($component));

        return $data;
    }

    /**
     * Summary of getReader
     * @param integer $idData
     * @param Request $request
     * @return string
     */
    public static function getReader($idData, $request)
    {
        $book = Book::getBookByDataId($idData);
        $add = 'data=' . $idData;
        $add = Format::addDatabaseParam($add, $book->getDatabaseId());

        $epub = new EPub($book->getFilePath('EPUB', $idData));
        $epub->initSpineComponent();

        $components = implode(', ', array_map(function ($comp) {
            return "'" . $comp . "'";
        }, $epub->components()));

        $contents = implode(', ', array_map(function ($content) {
            return "{title: '" . addslashes($content['title']) . "', src: '". $content['src'] . "'}";
        }, $epub->contents()));

        $data = [
            'title'      => $book->title,
            'version'    => Config::VERSION,
            'components' => $components,
            'contents'   => $contents,
            'link'       => self::$endpoint . "?" . $add .  "&comp=",
        ];

        header('Content-Type: text/html;charset=utf-8');

        $filecontent = file_get_contents(self::$template);
        $template = new doT();
        $dot = $template->template($filecontent, null);

        return $dot($data);
    }
}
