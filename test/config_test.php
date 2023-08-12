<?php

use SebLucas\Cops\Input\Config;

require(dirname(__FILE__) . "/../config_default.php");
$config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";

$config['cops_mail_configuration'] = [ "smtp.host"     => "smtp.free.fr",
                                                "smtp.username" => "",
                                                "smtp.password" => "",
                                                "smtp.secure"   => "",
                                                "address.from"  => "cops@slucas.fr",
                                                ];

// from here on, we assume that all global $config variables have been loaded
Config::load($config);
