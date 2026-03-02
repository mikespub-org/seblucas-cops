<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
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
    public const VERSION = '4.1.0';
    public const ENDPOINT = 'index.php';
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
        // add user- and/or database-specific config after AuthMiddleware
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

    /**
     * Get config/default.php
     * @return array<string, mixed>
     */
    public static function getDefaultConfig()
    {
        $config = [];
        $filepath = dirname(__DIR__, 2) . '/config/default.php';
        require $filepath;  // NOSONAR
        return $config;
    }

    /**
     * Get config/local.php
     * @return array<string, mixed>
     */
    public static function getLocalConfig()
    {
        $config = [];
        $filepath = dirname(__DIR__, 2) . '/config/local.php';
        if (file_exists($filepath)) {
            require $filepath;  // NOSONAR
        }
        return $config;
    }

    /**
     * Get config/local.{$username}.php
     * @param string $username
     * @return array<string, mixed>
     */
    public static function getUserConfig($username)
    {
        $config = [];
        // Clean username, only allow a-z, A-Z, 0-9, -_ chars
        $username = preg_replace('/[^a-zA-Z0-9_-]/', '', $username);
        if (empty($username)) {
            return $config;
        }
        $filepath = dirname(__DIR__, 2) . '/config/local.' . $username . '.php';
        if (file_exists($filepath)) {
            require $filepath;  // NOSONAR
        }
        return $config;
    }

    /**
     * Get config/local.{$username}.db-{$database}.php or config/local.db-{$database}.php
     * @param ?int $database
     * @param ?string $username
     * @return array<string, mixed>
     */
    public static function getDatabaseConfig($database, $username = null)
    {
        $config = [];
        $database ??= 0;
        $username ??= '';
        // Clean username, only allow a-z, A-Z, 0-9, -_ chars
        $username = preg_replace('/[^a-zA-Z0-9_-]/', '', $username);
        if (!empty($username)) {
            $filepath = dirname(__DIR__, 2) . '/config/local.' . $username . '.db-' . $database . '.php';
            if (file_exists($filepath)) {
                require $filepath;  // NOSONAR
                // username-specific database setup
                return $config;
            }
            // common database setup across users
        }
        $filepath = dirname(__DIR__, 2) . '/config/local.db-' . $database . '.php';
        if (file_exists($filepath)) {
            require $filepath;  // NOSONAR
        }
        return $config;
    }

    /**
     * List config/local.*.php files
     * @return array<string>
     */
    public static function listLocalConfigFiles()
    {
        $files = [];
        $dirpath = dirname(__DIR__, 2) . '/config';
        foreach (glob($dirpath . '/local.*.php') as $filename) {
            $files[] = basename($filename);
        }
        return $files;
    }
}
