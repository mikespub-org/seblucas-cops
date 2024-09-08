<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint to send books by email
 * URL format: sendtomail.php (POST data and email)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 * @deprecated 3.1.0 use index.php/mail instead
 */

$link = str_replace('sendtomail.php', 'index.php/mail', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
