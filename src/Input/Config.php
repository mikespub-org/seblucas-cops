<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use Exception;

/**
 * Summary of Config
 */
class Config
{
    public const VERSION = '3.5.7';
    public const ENDPOINT = [
        "html" => "index.php",
        "feed" => "feed.php",
        "json" => "getJSON.php",
        "fetch" => "fetch.php",
        "read" => "epubreader.php",
        "epubfs" => "epubfs.php",
        "restapi" => "restapi.php",
        "check" => "checkconfig.php",
        "opds" => "opds.php",
        "loader" => "loader.php",
        "zipper" => "zipper.php",
        "calres" => "calres.php",
        "zipfs" => "zipfs.php",
        "mail" => "sendtomail.php",
        "graphql" => "graphql.php",
        "tables" => "tables.php",
    ];
    protected const PREFIX = 'cops_';

    /**
     * Summary of values
     * @var array<string, mixed>
     */
    protected static $values = [];

    /**
     * Summary of load
     * @param array<string, mixed> $values
     * @return void
     */
    public static function load($values)
    {
        // some phpunit tests re-load the config so we merge here
        self::$values = array_merge(self::$values, $values);
    }

    /**
     * Summary of get
     * @param string $name
     * @param mixed $default
     * @throws \Exception
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if (empty(self::$values)) {
            throw new Exception('Config was not loaded correctly in config/config.php or config/test.php');
        }
        if (array_key_exists(self::PREFIX . $name, self::$values)) {
            return self::$values[self::PREFIX . $name];
        }
        return self::$values[$name] ?? $default;
    }

    /**
     * Summary of set
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public static function set($name, $value)
    {
        self::$values[self::PREFIX . $name] = $value;
    }

    /**
     * Summary of dump
     * @return array<string, mixed>
     */
    public static function dump()
    {
        return self::$values;
    }
}
