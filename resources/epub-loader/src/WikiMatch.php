<?php
/**
 * WikiMatch class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace Marsender\EPubLoader;

use Wikidata\Wikidata;
use Exception;

class WikiMatch
{
    public const ENTITY_URL = 'http://www.wikidata.org/entity/';
    public const AUTHOR_PROPERTY = 'P50';

    /** @var string|null */
    protected $cacheDir;
    /** @var string */
    protected $lang;
    /** @var string|int */
    protected $limit;
    /** @var Wikidata */
    protected $wikidata;

    /**
     * Summary of __construct
     * @param string|null $cacheDir
     * @param string $lang Language (default: en)
     * @param string|int $limit Max count of returning items (default: 10)
     */
    public function __construct($cacheDir = null, $lang = 'en', $limit = 10)
    {
        $this->cacheDir = $cacheDir;
        if (!empty($this->cacheDir)) {
            $this->prepareCacheDir();
        }
        $this->lang = $lang;
        $this->limit = $limit;
        $this->wikidata = new Wikidata();
    }

    /**
     * Summary of prepareCacheDir
     * @throws \Exception
     * @return void
     */
    protected function prepareCacheDir()
    {
        $makeDirs = [
            $this->cacheDir . '/authors',
            $this->cacheDir . '/works',
            $this->cacheDir . '/series',
            $this->cacheDir . '/entities',
        ];
        foreach ($makeDirs as $makeDir) {
            if (!is_dir($makeDir) && !mkdir($makeDir, 0755, true)) {
                throw new Exception('Cannot create directory: ' . $makeDir);
            }
        }
    }

    /**
     * Summary of findAuthors
     * @param string $query
     * @param string|null $lang Language (default: en)
     * @param string|int|null $limit Max count of returning items (default: 10)
     * @return array<string, mixed>
     */
    public function findAuthors($query, $lang = null, $limit = 10)
    {
        // Find match on Wikidata
        $lang ??= $this->lang;
        $limit ??= $this->limit;
        if ($this->cacheDir) {
            $cacheFile = $this->cacheDir . '/authors/' . $query . '.' . $lang . '.json';
            if (is_file($cacheFile)) {
                return $this->loadCache($cacheFile);
            }
        }
        $results = $this->wikidata->search($query, $lang, $limit);
        $matched = [];
        foreach ($results as $id => $result) {
            $matched[$id] = (array) $result;
        }
        if ($this->cacheDir) {
            $this->saveCache($cacheFile, $matched);
        }
        return $matched;
    }

    /**
     * Summary of findAuthorId
     * @param array<mixed> $author
     * @param string|null $lang Language (default: en)
     * @return string|null
     */
    public function findAuthorId($author, $lang = null)
    {
        if (!empty($author['link']) && strncmp($author['link'], static::ENTITY_URL, strlen(static::ENTITY_URL)) === 0) {
            return static::entity($author['link']);
        }
        $entityId = null;
        $query = $author['name'];
        $matched = $this->findAuthors($query, $lang);
        // Find works from author for 1st match
        if (count($matched) > 0) {
            $entityId = array_keys($matched)[0];
        }
        return $entityId;
    }

    /**
     * Summary of findWorksByAuthor
     * @param array<mixed> $author
     * @param string|null $lang Language (default: en)
     * @param string|int|null $limit Max count of returning items (default: 10)
     * @return array<string, mixed>
     */
    public function findWorksByAuthor($author, $lang = null, $limit = 100)
    {
        $lang ??= $this->lang;
        $limit ??= $this->limit;
        $entityId = $this->findAuthorId($author, $lang);
        if (empty($entityId)) {
            return [];
        }
        if ($this->cacheDir) {
            $cacheFile = $this->cacheDir . '/works/' . $entityId . '.' . $lang . '.' . $limit . '.json';
            if (is_file($cacheFile)) {
                return $this->loadCache($cacheFile);
            }
        }
        // Find literary works from author
        $propId = static::AUTHOR_PROPERTY;
        /**
        $propId = 'P31/wdt:P279* wd:Q7725634.
        ?item wdt:' . static::AUTHOR_PROPERTY;
    $query = '
            SELECT ?item WHERE {
                ?item wdt:' . $property . ' ' . $subject . '.
            } LIMIT ' . $limit . '
        ';
         */
        $results = $this->wikidata->searchBy($propId, $entityId, $lang, $limit);
        $matched = [];
        foreach ($results as $id => $result) {
            $matched[$id] = (array) $result;
        }
        if ($this->cacheDir) {
            $this->saveCache($cacheFile, $matched);
        }
        return $matched;
    }

    /**
     * Summary of findWorksByName
     * @param array<mixed> $author
     * @param string|null $lang Language (default: en)
     * @param string|int|null $limit Max count of returning items (default: 10)
     * @return array<string, mixed>
     */
    public function findWorksByName($author, $lang = null, $limit = 100)
    {
        $lang ??= $this->lang;
        $limit ??= $this->limit;
        $query = $author['name'];
        if ($this->cacheDir) {
            $cacheFile = $this->cacheDir . '/works/' . $query . '.' . $lang . '.json';
            if (is_file($cacheFile)) {
                return $this->loadCache($cacheFile);
            }
        }
        $propId = 'P2093';
        $results = $this->wikidata->searchBy($propId, $query, $lang, $limit);
        $matched = [];
        foreach ($results as $id => $result) {
            $matched[$id] = (array) $result;
        }
        if ($this->cacheDir) {
            $this->saveCache($cacheFile, $matched);
        }
        return $matched;
    }

    /**
     * Summary of findWorksByTitle
     * @param string $query
     * @param string|null $lang Language (default: en)
     * @param string|int|null $limit Max count of returning items (default: 10)
     * @return array<string, mixed>
     */
    public function findWorksByTitle($query, $lang = null, $limit = 10)
    {
        $lang ??= $this->lang;
        $limit ??= $this->limit;
        if ($this->cacheDir) {
            $cacheFile = $this->cacheDir . '/works/' . $query . '.' . $lang . '.json';
            if (is_file($cacheFile)) {
                return $this->loadCache($cacheFile);
            }
        }
        $results = $this->wikidata->search($query, $lang, $limit);
        $matched = [];
        foreach ($results as $id => $result) {
            $matched[$id] = (array) $result;
        }
        if ($this->cacheDir) {
            $this->saveCache($cacheFile, $matched);
        }
        return $matched;
    }

    /**
     * Summary of findWorkId - @todo
     * @param array<mixed> $author
     * @param array<mixed> $book
     * @param string|null $lang Language (default: en)
     * @return string|null
     */
    public function findWorkId($author, $book, $lang = null)
    {
        $lang ??= $this->lang;
        $authorId = $this->findAuthorId($author, $lang);
        $entityId = null;
        $query = $book['title'];
        $matched = $this->findAuthors($query, $lang);
        // Find works from author for 1st match
        if (count($matched) > 0) {
            $entityId = array_keys($matched)[0];
        }
        return $entityId;
    }

    /**
     * Summary of findSeriesByAuthor
     * @param array<mixed> $author
     * @param string|null $lang Language (default: en)
     * @param string|int|null $limit Max count of returning items (default: 10)
     * @return array<string, mixed>
     */
    public function findSeriesByAuthor($author, $lang = null, $limit = 100)
    {
        $lang ??= $this->lang;
        $limit ??= $this->limit;
        $entityId = $this->findAuthorId($author, $lang);
        if (empty($entityId)) {
            return [];
        }
        if ($this->cacheDir) {
            $cacheFile = $this->cacheDir . '/series/' . $entityId . '.' . $lang . '.' . $limit . '.json';
            if (is_file($cacheFile)) {
                return $this->loadCache($cacheFile);
            }
        }
        // Find series of creative works from author
        //$propId = static::AUTHOR_PROPERTY;
        $propId = 'P31/wdt:P279* wd:Q7725310.
        ?item wdt:' . static::AUTHOR_PROPERTY;
        /**
    $query = '
            SELECT ?item WHERE {
                ?item wdt:' . $property . ' ' . $subject . '.
            } LIMIT ' . $limit . '
        ';
         */
        $results = $this->wikidata->searchBy($propId, $entityId, $lang, $limit);
        $matched = [];
        foreach ($results as $id => $result) {
            $matched[$id] = (array) $result;
        }
        if ($this->cacheDir) {
            $this->saveCache($cacheFile, $matched);
        }
        return $matched;
    }

    /**
     * Summary of findSeriesByTitle
     * @param string $query
     * @param string|null $lang Language (default: en)
     * @param string|int|null $limit Max count of returning items (default: 10)
     * @return array<string, mixed>
     */
    public function findSeriesByTitle($query, $lang = null, $limit = 10)
    {
        $lang ??= $this->lang;
        $limit ??= $this->limit;
        if ($this->cacheDir) {
            $cacheFile = $this->cacheDir . '/series/' . $query . '.' . $lang . '.json';
            if (is_file($cacheFile)) {
                return $this->loadCache($cacheFile);
            }
        }
        $results = $this->wikidata->search($query, $lang, $limit);
        $matched = [];
        foreach ($results as $id => $result) {
            $matched[$id] = (array) $result;
        }
        if ($this->cacheDir) {
            $this->saveCache($cacheFile, $matched);
        }
        return $matched;
    }

    /**
     * Summary of getEntity
     * @param string $entityId
     * @param string|null $lang Language (default: en)
     * @return array<string, mixed>
     */
    public function getEntity($entityId, $lang = null)
    {
        $lang ??= $this->lang;
        if ($this->cacheDir) {
            $cacheFile = $this->cacheDir . '/entities/' . $entityId . '.' . $lang . '.json';
            if (is_file($cacheFile)) {
                return $this->loadCache($cacheFile);
            }
        }
        $result = $this->wikidata->get($entityId, $lang);
        $entity = $result->toArray();
        $entity = json_decode(json_encode($entity), true);
        if ($this->cacheDir) {
            $this->saveCache($cacheFile, $entity);
        }
        return $entity;
    }

    /**
     * Summary of loadCache
     * @param string $cacheFile
     * @return mixed
     */
    public function loadCache($cacheFile)
    {
        return json_decode(file_get_contents($cacheFile), true);
    }

    /**
     * Summary of saveCache
     * @param string $cacheFile
     * @param mixed $data
     * @return void
     */
    public function saveCache($cacheFile, $data)
    {
        file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Summary of link
     * @param string $entityId
     * @return string
     */
    public static function link($entityId)
    {
        return static::ENTITY_URL . $entityId;
    }

    /**
     * Summary of entity
     * @param string $link
     * @return string
     */
    public static function entity($link)
    {
        return str_replace(static::ENTITY_URL, '', $link);
    }
}
