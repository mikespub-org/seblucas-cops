<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint to send books by email
 * URL format: sendtomail.php (POST data and email)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

Framework::run('mail');
