<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for REST API
 * URL format: restapi.php{/route}?db={db} etc.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 * @deprecated 3.1.0 use index.php/restapi instead
 */

$link = str_replace('restapi.php', 'index.php/restapi', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
