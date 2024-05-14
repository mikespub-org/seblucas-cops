<?php
/**
 * COPS (Calibre OPDS PHP Server) download all books for a page, series or author by format (epub, mobi, any, ...)
 * URL format: zipper.php?page={page}&type={type}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

Framework::run('zipper');
