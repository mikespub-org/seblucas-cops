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
     * Summary of isComicFile
     * @param string $path
     * @return bool
     */
    public static function isComicFile($path)
    {
        $format = strtoupper(pathinfo($path, PATHINFO_EXTENSION));
        return $format == static::EXTENSION;
    }

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

    /**
     * Summary of getZipContent
     * @param string $filePath
     * @param string $component
     * @param int $flags ignore directory for ComicReader
     * @throws \InvalidArgumentException
     * @return string|bool
     */
    public function getZipFileContent($filePath, $component, $flags = 0)
    {
        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);
        if ($result !== true) {
            throw new InvalidArgumentException('Invalid file ' . basename($filePath));
        }
        $cover = false;
        $thumb = null;
        $index = $zip->locateName($component, $flags);
        if ($index === false) {
            if ($component == 'cover.jpg' && static::isComicFile($filePath)) {
                $cover = true;
                $thumb = $this->request->get('size');
                $index = $this->findCoverImage($zip);
            }
            if ($index === false) {
                $zip->close();
                throw new InvalidArgumentException('Unknown component ' . $component);
            }
        }
        $data = $zip->getFromIndex($index);
        $zip->close();

        if ($cover && $thumb) {
            // @todo resize image for thumbnail
        }

        return $data;
    }

    /**
     * Summary of findCoverImage
     * @param ZipArchive $zip
     * @return int|bool
     */
    public function findCoverImage($zip)
    {
        // ... find FrontCover in metadata or use first image
        $index = false;
        $images = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (str_starts_with($filename, '__MACOSX')) {
                continue;
            }
            if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/', $filename)) {
                $images[] = $filename;
            }
        }
        if (!empty($images)) {
            natsort($images);
            $index = $zip->locateName($images[0]);
        }
        return $index;
    }
}
