<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\RequestConfig;
use SebLucas\Cops\Language\Normalizer;
use Pdo\Sqlite;

/**
 * Replace static Database method calls using $database and $config
 * with DatabaseContext instance method calls without them to simplify
 */
class DatabaseContext
{
    protected ?int $database;
    protected ?RequestConfig $config;
    protected ?bool $isMultiple = null;
    protected ?string $dbDirectory = null;
    protected ?string $dbFileName = null;
    protected ?string $dbName = null;
    protected ?int $userVersion = null;
    protected ?DatabaseConnection $dbConn = null;

    public function __construct(?int $database = null, ?RequestConfig $config = null)
    {
        $this->database = $database;
        $this->config = $config;
    }

    /**
     * Summary of getDatabase
     */
    public function getDatabase(): ?int
    {
        return $this->database;
    }

    /**
     * Summary of getConfig
     */
    public function getConfig(): ?RequestConfig
    {
        return $this->config;
    }

    /**
     * Summary of setDatabase
     */
    public function setDatabase(?int $database): void
    {
        $this->database = $database;
        $this->clear();
    }

    /**
     * Summary of clear
     */
    public function clear(): void
    {
        $this->isMultiple = null;
        $this->dbDirectory = null;
        $this->dbFileName = null;
        $this->dbName = null;
        $this->userVersion = null;
        if (isset($this->dbConn)) {
            $this->dbConn->clear();
            $this->dbConn = null;
        }
    }

    /**
     * Summary of isMultipleDatabaseEnabled
     */
    public function isMultipleDatabaseEnabled(): bool
    {
        if (!isset($this->isMultiple)) {
            $this->isMultiple = is_array(Config::getFrom($this->config, 'calibre_directory', null));
        }
        return $this->isMultiple;
    }

    /**
     * Summary of findDatabaseId
     */
    public function findDatabaseId(string $dbName): ?int
    {
        if (!$this->isMultipleDatabaseEnabled()) {
            return null;
        }
        $array = array_keys(Config::getFrom($this->config, 'calibre_directory', []));
        $database = array_search($dbName, $array);
        if ($database === false || !is_numeric($database)) {
            return null;
        }
        return (int) $database;
    }

    /**
     * Summary of useAbsolutePath
     */
    public function useAbsolutePath(): bool
    {
        $path = $this->getDbDirectory();
        return preg_match('/^\//', $path) // Linux /
               || preg_match('/^\w\:/', $path); // Windows X:
    }

    /**
     * Summary of noDatabaseSelected
     */
    public function noDatabaseSelected(): bool
    {
        return $this->isMultipleDatabaseEnabled() && is_null($this->database);
    }

    /**
     * Summary of getDbList
     * @return array<string, string>
     */
    public function getDbList(): array
    {
        if ($this->isMultipleDatabaseEnabled()) {
            return Config::getFrom($this->config, 'calibre_directory', []);
        } else {
            return ["" => Config::getFrom($this->config, 'calibre_directory', '')];
        }
    }

    /**
     * Summary of getDbNameList
     * @return array<string>
     */
    public function getDbNameList(): array
    {
        if ($this->isMultipleDatabaseEnabled()) {
            return array_keys(Config::getFrom($this->config, 'calibre_directory', []));
        }
        return [""];
    }

    /**
     * Summary of getDbName
     */
    public function getDbName(): string
    {
        if (!isset($this->dbName)) {
            if ($this->isMultipleDatabaseEnabled()) {
                $database = $this->database ?? 0;
                $array = array_keys(Config::getFrom($this->config, 'calibre_directory', []));
                $this->dbName = $array[$database];
            } else {
                $this->dbName = "";
            }
        }
        return $this->dbName;
    }

    /**
     * Summary of getDbDirectory
     * @throws \Exception
     */
    public function getDbDirectory(): string
    {
        if (!isset($this->dbDirectory)) {
            if ($this->isMultipleDatabaseEnabled()) {
                $database = $this->database ?? 0;
                $array = array_values(Config::getFrom($this->config, 'calibre_directory', []));
                if ($database > count($array) - 1) {
                    throw new \Exception("Database <{$database}> not found.");
                }
                $this->dbDirectory = $array[$database];
            } else {
                $this->dbDirectory = Config::getFrom($this->config, 'calibre_directory', '');
            }
        }
        return $this->dbDirectory;
    }

