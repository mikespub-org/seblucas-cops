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

class FileResponse extends Response
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
     * @param string $filepath actual filepath
     * @param bool $istmpfile with true if this is a temp file, false otherwise
     * @return static
     */
    public function sendFile($filepath, $istmpfile = false)
    {
        // detect mimetype from filepath here if needed
        if (empty($this->mimetype)) {
            $this->mimetype = static::getMimeType($filepath);
        }

        // @todo do we send cache control for tmpfile too?
        $this->sendHeaders();

        // @todo clean up nginx x_accel_redirect
        // don't use x_accel_redirect if we deal with a tmpfile here
        if (!empty($istmpfile) || empty(Config::get('x_accel_redirect'))) {
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
        } else {
            header(Config::get('x_accel_redirect') . ': ' . $filepath);
        }

        return $this;
    }
}
