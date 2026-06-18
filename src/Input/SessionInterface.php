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
 * Summary of SessionInterface
 * @see https://github.com/symfony/symfony/blob/7.2/src/Symfony/Component/HttpFoundation/Session/SessionInterface.php
 */
interface SessionInterface
{
    /**
     * Start new session or resume existing session
     */
    public function start(): bool;

    /**
     * Get session id
     */
    public function getId(): string;

    /**
     * Set session id and restore session (start)
     */
    public function restore(string $id): bool;

    /**
     * Regenerate session id and optionally destroy values
     */
    public function regenerate(bool $destroy = false): bool;

    public function has(string $name): bool;

    public function get(string $name, mixed $default = null): mixed;

    public function set(string $name, mixed $value): void;

    /**
     * Get all session values
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * Remove session value by name
     */
    public function remove(string $name): mixed;
}
