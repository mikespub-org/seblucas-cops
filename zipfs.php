<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for epubjs-reader
 * URL format: zipfs.php/{db}/{idData}/{component}
 *
 * @author mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

Framework::run('zipfs');
