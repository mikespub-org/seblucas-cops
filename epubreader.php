<?php
/**
 * COPS (Calibre OPDS PHP Server) epub reader with monocle
 * URL format: epubreader.php?data={idData}&version={version}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

Framework::run('read');
