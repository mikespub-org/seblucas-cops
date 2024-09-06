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
    protected int $status = 200;
    protected ?string $mimetype;
    protected ?int $expires;
    protected ?string $filename;

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
     * Summary of __construct
     * @param ?string $mimetype with null = no mimetype, '...' = actual mimetype for Content-Type
     * @param ?int $expires with null = no cache control, 0 = default expiration, > 0 actual expiration
     * @param ?string $filename with null = no disposition, '' = inline, '...' = attachment filename
     * @return void
     */
    public function __construct($mimetype = null, $expires = null, $filename = null)
    {
        $this->setHeaders($mimetype, $expires, $filename);
    }

    /**
     * Summary of setHeaders
     * @param ?string $mimetype with null = no mimetype, '...' = actual mimetype for Content-Type
     * @param ?int $expires with null = no cache control, 0 = default expiration, > 0 actual expiration
     * @param ?string $filename with null = no disposition, '' = inline, '...' = attachment filename
     * @return void
     */
    public function setHeaders($mimetype = null, $expires = null, $filename = null)
    {
        $this->mimetype = $mimetype;
        $this->expires = $expires;
        $this->filename = $filename;
    }

    /**
     * Summary of sendHeaders
     * @return void
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }

        if (is_null($this->expires)) {
            // no cache control
        } elseif (empty($this->expires)) {
            // use default expiration (14 days)
            $this->expires = 60 * 60 * 24 * 14;
        }
        if (!empty($this->expires)) {
            header('Pragma: public');
            header('Cache-Control: max-age=' . $this->expires);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $this->expires) . ' GMT');
        }

        if (!empty($this->mimetype)) {
            header('Content-Type: ' . $this->mimetype);
        }

        if (is_null($this->filename)) {
            // no content disposition
        } elseif (empty($this->filename)) {
            header('Content-Disposition: inline');
        } else {
            header('Content-Disposition: attachment; filename="' . basename($this->filename) . '"');
        }
    }

    /**
     * Summary of sendData
     * @param string $data actual data
     * @return static
     */
    public function sendData($data)
    {
        $this->sendHeaders();

        echo $data;

        return $this;
    }

    /**
     * Summary of notFound
     * @param ?Request $request
     * @return never
     */
    public static function notFound($request = null): never
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
    public static function sendError($request = null, $error = null, $params = ['page' => 'index', 'db' => 0, 'vl' => 0]): never
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
     * @return void
     */
    public static function redirect($location)
    {
        header('Location: ' . $location);
        //exit;
    }
}
