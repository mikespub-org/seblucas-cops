<?php

use SebLucas\Cops\Input\Config;

require_once __DIR__ . '/../vendor/autoload.php';
require dirname(__DIR__) . '/config_default.php';

$config['calibre_directory'] = __DIR__ . "/BaseWithSomeBooks/";

$config['cops_mail_configuration'] = [
    "smtp.host"     => "smtp.free.fr",
    "smtp.username" => "",
    "smtp.password" => "",
    "smtp.secure"   => "",
    "address.from"  => "cops@slucas.fr",
];

// from here on, we assume that all global $config variables have been loaded
Config::load($config);

// load global functions if necessary
require_once __DIR__ . '/../lib/functions.php';
