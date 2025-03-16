<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

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
        // session is used to validate fetching/zipping books or configure COPS, so we use long timeout here
        $timeout = Config::get('session_timeout') ?? (365 * 24 * 60 * 60);
        ini_set('session.cookie_lifetime', $timeout);
        ini_set('session.gc_maxlifetime', $timeout);
    }

    public function start(): bool
    {
        $status = session_status();
        return match ($status) {
            PHP_SESSION_ACTIVE => true,
            PHP_SESSION_DISABLED => false,
            PHP_SESSION_NONE => session_start(),
        };
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
