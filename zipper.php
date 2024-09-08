<?php
/**
 * COPS (Calibre OPDS PHP Server) download all books for a page, series or author by format (epub, mobi, any, ...)
 * URL format: zipper.php?page={page}&type={type}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 * @deprecated 3.1.0 use index.php/zipper instead
 */

$link = str_replace('zipper.php', 'index.php/zipper', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
