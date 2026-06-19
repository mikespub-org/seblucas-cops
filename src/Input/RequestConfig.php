<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

/**
 * Load user- and/or database-specific config after request match & update + authentication
 * @todo also load cookie options and/or session values for specific keys?
 */
class RequestConfig
{
    public const PREFIX = Config::PREFIX;

    /**
     * Summary of values
     * @var array<string, mixed>
     */
    protected $values = [];

    /**
     * Summary of load
     * @param array<string, mixed> $values
     * @return void
     */
    public function load($values)
    {
        // add user- and/or database-specific config after AuthMiddleware
        // some phpunit tests re-load the config so we merge here
        $this->values = array_merge($this->values, $values);
    }

    /**
     * Summary of get
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (empty($this->values)) {
            return Config::get($name, $default);
        }
        // first check with prefix here
        if (array_key_exists(self::PREFIX . $name, $this->values)) {
            return $this->values[self::PREFIX . $name];
        }
        if (array_key_exists($name, $this->values)) {
            return $this->values[$name];
        }
        return Config::get($name, $default);
    }

    /**
     * Summary of set
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set($name, $value)
    {
        // always set with prefix here
        $this->values[self::PREFIX . $name] = $value;
    }
}
