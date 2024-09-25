<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for calres:// resource
 * URL format: calres.php/{db}/{alg}/{digest} with {hash} = {alg}:{digest}
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 * @deprecated 3.1.0 use index.php/calres instead
 */

$link = str_replace('calres.php', 'index.php/calres', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
