<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for monocle epub reader
 * URL format: epubfs.php?data={idData}&comp={component}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 * @deprecated 3.1.0 use index.php/epubfs instead
 */

$link = str_replace('epubfs.php', 'index.php/epubfs', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
