<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use SebLucas\Cops\Routing\UriGenerator;

/**
 * Summary of Session
 * @see https://github.com/symfony/symfony/blob/7.2/src/Symfony/Component/HttpFoundation/Session/Session.php
 */
class Session
{
    public function __construct()
    {
        $this->configure();
    }

    protected function configure(): void
    {
        // avoid overlap with other PHP session names
        $name = Config::get('session_name') ?? 'COPS_SESSID';
        ini_set('session.name', $name);
        // use only session cookies for now - revisit if first-party cookie policy changes
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        $timeout = $this->timeout();
        ini_set('session.cookie_lifetime', $timeout);
        ini_set('session.gc_maxlifetime', $timeout);
        // set cookie path for session cookies here
        $baseUrl = UriGenerator::base();
        if (str_contains($baseUrl, '://')) {
            if (str_starts_with($baseUrl, 'https://')) {
                ini_set('session.cookie_secure', 1);
            } else {
                // allow local insecure cookies for now
                // ini_set('session.cookie_secure', 1);
            }
            $baseUrl = parse_url($baseUrl, PHP_URL_PATH);
        } else {
            // allow local insecure cookies for now
            // ini_set('session.cookie_secure', 1);
        }
        ini_set('session.cookie_path', $baseUrl);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Strict');
    }

    /**
     * Session is used to validate fetching/zipping books or customize COPS, so we use long timeout here
     */
    protected function timeout(): int
    {
        $timeout = Config::get('session_timeout', 365 * 24 * 60 * 60);
        return (int) $timeout;
    }

    /**
     * Set expires or regenerate half-way in the lifetime
     */
    protected function expires(): bool
    {
        if (!$this->has('expires')) {
            $this->set('expires', time() + intdiv($this->timeout(), 2));
            return true;
        }
        $expires = $this->get('expires');
        if ($expires > time()) {
            return false;
        }
        if (!function_exists('\session_regenerate_id')) {
            return false;
        }
        $result = \session_regenerate_id();
        if ($result) {
            $this->set('expires', time() + intdiv($this->timeout(), 2));
        }
        return $result;
    }

    public function start(): bool
    {
        if (!function_exists('\session_status')) {
            return false;
        }
        $status = \session_status();
        $started = match ($status) {
            PHP_SESSION_ACTIVE => true,
            PHP_SESSION_DISABLED => false,
            PHP_SESSION_NONE => \session_start(),
        };
        if ($started) {
            $this->expires();
        }
        return $started;
    }

    public function restore(string $id): bool
    {
        if (!function_exists('\session_id')) {
            return false;
        }
        \session_id($id);
        return $this->start();
    }

    public function regenerate(bool $destroy = false): bool
    {
        if (!function_exists('\session_regenerate_id')) {
            return false;
        }
        $this->start();
        if (PHP_SESSION_ACTIVE !== \session_status()) {
            return false;
        }
        if ($destroy) {
            $_SESSION = [];
        }
        $result = \session_regenerate_id();
        if ($result) {
            $this->set('expires', time() + intdiv($this->timeout(), 2));
        }
        return $result;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $_SESSION);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $_SESSION[$name] ?? $default;
    }

    public function set(string $name, mixed $value): void
    {
        $_SESSION[$name] = $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $_SESSION;
    }

    /**
     * @see https://github.com/symfony/symfony/blob/7.2/src/Symfony/Component/HttpFoundation/Session/Attribute/AttributeBag.php
     */
    public function remove(string $name): mixed
    {
        $retval = null;
        if ($this->has($name)) {
            $retval = $this->get($name);
            unset($_SESSION[$name]);
        }
        return $retval;
    }
}
