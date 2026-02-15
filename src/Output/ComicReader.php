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
use SebLucas\Cops\Calibre\Metadata;
use SebLucas\Cops\Handlers\ZipFsHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Output\Format;
use ZipArchive;
use InvalidArgumentException;

/**
 * Comic Reader based on ?url=... templates (WIP)
 */
class ComicReader extends EPubReader
{
    public const EXTENSION = 'CBZ';

    /**
     * Summary of getMetadata
     * @param string $filePath
     * @return Metadata|false
     */
    public function getMetadata($filePath)
    {
        try {
            // ComicInfo.xml could be in subdirectory with the images
            $data = $this->getZipFileContent($filePath, 'ComicInfo.xml', ZipArchive::FL_NODIR);
        } catch (InvalidArgumentException) {
            return false;
        }
        if (empty($data)) {
            return false;
        }

        return Metadata::parseComicInfo($data);
    }

    /**
     * Summary of getReader - @todo not used here
     * @param int $idData
     * @param ?string $version
     * @param ?int $database
     * @return string
     */
    public function getReader($idData, $version = null, $database = null)
    {
        $version ??= Config::get('comic_reader', 'comic-reader.html?url=');
        $template = "templates/" . explode('?', $version)[0];
        return $this->getComicReader($idData, $database, $template);
    }

    /**
     * Summary of getComicReader - @todo not used here
     * @param int $idData
     * @param ?int $database
     * @param ?string $template
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getComicReader($idData, $database = null, $template = null)
    {
        $template ??= "templates/comic-reader.html";
        $book = Book::getBookByDataId($idData, $database);
        if (!$book) {
            throw new InvalidArgumentException('Unknown data ' . $idData);
        }
        $this->setHandler(ZipFsHandler::class);

        $link = $this->getDataLink($book, $idData);
        // Configurable settings (javascript object as text)
        $settings = Config::get('comic_reader_settings', '');

        $dist = $this->getPath(dirname((string) Config::get('assets')) . '/mikespub/web-comic-reader/assets');
        $data = [
            'title'      => htmlspecialchars($book->title),
            'version'    => Config::VERSION,
            'dist'       => $dist,
            'link'       => $link,
            'settings'   => $settings,
        ];

        return Format::template($data, $template);
    }
}
