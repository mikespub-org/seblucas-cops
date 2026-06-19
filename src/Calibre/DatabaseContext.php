<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\RequestConfig;
use Pdo\Sqlite;

/**
 * Replace static Database method calls using $database and $config
 * with DatabaseContext instance method calls without them to simplify
 */
class DatabaseContext
{
    protected ?int $database;
    protected ?RequestConfig $config;

    public function __construct(?int $database = null, ?RequestConfig $config = null)
    {
        $this->database = $database;
        $this->config = $config;
    }

    public function getDatabase(): ?int
    {
        return $this->database;
    }

    public function getConfig(): ?RequestConfig
    {
        return $this->config;
    }

    public function getConnection(): DatabaseConnection
    {
        return Database::getConnection($this->database, $this->config);
    }

    public function getDb(): Sqlite
    {
        return $this->getConnection()->getDb();
    }

    public function addSqliteFunctions(): void
    {
        $this->getConnection()->addSqliteFunctions();
    }

    /**
     * @param array<mixed> $params
     */
    public function query(string $query, array $params = []): \PDOStatement
    {
        return Database::query($query, $params, $this->database, $this->config);
    }

    public function querySingle(string $query): mixed
    {
        return Database::querySingle($query, $this->database, $this->config);
    }

    /**
     * @param array<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!method_exists(Database::class, $name)) {
            throw new \BadMethodCallException("Method {$name} does not exist on Database");
        }

        $reflection = new \ReflectionMethod(Database::class, $name);
        $params = $reflection->getParameters();
        $paramCount = count($params);

        if ($paramCount === 0) {
            return forward_static_call_array([Database::class, $name], $arguments);
        }

        $hasConfigParam = $params[$paramCount - 1]->getName() === 'config';
        $hasDatabaseParam = $paramCount > 1 && $params[$paramCount - 2]->getName() === 'database';

        if ($hasConfigParam) {
            $requiredArguments = $paramCount;
            if ($hasDatabaseParam && count($arguments) < $requiredArguments - 1) {
                $arguments[] = $this->database;
            }
            if (count($arguments) < $requiredArguments) {
                $arguments[] = $this->config;
            }
        }

        return forward_static_call_array([Database::class, $name], $arguments);
    }
}
