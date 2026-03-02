<?php

/**
 * COPS (Calibre OPDS PHP Server) config loader
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
// 1. load config/default.php with default $config variables
require __DIR__ . '/default.php';  // NOSONAR
// 2. load config/local.php  to override with local $config values
if (php_sapi_name() !== 'cli') {
    if (file_exists(__DIR__ . '/local.php')) {
        try {
            require __DIR__ . '/local.php';  // NOSONAR
        } catch (Throwable $e) {
            echo "Error loading local.php<br>\n";
            echo $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine();
            exit;
        }
    } elseif (file_exists(dirname(__DIR__) . '/config_local.php')) {
        // @deprecated 3.0.0 move config_local.php file to config/local.php
        echo "Please replace 'config_local.php' file with 'config/local.php' (= local.php file in config/ directory)\n";
        echo "See https://github.com/mikespub-org/seblucas-cops#breaking-changes-for-3x-release-php--82\n";
        exit;
    }
}
/** @var array<mixed> $config */

use SebLucas\Cops\Input\Config;

// from here on, we assume that all global $config variables have been loaded
Config::load($config);
date_default_timezone_set(Config::get('default_timezone'));

// override $config after authentication with AuthMiddleware:
// 3. load config/local.{remote_user}.php if available
// 4. then load config/local.{remote_user}.db-{database}.php if available
// 5. or load config/local.db-{database}.php if available

// replace User::verifyLogin with AuthMiddleware - see PR #161
