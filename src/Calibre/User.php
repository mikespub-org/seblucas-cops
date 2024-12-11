<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use PDO;
use Exception;
use JsonException;

/**
 * Summary of User
 */
class User
{
    public const ROUTE_ALL = "restapi-user";
    public const ROUTE_DETAIL = "restapi-user-details";
    public const SQL_TABLE = "users";
    public const SQL_COLUMNS = "id, name, timestamp, session_data, restriction, readonly, misc_data";

    public int $id;
    public string $name;
    public mixed $restriction;
    public ?string $userDbFile = null;

    /**
     * Summary of __construct
     * @param \stdClass $post
     * @param ?string $userDbFile
     */
    public function __construct($post, $userDbFile = null)
    {
        $this->id = $post->id;
        $this->name = $post->name;
        // @todo use restriction etc. from Calibre user database
        // ['library_restrictions' => []]
        try {
            $this->restriction = json_decode($post->restriction, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->restriction = $post->restriction;
        }
        $this->userDbFile = $userDbFile;
    }

    /**
     * Summary of getUserDb
     * @param string|null $userDbFile
     * @return \PDO
     */
    public static function getUserDb($userDbFile = null)
    {
        $userDbFile ??= Config::get('calibre_user_database');
        if (!is_string($userDbFile) || !is_readable($userDbFile)) {
            throw new Exception('Invalid users database ' . $userDbFile);
        }
        return new PDO('sqlite:' . $userDbFile);
    }

    /**
     * Summary of getInstanceByName
     * @param string $name
     * @param ?string $userDbFile
     * @return self|null
     */
    public static function getInstanceByName($name, $userDbFile = null)
    {
        try {
            $db = self::getUserDb($userDbFile);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
        $query = 'select ' . self::SQL_COLUMNS . ' from ' . self::SQL_TABLE . ' where name = ?';
        $params = [$name];
        $result = $db->prepare($query);
        $result->execute($params);
        if ($post = $result->fetchObject()) {
            return new self($post, $userDbFile);
        }
        return null;
    }

    /**
     * Summary of verifyLogin
     * @param array<mixed> $serverVars
     * @return bool
     */
    public static function verifyLogin($serverVars = null)
    {
        $basicAuth = Config::get('basic_authentication');
        if (empty($basicAuth)) {
            return true;
        }
        $serverVars ??= $_SERVER;
        if (empty($serverVars['PHP_AUTH_USER']) || empty($serverVars['PHP_AUTH_PW'])) {
            return false;
        }
        // array( "username" => "xxx", "password" => "secret")
        if (is_array($basicAuth)) {
            return self::checkBasicAuthArray($basicAuth, $serverVars);
        }
        // /config/.config/calibre/server-users.sqlite
        if (is_string($basicAuth)) {
            return self::checkBasicAuthDatabase($basicAuth, $serverVars);
        }
        return false;
    }

    /**
     * Summary of checkBasicAuthArray
     * @param array<string, mixed> $authArray
     * @param array<mixed> $serverVars
     * @return bool
     */
    public static function checkBasicAuthArray($authArray, $serverVars)
    {
        if (($serverVars['PHP_AUTH_USER'] != $authArray['username'] ||
            $serverVars['PHP_AUTH_PW'] != $authArray['password'])) {
            return false;
        }
        return true;
    }

    /**
     * Summary of checkBasicAuthDatabase
     * @param string $userDbFile
     * @param array<mixed> $serverVars
     * @return bool
     */
    public static function checkBasicAuthDatabase($userDbFile, $serverVars)
    {
        try {
            $db = self::getUserDb($userDbFile);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
        $query = 'select ' . self::SQL_COLUMNS . ' from ' . self::SQL_TABLE . ' where name = ? and pw = ?';
        $stmt = $db->prepare($query);
        $params = [ $serverVars['PHP_AUTH_USER'], $serverVars['PHP_AUTH_PW'] ];
        $stmt->execute($params);
        $result = $stmt->fetchObject();
        if (empty($result) || $result->name != $serverVars['PHP_AUTH_USER']) {
            return false;
        }
        return true;
    }
}
