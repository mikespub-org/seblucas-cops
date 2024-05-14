<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for monocle epub reader
 * URL format: epubfs.php?data={idData}&comp={component}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

Framework::run('epubfs');
