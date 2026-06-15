<?php

/**
 * COPS (Calibre OPDS PHP Server) HTML main endpoint
 * URL format: index.php?page={page}&...
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework\LegacyFramework;

require_once __DIR__ . '/config/config.php';  // NOSONAR

LegacyFramework::run();
