<?php
/**
 * COPS (Calibre OPDS PHP Server) epub reader with monocle
 * URL format: epubreader.php?data={idData}&version={version}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 * @deprecated 3.1.0 use index.php/read instead
 */

$link = str_replace('epubreader.php', 'index.php/read', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
