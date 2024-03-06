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
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Format;
use SebLucas\EPubMeta\EPub;
use Exception;

/**
 * EPub Reader based on Monocle
 */
class EPubReader
{
    public static string $endpoint = Config::ENDPOINT["epubfs"];
    public static string $template = "templates/epubreader.html";
    public static string $epubClass = EPub::class;

    /**
     * Summary of getComponentContent
     * @param EPub $book
     * @param string $component
     * @param array<mixed> $params
     * @return ?string
     */
    public static function getComponentContent($book, $component, $params = [])
    {
        $data = $book->component($component);
        $endpoint = Config::ENDPOINT["epubfs"];

        $callback = function ($m) use ($book, $component, $params, $endpoint) {
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
            $params['comp'] = $comp;
            $out = $method . "'" . Route::url($endpoint, null, $params) . $hash . "'" . $end;
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
     * @param int $idData
     * @param string $component
     * @param Request $request
     * @return string
     */
    public static function getContent($idData, $component, $request)
    {
        $book = Book::getBookByDataId($idData, $request->database());
        if (!$book) {
            throw new Exception('Unknown data ' . $idData);
        }
        $params = ['data' => $idData, 'db' => $book->getDatabaseId()];

        $epub = new static::$epubClass($book->getFilePath('EPUB', $idData));
        $epub->initSpineComponent();

        $data = static::getComponentContent($epub, $component, $params);

        header('Content-Type: ' . $epub->componentContentType($component));

        return $data;
    }

    /**
     * Summary of getReader
     * @param int $idData
     * @param Request $request
     * @return string
     */
    public static function getReader($idData, $request)
    {
        $version = $request->get('version', Config::get('epub_reader', 'monocle'));
        if ($version == 'epubjs') {
            return static::getEpubjsReader($idData, $request);
        }
        $book = Book::getBookByDataId($idData, $request->database());
        if (!$book) {
            throw new Exception('Unknown data ' . $idData);
        }
        $params = ['data' => $idData, 'db' => $book->getDatabaseId()];

        try {
            $epub = new static::$epubClass($book->getFilePath('EPUB', $idData));
            $epub->initSpineComponent();
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $components = implode(', ', array_map(function ($comp) {
            return "'" . $comp . "'";
        }, $epub->components()));

        $contents = implode(', ', array_map(function ($content) {
            return static::addContentItem($content);
        }, $epub->contents()));

        $params['comp'] = '~COMP~';
        $link = str_replace(urlencode('~COMP~'), '', Route::url(static::$endpoint, null, $params));

        $data = [
            'title'      => $book->title,
            'version'    => Config::VERSION,
            'resources'  => Route::url('resources'),
            'styles'     => Route::url('styles'),
            'favicon'    => Route::url(Config::get('icon')),
            'components' => $components,
            'contents'   => $contents,
            'link'       => $link,
        ];

        return Format::template($data, static::$template);
    }

    /**
     * Summary of addContentItem
     * @param array<mixed> $item
     * @return string
     */
    public static function addContentItem($item)
    {
        if (empty($item['children'])) {
            return "{title: '" . addslashes($item['title']) . "', src: '" . $item['src'] . "'}";
        }
        foreach (array_keys($item['children']) as $idx) {
            $item['children'][$idx] = static::addContentItem($item['children'][$idx]);
        }
        return "{title: '" . addslashes($item['title']) . "', src: '" . $item['src'] . "', children: [" . implode(', ', $item['children']) . "]}";
    }

    /**
     * Encode the component name (to replace / and -)
     * @param mixed $src
     * @return string
     */
    public static function encode($src)
    {
        $encodeReplace = static::$epubClass::$encodeNameReplace;
        return str_replace(
            $encodeReplace[0],
            $encodeReplace[1],
            $src
        );
    }

    /**
     * Summary of getEpubjsReader
     * @param int $idData
     * @param Request $request
     * @return string
     */
    public static function getEpubjsReader($idData, $request)
    {
        $endpoint = Config::ENDPOINT["zipfs"];
        $template = "templates/epubjs-reader.html";
        $book = Book::getBookByDataId($idData, $request->database());
        if (!$book) {
            throw new Exception('Unknown data ' . $idData);
        }
        $epub = $book->getFilePath('EPUB', $idData);
        if (!$epub || !file_exists($epub)) {
            throw new Exception('Unknown file ' . $epub);
        }
        // URL format: zipfs.php/{db}/{idData}/{component}
        $db = $book->getDatabaseId() ?? 0;
        $link = Route::url($endpoint . "/{$db}/{$idData}/");

        $dist = Route::url(dirname(Config::get('assets')) . '/mikespub/epubjs-reader/dist');
        $data = [
            'title'      => htmlspecialchars($book->title),
            'version'    => Config::VERSION,
            'dist'       => $dist,
            'link'       => $link,
        ];

        return Format::template($data, $template);
    }
}
