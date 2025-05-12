<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Input\Config;

class FileResponse extends Response
{
    protected ?string $filepath;
    protected bool $istmpfile = false;

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
     * Summary of setFile
     * @param string $filepath actual filepath
     * @param bool $istmpfile with true if this is a temp file, false otherwise
     * @return static
     */
    public function setFile($filepath, $istmpfile = false)
    {
        $this->filepath = $filepath;
        $this->istmpfile = $istmpfile;
        // detect mimetype from filepath here if needed
        if (empty($this->mimetype)) {
            $this->mimetype = self::getMimeType($filepath);
        }
        return $this;
    }

    /**
     * Summary of sendFile
     * @return static
     */
    public function sendFile()
    {
        // don't use x_accel_redirect if we deal with a tmpfile here
        if (!empty($this->istmpfile) || empty(Config::get('x_accel_redirect'))) {
            header('Content-Length: ' . filesize($this->filepath));
            readfile($this->filepath);
        } else {
            header(Config::get('x_accel_redirect') . ': ' . $this->filepath);
        }

        return $this;
    }

    /**
     * Summary of send
     * @return static
     */
    public function send(bool $flush = true): static
    {
        if (empty($this->filepath)) {
            return parent::send();
        }
        if ($this->sent) {
            return $this;
        }
        $this->sent = true;

        // @todo do we send cache control for tmpfile too?
        $this->sendHeaders();
        $this->sendFile();

        return $this;
    }
}
