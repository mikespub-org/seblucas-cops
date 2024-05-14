<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for OPDS 1.2 feed
 * URL format: feed.php?page={page}&query={query}&...
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

Framework::run('feed');
