<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\HasConfigTrait;
use SebLucas\Cops\Input\RequestConfig;
use SebLucas\Cops\Language\Normalizer;
use Exception;
use Pdo\Sqlite;

/**
 * Move actual database methods from static Database methods
 * to DatabaseConnection instance methods for each sqlite db
 */
class DatabaseConnection
{
    use HasConfigTrait;

    protected string $dbFileName;
    protected ?int $database;
    protected ?Sqlite $db = null;
    protected bool $functions = false;

    public function __construct(string $dbFileName, ?RequestConfig $config = null, ?int $database = null)
    {
        $this->dbFileName = $dbFileName;
        $this->config = $config;
        $this->database = $database;
    }

    public function getDb(): Sqlite
    {
        if ($this->db === null) {
            if (!is_readable($this->dbFileName)) {
                throw new Exception(sprintf("Database <%s> not found or not readable.", $this->database));
            }
            $this->db = new Sqlite('sqlite:' . $this->dbFileName);
            $this->createSqliteFunctions();
        }
        return $this->db;
    }

    public function getDbFileName(): string
    {
        return $this->dbFileName;
    }

    public function clear(): void
    {
        $this->db = null;
        $this->functions = false;
    }

    public function addSqliteFunctions(): void
    {
        if ($this->functions) {
            return;
        }
        $db = $this->getDb();
        if (!in_array('series', $this->config('calibre_categories_using_hierarchy', []))) {
            $db->createFunction('title_sort', fn($s) => Normalizer::getTitleSort($s), 1);
        }
        $db->createFunction('books_list_filter', fn($s) => 1, 1);
        $db->createAggregate('concat', function ($context, $string) {
            $context ??= [];
            $context[] = $string;
            return $context;
        }, function ($context) {
            $context ??= [];
            return implode(',', $context);
        }, 1);
        $db->createAggregate('sortconcat', function ($context, $id, $string) {
            $context ??= [];
            $context[$id] = $string;
            return $context;
        }, function ($context) {
            $context ??= [];
            sort($context);
            return implode(',', $context);
        }, 2);
        $this->functions = true;
    }

    protected function createSqliteFunctions(): void
    {
        $db = $this->db;
        if (!$db) {
            return;
        }

        if (Normalizer::useNormAndUp($this->config)) {
            $db->createFunction('normAndUp', fn($s) => Normalizer::normAndUp($s), 1);
        }
        if (in_array('series', $this->config('calibre_categories_using_hierarchy', []))) {
            $db->createFunction('title_sort', fn($s) => Normalizer::getTitleSort($s), 1);
        }

        $sql = 'SELECT sqlite_version() as version;';
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($post = $stmt->fetchObject()) {
            if ($post->version >= '3.38') {
                return;
            }
        }

        $db->createFunction('unixepoch', function ($s) {
            if (!empty($s) && $s === 'subsec') {
                return microtime(true);
            }
            return time();
        }, 1);
    }

    /**
     * Attach an sqlite database to existing db connection
     */
    public function attachDatabase(string $dbFileName, string $attachDatabase): void
    {
        $db = $this->getDb();
        try {
            $sql = "ATTACH DATABASE '{$dbFileName}' AS {$attachDatabase};";
            $stmt = $db->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            $error = sprintf('Cannot attach %s database [%s]: %s', $attachDatabase, $dbFileName, $e->getMessage());
            throw new Exception($error);
        }
    }

    /**
     * Get list of databases (open or attach) from SQLite
     * @return array<string, mixed>
     */
    public function getDatabaseList(): array
    {
        $sql = 'select * from pragma_database_list;';
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();
        $databases = [];
        while ($post = $stmt->fetchObject()) {
            $databases[$post->name] = (array) $post;
        }
        return $databases;
    }

}
