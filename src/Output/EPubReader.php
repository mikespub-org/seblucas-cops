<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Handlers\EpubFsHandler;
use SebLucas\Cops\Handlers\ZipFsHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Output\Format;
use SebLucas\EPubMeta\EPub;
use ZipArchive;
use Exception;

/**
 * EPub Reader based on Monocle or EPub.js
 */
class EPubReader extends BaseRenderer
{
    public const ROUTE_EPUBFS = EpubFsHandler::HANDLER;
    public const ROUTE_ZIPFS = ZipFsHandler::HANDLER;

    public static string $epubClass = EPub::class;

    /**
     * Summary of getComponentContent
     * @param EPub $epub
     * @param string $component
     * @param array<mixed> $params
     * @return ?string
     */
    public function getComponentContent($epub, $component, $params = [])
    {
        $data = $epub->component($component);
        $this->setHandler(EpubFsHandler::class);

        $callback = function ($m) use ($epub, $component, $params) {
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
            $comp = $epub->getComponentName($component, $path);
            if (!$comp) {
                return $method . "'#'" . $end;
            }
            $params['comp'] = $comp;
            $out = $method . "'" . $this->getRoute(self::ROUTE_EPUBFS, $params) . $hash . "'" . $end;
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
     * Summary of sendContent
     * @param int $idData
     * @param string $component
     * @param ?int $database
     * @return Response
     */
    public function sendContent($idData, $component, $database = null)
    {
        $book = Book::getBookByDataId($idData, $database);
        if (!$book) {
            throw new Exception('Unknown data ' . $idData);
        }
        $this->setHandler(EpubFsHandler::class);
        $db = $book->getDatabaseId() ?? 0;
        $params = ['data' => $idData, 'db' => $db];

        /** @var EPub $epub */
        $epub = new self::$epubClass($book->getFilePath('EPUB', $idData));
        $epub->initSpineComponent();

        $data = $this->getComponentContent($epub, $component, $params);

        // get mimetype for $component from EPub manifest here
        $mimetype = $epub->componentContentType($component);

        // use cache control here
        $this->response->setHeaders($mimetype, 0);
        return $this->response->setContent($data);
    }

    /**
     * Summary of getReader
     * @param int $idData
     * @param ?string $version
     * @param ?int $database
     * @return string
     */
    public function getReader($idData, $version = null, $database = null)
    {
        $version ??= Config::get('epub_reader', 'monocle');
        if ($version == 'epubjs') {
            return $this->getEpubjsReader($idData, $database);
        }
        return $this->getMonocleReader($idData, $database);
    }

    /**
     * Summary of getMonocleReader
     * @param int $idData
     * @param ?int $database
     * @param ?string $template
     * @return string
     */
    public function getMonocleReader($idData, $database = null, $template = null)
    {
        $template ??= "templates/epubreader.html";
        $book = Book::getBookByDataId($idData, $database);
        if (!$book) {
            throw new Exception('Unknown data ' . $idData);
        }
        if ($book->isExternal()) {
            return 'The "monocle" epub reader does not work with calibre_external_storage - please use "epubjs" reader instead';
        }
        $this->setHandler(EpubFsHandler::class);

        try {
            /** @var EPub $epub */
            $epub = new self::$epubClass($book->getFilePath('EPUB', $idData));
            $epub->initSpineComponent();
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $components = implode(', ', array_map(function ($comp) {
            return "'" . $comp . "'";
        }, $epub->components()));

        $contents = implode(', ', array_map(function ($content) {
            return self::addContentItem($content);
        }, $epub->contents()));

        // URL format: index.php/epubfs/{db}/{data}/{comp} - let monocle reader retrieve individual components
        $db = $book->getDatabaseId() ?? 0;
        $params = ['db' => $db, 'data' => $idData, 'comp' => 'COMPONENT'];
        $link = str_replace('COMPONENT', '~COMP~', $this->getRoute(self::ROUTE_EPUBFS, $params));

        $data = [
            'title'      => $book->title,
            'version'    => Config::VERSION,
            'resources'  => $this->getPath('resources'),
            'styles'     => $this->getPath('styles'),
            'favicon'    => $this->getPath(Config::get('icon')),
            'components' => $components,
            'contents'   => $contents,
            'link'       => $link,
        ];

        return Format::template($data, $template);
    }

    /**
     * Summary of addContentItem
     * @param array<mixed> $item
     * @return string
     */
    public static function addContentItem($item)
    {
        if (empty($item['children'])) {
            return "{title: '" . addslashes((string) $item['title']) . "', src: '" . $item['src'] . "'}";
        }
        foreach (array_keys($item['children']) as $idx) {
            $item['children'][$idx] = self::addContentItem($item['children'][$idx]);
        }
        return "{title: '" . addslashes((string) $item['title']) . "', src: '" . $item['src'] . "', children: [" . implode(', ', $item['children']) . "]}";
    }

    /**
     * Encode the component name (to replace / and -)
     * @param mixed $src
     * @return string
     */
    public static function encode($src)
    {
        $encodeReplace = self::$epubClass::$encodeNameReplace;
        return str_replace(
            $encodeReplace[0],
            $encodeReplace[1],
            $src
        );
    }

    /**
     * Decode the component name (to replace / and -)
     * @param mixed $src
     * @return string
     */
    public static function decode($src)
    {
        $decodeReplace = self::$epubClass::$decodeNameReplace;
        return str_replace(
            $decodeReplace[0],
            $decodeReplace[1],
            $src
        );
    }

    /**
     * Summary of getEpubjsReader
     * @param int $idData
     * @param ?int $database
     * @param ?string $template
     * @return string
     */
    public function getEpubjsReader($idData, $database = null, $template = null)
    {
        $template ??= "templates/epubjs-reader.html";
        $book = Book::getBookByDataId($idData, $database);
        if (!$book) {
            throw new Exception('Unknown data ' . $idData);
        }
        $this->setHandler(ZipFsHandler::class);
        if ($book->isExternal()) {
            // URL format: full url to external epub file here - let epubjs reader handle parsing etc. in browser
            $link = $book->getFilePath('EPUB', $idData);
            if (!$link) {
                throw new Exception('Unknown link ' . $idData);
            }
        } else {
            $epub = $book->getFilePath('EPUB', $idData);
            if (!$epub || !file_exists($epub)) {
                throw new Exception('Unknown file ' . $epub);
            }
            // URL format: index.php/zipfs/{db}/{data}/{comp} - let epubjs reader retrieve individual components
            $db = $book->getDatabaseId() ?? 0;
            $params = ['db' => $db, 'data' => $idData, 'comp' => 'COMPONENT'];
            $link = $this->getRoute(self::ROUTE_ZIPFS, $params);
            $link = str_replace('COMPONENT', '', $link);
        }
        // Configurable settings (javascript object as text)
        $settings = Config::get('epubjs_reader_settings');

        $dist = $this->getPath(dirname((string) Config::get('assets')) . '/mikespub/epubjs-reader/dist');
        $data = [
            'title'      => htmlspecialchars($book->title),
            'version'    => Config::VERSION,
            'dist'       => $dist,
            'link'       => $link,
            'settings'   => $settings,
        ];

        return Format::template($data, $template);
    }

    /**
     * Summary of getZipContent
     * @param string $filePath
     * @param string $component
     * @return ?string
     */
    public function getZipFileContent($filePath, $component)
    {
        $zip = new ZipArchive();
        $res = $zip->open($filePath, ZipArchive::RDONLY);
        if ($res !== true) {
            throw new Exception('Invalid file ' . $filePath);
        }
        $res = $zip->locateName($component);
        if ($res === false) {
            $zip->close();
            throw new Exception('Unknown component ' . $component);
        }
        $data = $zip->getFromName($component);
        $zip->close();

        return $data;
    }

    /**
     * Summary of sendZipContent
     * @param int $idData
     * @param string $component
     * @param ?int $database
     * @return Response
     */
    public function sendZipContent($idData, $component, $database = null)
    {
        $book = Book::getBookByDataId($idData, $database);
        if (!$book) {
            throw new Exception('Unknown data ' . $idData);
        }
        $filePath = $book->getFilePath('EPUB', $idData);
        if (!$filePath || !file_exists($filePath)) {
            throw new Exception('Unknown file ' . $filePath);
        }

        $data = $this->getZipFileContent($filePath, $component);

        // get mimetype based on $component name alone here
        $mimetype = Response::getMimeType($component);

        // use cache control here
        $this->response->setHeaders($mimetype, 0);
        return $this->response->setContent($data);
    }
}
