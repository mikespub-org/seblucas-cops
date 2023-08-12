<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
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
    public const VERSION = '1.4.1';
    public const ENDPOINT = [
        "index" => "index.php",
        "feed" => "feed.php",
        "json" => "getJSON.php",
        "fetch" => "fetch.php",
        "read" => "epubreader.php",
        "epubfs" => "epubfs.php",
        "restapi" => "restapi.php",
        "check" => "checkconfig.php",
        "opds" => "opds.php",
    ];
    /**
     * Summary of values
     * @var array<string, mixed>
     */
    protected static $values = [];

    /**
     * Summary of load
     * @param array $values
     * @return void
     */
    public static function load($values)
    {
        self::$values = $values;
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
            throw new Exception('Config was not loaded correctly in config.php');
        }
        if (array_key_exists('cops_' . $name, self::$values)) {
            return self::$values['cops_' . $name];
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
        self::$values['cops_' . $name] = $value;
    }
}