    /**
     * Summary of getDbFileName
     */
    public function getDbFileName(): string
    {
        if (!isset($this->dbFileName)) {
            $this->dbFileName = $this->getDbDirectory() . Database::CALIBRE_DB_FILE;
        }
        return $this->dbFileName;
    }

    /**
     * Summary of getLastModified
     */
    public function getLastModified(): string
    {
        $fileName = $this->getDbFileName();
        return date(DATE_ATOM, filemtime($fileName));
    }

    // -DC- Add image directory
    /**
     * Summary of getImgDirectory
     */
    public function getImgDirectory(): string
    {
        if ($this->isMultipleDatabaseEnabled()) {
            $database = $this->database ?? 0;
            $array = array_values(Config::getFrom($this->config, 'image_directory', []));
            if ($database > count($array) - 1) {
                throw new \Exception("Image directory <{$database}> not found.");
            }
            return $array[$database];
        }
        return Config::getFrom($this->config, 'image_directory', '');
    }

    /**
     * Summary of getConnection
     */
    public function getConnection(): DatabaseConnection
    {
        if (!isset($this->dbConn)) {
            $this->dbConn = Database::getContextConnection($this);
        }
        return $this->dbConn;
    }

    /**
     * Summary of getDb
     */
    public function getDb(): Sqlite
    {
        return $this->getConnection()->getDb();
    }

    /**
     * Summary of addSqliteFunctions
     */
    public function addSqliteFunctions(): void
    {
        $this->getConnection()->addSqliteFunctions();
    }

    /**
     * Summary of querySingle
     */
    public function querySingle(string $query): mixed
    {
        return $this->getConnection()->querySingle($query);
    }

    /**
     * Summary of query
     * @param array<mixed> $params
     */
    public function query(string $query, array $params = []): \PDOStatement
    {
        return $this->getConnection()->query($query, $params);
    }

    /**
     * Summary of queryTotal
     * @param array<mixed> $params
     * @return array{0: integer, 1: \PDOStatement}
     */
    public function queryTotal(string $query, string $columns, string $filter, array $params, int $n, ?int $numberPerPage = null): array
    {
        if (Normalizer::useNormAndUp($this->config)) {
            $query = str_replace("upper", "normAndUp", $query);
            $columns = str_replace("upper", "normAndUp", $columns);
        }

        if (is_null($numberPerPage)) {
            $numberPerPage = Config::getFrom($this->config, 'max_item_per_page');
        }

        return $this->getConnection()->queryTotal($query, $columns, $filter, $params, $n, $numberPerPage);
    }

    /**
     * Summary of queryFilter
     * @param array<mixed> $params
     */
    public function queryFilter(string $query, string $columns, string $filter, array $params, int $n, ?int $numberPerPage = null): \PDOStatement
    {
        if (Normalizer::useNormAndUp($this->config)) {
            $query = str_replace("upper", "normAndUp", $query);
            $columns = str_replace("upper", "normAndUp", $columns);
        }

        if (is_null($numberPerPage)) {
            $numberPerPage = Config::getFrom($this->config, 'max_item_per_page');
        }

        return $this->getConnection()->queryFilter($query, $columns, $filter, $params, $n, $numberPerPage);
    }

    /**
     * Summary of countFilter
     * @param array<mixed> $params
     */
    public function countFilter(string $query, string $columns = 'count(*)', string $filter = '', array $params = []): int
    {
        return $this->getConnection()->countFilter($query, $columns, $filter, $params);
    }

    /**
     * Summary of getDbSchema
     * @param ?string $type get table or view entries
     * @return array<mixed>
     */
    public function getDbSchema(?string $type = null): array
    {
        return $this->getConnection()->getDbSchema($type);
    }

    /**
     * Summary of getTableInfo
     * @param string $name table or view name
     * @return array<mixed>
     */
    public function getTableInfo(string $name = 'books'): array
    {
        return $this->getConnection()->getTableInfo($name);
    }

    /**
     * Summary of getUserVersion
     */
    public function getUserVersion(): int
    {
        if (!isset($this->userVersion)) {
            $this->userVersion = $this->getConnection()->getUserVersion();
        }
        return $this->userVersion;
    }

    /**
     * Get list of databases (open or attach) from SQLite
     * @return array<mixed>
     */
    public function getDatabaseList(): array
    {
        return $this->getConnection()->getDatabaseList();
    }

    /**
     * Summary of getNotesDb
     */
    public function getNotesDb(): ?Sqlite
    {
        return $this->getConnection()->getNotesDb();
    }
}
