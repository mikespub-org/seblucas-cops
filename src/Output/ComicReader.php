<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Metadata;
use SebLucas\Cops\Handlers\ZipFsHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Output\Format;
use ZipArchive;
use InvalidArgumentException;

/**
 * Comic Reader based on ?url=... templates (WIP)
 *
 * Special components:
 * - cover.jpg find cover image
 * - index.json list image files
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
        $this->findBookData($idData, $database);
        $this->setHandler(ZipFsHandler::class);

        $link = $this->getDataLink();
        // Configurable settings (javascript object as text)
        $settings = Config::get('comic_reader_settings', '');

        $dist = $this->getPath(dirname((string) Config::get('assets')) . '/mikespub/web-comic-reader/assets');
        $data = [
            'title'      => htmlspecialchars($this->book->title),
            'version'    => Config::VERSION,
            'dist'       => $dist,
            'link'       => $link,
            'settings'   => $settings,
        ];

        return Format::template($data, $template);
    }

    /**
     * Summary of listContentFiles
     * @param ZipArchive $zip
     * @param string $filePath
     * @return string
     */
    public function listContentFiles($zip, $filePath)
    {
        $images = $this->getImageFiles($zip);
        $zip->close();
        // get data ready for consumption
        $datalink = $this->getDataLink();
        $data = [];
        foreach ($images as $image) {
            $data[] = [
                'name' => htmlspecialchars($image),
                'type' => 'image',
                'href' => $datalink . rawurlencode($image),
            ];
        }
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Summary of getImageFiles
     * @param ZipArchive $zip
     * @return array<string>
     */
    public function getImageFiles($zip)
    {
        $images = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (str_starts_with($filename, '__MACOSX')) {
                continue;
            }
            if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $filename)) {
                $images[] = $filename;
            }
        }
        natcasesort($images);
        return $images;
    }

    /**
     * Summary of sendCoverImage
     * @param ZipArchive $zip
     * @param string $filePath
     * @throws \InvalidArgumentException
     * @return Response|string|bool
     */
    public function sendCoverImage($zip, $filePath)
    {
        $index = $this->findCoverImage($zip);
        if ($index === false) {
            $zip->close();
            throw new InvalidArgumentException('Unknown cover for ' . basename($filePath));
        }

        $thumb = $this->request->get('size');
        if (!empty($thumb)) {
            $this->request->set('thumb', $thumb);
        }

        $image = new ImageResponse();
        $image->setRequest($this->request);
        // set fake uuid for cover cache
        $mtime = filemtime($filePath);
        $name = (string) $index . '-' . $filePath;
        $uuid = md5((string) $mtime . '-' . $name);
        $image->setSource($uuid, $name, $mtime);

        $cacheFile = $image->checkCache();
        // already cached or not modified
        if ($cacheFile instanceof Response) {
            $zip->close();
            return $cacheFile;
        }

        // get image data from zip file
        $data = $zip->getFromIndex($index);
        $zip->close();

        // resize image data for thumbnail
        return $image->getThumbFromData($data, $cacheFile);
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
        $images = $this->getImageFiles($zip);
        if (!empty($images)) {
            $index = $zip->locateName($images[0]);
        }
        return $index;
    }
}
