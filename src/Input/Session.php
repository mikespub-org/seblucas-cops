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
        // ...
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
