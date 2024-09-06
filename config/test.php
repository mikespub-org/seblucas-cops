<?php

use SebLucas\Cops\Framework;
use SebLucas\Cops\Input\Config;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/default.php';

//$config['calibre_directory'] = dirname(__DIR__) . "/tests/BaseWithSomeBooks/";
$config['calibre_directory'] = "./tests/BaseWithSomeBooks/";

$config['cops_mail_configuration'] = [
    "smtp.host"     => "smtp.free.fr",
    "smtp.username" => "",
    "smtp.password" => "",
    "smtp.secure"   => "",
    "address.from"  => "cops@slucas.fr",
];

// from here on, we assume that all global $config variables have been loaded
Config::load($config);

// initialize framework with routes etc. for tests
Framework::init();
