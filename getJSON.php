<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for JSON ajax requests
 * URL format: getJSON.php?page={page}&...
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 * @deprecated 3.1.0 use index.php instead (with Accept: application/json or X-Requested-With: XMLHttpRequest)
 */

$link = str_replace('getJSON.php', 'index.php', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
