<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for calres:// resource
 * URL format: calres.php/{db}/{alg}/{digest} with {hash} = {alg}:{digest}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

$link = str_replace('calres.php', 'index.php/calres', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
