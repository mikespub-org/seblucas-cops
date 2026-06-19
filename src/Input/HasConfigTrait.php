<?php

namespace SebLucas\Cops\Input;

/**
 * Trait for classes that use Config::get() or RequestConfig->get()
 * for request-dependent config based on username and/or database
 */
trait HasConfigTrait
{
    protected ?RequestConfig $config = null;

    /**
     * Summary of config
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function config($name, $default = null)
    {
        if ($this->config !== null) {
            return $this->config->get($name, $default);
        }

        if (method_exists($this, 'getContext')) {
            $context = $this->getContext();
            if ($context instanceof RequestContext) {
                return $context->getConfig()->get($name, $default);
            }
        }

        return Config::get($name, $default);
    }

    public function setConfig(RequestConfig $config): void
    {
        $this->config = $config;
    }

    public function getConfig(): ?RequestConfig
    {
        return $this->config;
    }

    /**
     * Summary of configFrom - same as Config::getFrom()
     * @param mixed $config
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected static function configFrom($config, string $name, mixed $default = null): mixed
    {
        if (is_object($config) && method_exists($config, 'get')) {
            return $config->get($name, $default);
        }

        return Config::get($name, $default);
    }
}
