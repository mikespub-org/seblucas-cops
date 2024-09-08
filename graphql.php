<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for GraphQL (dev only)
 * URL format: graphql.php?db={db} etc.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 * @deprecated 3.1.0 use index.php/graphql instead
 */

$link = str_replace('graphql.php', 'index.php/graphql', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
