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

class FileRenderer extends Response
{
    /**
     * Summary of getTempFile
     * @param string $extension
     * @return string
     */
    public static function getTempFile($extension = '')
    {
        $tmpdir = sys_get_temp_dir();
        $tmpfile = tempnam($tmpdir, 'COPS');
        if (empty($extension)) {
            return $tmpfile;
        }
        rename($tmpfile, $tmpfile . '.' . $extension);
        return $tmpfile . '.' . $extension;
    }

    /**
     * Summary of sendFile
     * @todo align params order with Response::sendData() if/when it makes sense
     * @param string $filepath actual filepath
     * @param ?string $filename with null = no disposition, '' = inline, '...' = attachment filename
     * @param ?string $mimetype with null = detect from filepath, '...' = actual mimetype for Content-Type
     * @param bool $istmpfile with true if this is a temp file, false otherwise
     * @param ?int $expires use default expiration for files
     * @return void
     */
    public static function sendFile($filepath, $filename = null, $mimetype = null, $istmpfile = false, $expires = 0)
    {
        // detect mimetype from filepath here if needed
        if (empty($mimetype)) {
            $mimetype = static::getMimeType($filepath);
        }

        // @todo do we send cache control for tmpfile too?
        static::sendHeaders($mimetype, $expires, $filename);

        // @todo clean up nginx x_accel_redirect
        if (!empty($istmpfile) || empty(Config::get('x_accel_redirect'))) {
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
        } else {
            header(Config::get('x_accel_redirect') . ': ' . $filepath);
        }
    }
}
