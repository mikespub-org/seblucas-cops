<?php
/**
 * COPS (Calibre OPDS PHP Server) download all books for a page, series or author by format
 * URL format: download.php?page={page}&type={format}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

$request = Framework::getRequest('download');

$handler = Framework::getHandler('download');
$handler->handle($request);
