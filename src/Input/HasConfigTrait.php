<?php

namespace SebLucas\Cops\Input;

/**
 * Trait for classes that use Config::get()
 * @todo use request-dependent config based on username, database and/or cookie options
 */
trait HasConfigTrait
{
    private Config $config;

    /**
     * Summary of config
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function config($name, $default = null)
    {
        return Config::get($name, $default);
    }
}
