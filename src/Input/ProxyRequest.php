<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use SebLucas\Cops\Input\Config;

/**
 * Summary of ProxyRequest
 */
class ProxyRequest
{
    public const SYMFONY_REQUEST = '\Symfony\Component\HttpFoundation\Request';

    /** @var ?\Symfony\Component\HttpFoundation\Request */
    public static $proxyRequest = null;

    /**
     * Summary of getProxyBaseUrl
     * @return string
     */
    public static function getProxyBaseUrl()
    {
        // use scheme and host + base path here to apply potential forwarded values
        return self::$proxyRequest->getSchemeAndHttpHost() . self::$proxyRequest->getBasePath();
    }

    /**
     * Check if we have trusted proxies defined in config/local.php
     * @see https://github.com/symfony/symfony/blob/7.1/src/Symfony/Component/HttpKernel/Kernel.php#L741
     * @return bool
     */
    public static function hasTrustedProxies()
    {
        $class = self::SYMFONY_REQUEST;
        if (!class_exists($class)) {
            return false;
        }
        if (empty(Config::get('trusted_proxies')) || empty(Config::get('trusted_headers'))) {
            return false;
        }
        if (!isset(self::$proxyRequest)) {
            $proxies = Config::get('trusted_proxies');
            $headers = Config::get('trusted_headers');
            $class::setTrustedProxies(is_array($proxies) ? $proxies : array_map('trim', explode(',', (string) $proxies)), self::resolveTrustedHeaders($headers));
            self::$proxyRequest = $class::createFromGlobals();
        }
        return true;
    }

    /**
     * Convert trusted headers into bit field of Request::HEADER_*
     * @see https://github.com/symfony/symfony/blob/7.1/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/FrameworkExtension.php#L3054
     * @param string[] $headers
     * @return int
     */
    protected static function resolveTrustedHeaders(array $headers)
    {
        $class = self::SYMFONY_REQUEST;
        $trustedHeaders = 0;

        foreach ($headers as $h) {
            $trustedHeaders |= match ($h) {
                'forwarded' => $class::HEADER_FORWARDED,
                'x-forwarded-for' => $class::HEADER_X_FORWARDED_FOR,
                'x-forwarded-host' => $class::HEADER_X_FORWARDED_HOST,
                'x-forwarded-proto' => $class::HEADER_X_FORWARDED_PROTO,
                'x-forwarded-port' => $class::HEADER_X_FORWARDED_PORT,
                'x-forwarded-prefix' => $class::HEADER_X_FORWARDED_PREFIX,
                default => 0,
            };
        }

        return $trustedHeaders;
    }
}
