<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Data;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;

class Response
{
    /**
     * Summary of getMimeType
     * @param string $filepath
     * @return ?string mimetype for known extension or existing file, or null if undefined
     */
    public static function getMimeType($filepath)
    {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        if (array_key_exists($extension, Data::$mimetypes)) {
            $mimetype = Data::$mimetypes[$extension];
        } elseif (file_exists($filepath)) {
            $mimetype = mime_content_type($filepath);
            if (!$mimetype) {
                $mimetype = 'application/octet-stream';
            }
        } else {
            // undefined mimetype - do not set Content-Type
            $mimetype = null;
        }
        return $mimetype;
    }

    /**
     * Summary of sendHeaders
     * @param ?string $mimetype with null = no mimetype, '...' = actual mimetype for Content-Type
     * @param ?int $expires with null = no cache control, 0 = default expiration, > 0 actual expiration
     * @param ?string $filename with null = no disposition, '' = inline, '...' = attachment filename
     * @return void
     */
    public static function sendHeaders($mimetype = null, $expires = null, $filename = null)
    {
        if (headers_sent()) {
            return;
        }

        if (is_null($expires)) {
            // no cache control
        } elseif (empty($expires)) {
            // use default expiration (14 days)
            $expires = 60 * 60 * 24 * 14;
        }
        if (!empty($expires)) {
            header('Pragma: public');
            header('Cache-Control: max-age=' . $expires);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        }

        if (!empty($mimetype)) {
            header('Content-Type: ' . $mimetype);
        }

        if (is_null($filename)) {
            // no content disposition
        } elseif (empty($filename)) {
            header('Content-Disposition: inline');
        } else {
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        }
    }

    /**
     * Summary of sendData
     * @param string $data actual data
     * @param ?string $mimetype with null = no mimetype, '...' = actual mimetype for Content-Type
     * @param ?int $expires with null = no cache control, 0 = default expiration, >0 actual expiration
     * @param ?string $filename with null = no disposition, '' = inline, '...' = attachment filename
     * @return void
     */
    public static function sendData($data, $mimetype = null, $expires = null, $filename = null)
    {
        static::sendHeaders($mimetype, $expires, $filename);

        echo $data;
    }

    /**
     * Summary of notFound
     * @param ?Request $request
     * @return never
     */
    public static function notFound($request = null)
    {
        header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1') . ' 404 Not Found');
        header('Status: 404 Not Found');

        $_SERVER['REDIRECT_STATUS'] = 404;
        $data = ['link' => Route::link("index")];
        $template = 'templates/notfound.html';
        echo Format::template($data, $template);
        exit;
    }

    /**
     * Summary of sendError
     * @param ?Request $request
     * @param string|null $error
     * @param array<string, mixed> $params
     * @return never
     */
    public static function sendError($request = null, $error = null, $params = ['page' => 'index', 'db' => 0, 'vl' => 0])
    {
        header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1') . ' 404 Not Found');
        header('Status: 404 Not Found');

        $_SERVER['REDIRECT_STATUS'] = 404;
        $data = ['link' => Route::link("index", null, $params)];
        $data['error'] = htmlspecialchars($error ?? 'Unknown Error');
        $template = 'templates/error.html';
        echo Format::template($data, $template);
        exit;
    }

    /**
     * Summary of redirect
     * @param string $location
     * @return never
     */
    public static function redirect($location)
    {
        header('Location: ' . $location);
        exit;
    }
}
