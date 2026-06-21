<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Database;

use SebLucas\Cops\Input\RequestConfig;

/**
 * Keep Database static methods as legacy + provide mapping
 * from $database + $config to actual database connection
 */
class Database
{
    public const KEEP_STATS = false;
    public const CALIBRE_DB_FILE = 'metadata.db';
    public const NOTES_DIR_NAME = '.calnotes';
    public const NOTES_DB_FILE = 'notes.db';
    public const NOTES_DB_NAME = 'notes_db';
    public const ROUTE_CHECK = "check";

    /** @var array<string, DatabaseConnection> */
    protected static array $connections = [];
    protected static int $count = 0;
    /** @var array<string> */
    protected static $queries = [];

    /**
     * Summary of getDbStatistics
     * @return array<mixed>
     */
    public static function getDbStatistics()
    {
        return ['count' => self::$count, 'queries' => self::$queries];
    }

    /**
     * Summary of getConnectionKey
     * @param string $dbFileName
     * @return string
     */
    protected static function getConnectionKey(string $dbFileName): string
    {
        return $dbFileName;
    }

    /**
     * Summary of getContext
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return DatabaseContext
     */
    public static function getContext($database = null, $config = null)
    {
        return new DatabaseContext($database, $config);
    }

    /**
     * Summary of getContextConnection
     * @param DatabaseContext $dbContext
     * @return DatabaseConnection
     */
    public static function getContextConnection($dbContext)
    {
        $dbFileName = $dbContext->getDbFileName();
        $key = self::getConnectionKey($dbFileName);
        if (!isset(self::$connections[$key])) {
            self::$connections[$key] = new DatabaseConnection($dbFileName, $dbContext->getConfig(), $dbContext->getDatabase());
        }
        return self::$connections[$key];
    }

    /**
     * Summary of checkDatabaseAvailability
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return bool
     */
    public static function checkDatabaseAvailability($database, $config = null)
    {
        $dbContext = self::getContext($database, $config);
        if ($dbContext->noDatabaseSelected()) {
            $count = count($dbContext->getDbList());
            for ($i = 0; $i < $count; $i++) {
                $dbContext->setDatabase($i);
                $dbContext->getDb();
                self::clearDb();
            }
        } else {
            $dbContext->getDb();
        }
        return true;
    }

    /**
     * Summary of clearDb
     * @return void
     */
    public static function clearDb()
    {
        foreach (self::$connections as $connection) {
            $connection->clear();
        }
        self::$connections = [];
    }
}
