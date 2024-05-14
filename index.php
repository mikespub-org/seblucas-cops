<?php
/**
 * COPS (Calibre OPDS PHP Server) HTML main endpoint
 * URL format: index.php?page={page}&...
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

// @todo handle 'json' routes correctly - see util.js
Framework::run('index');
