<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Calibre\Data;

class FileRenderer
{
    /**
     * Summary of getMimeType
     * @param string $filepath
     * @return string
     */
    public static function getMimeType($filepath)
    {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        if (array_key_exists($extension, Data::$mimetypes)) {
            $mimetype = Data::$mimetypes[$extension];
        } else {
            $mimetype = mime_content_type($filepath);
            if (!$mimetype) {
                $mimetype = 'application/octet-stream';
            }
        }
        return $mimetype;
    }

    /**
     * Summary of sendFile
     * @param string $filepath actual filepath
     * @param ?string $filename with null = no disposition, '' = inline, '...' = attachment filepath
     * @param ?string $mimetype with null = detect from filepath, '...' = actual mimetype for Content-Type
     * @param bool $istmpfile with true if this is a temp file, false otherwise
     * @return void
     */
    public static function sendFile($filepath, $filename = null, $mimetype = null, $istmpfile = false)
    {
        if (empty($mimetype)) {
            $mimetype = static::getMimeType($filepath);
        }

        // @todo do we send cache control for tmpfile too?
        $expires = 60 * 60 * 24 * 14;
        header('Pragma: public');
        header('Cache-Control: max-age=' . $expires);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

        header('Content-Type: ' . $mimetype);
        if (is_null($filename)) {
            // no content disposition
        } elseif (empty($filename)) {
            header('Content-Disposition: inline');
        } else {
            header('Content-Disposition: attachment; filepath="' . basename($filename) . '"');
        }

        // @todo clean up nginx x_accel_redirect
        if (!empty($istmpfile) || empty(Config::get('x_accel_redirect'))) {
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
        } else {
            header(Config::get('x_accel_redirect') . ': ' . $filepath);
        }
    }
}
