<?php

namespace SebLucas\EPubMeta\Contents;

use Iterator;

/**
 * A list of EPUB TOC navigation points.
 *
 * @author Simon Schrape <simon@epubli.com>
 * @implements \Iterator<int, NavPoint>
 */
class NavPointList implements Iterator
{
    /** @var array|NavPoint[] */
    private $navPoints = [];

    public function __construct()
    {
    }

    /**
     * @param NavPoint $navPoint
     * @return void
     */
    public function addNavPoint(NavPoint $navPoint)
    {
        $this->navPoints[] = $navPoint;
    }

    /**
     * @param string $file
     *
     * @return array|NavPoint[]
     */
    public function findNavPointsForFile($file)
    {
        $matches = [];
        foreach ($this->navPoints as $navPoint) {
            if ($navPoint->getContentSourceFile() == $file) {
                $matches[] = $navPoint;
            }
            $childMatches = $navPoint->getChildren()->findNavPointsForFile($file);
            if (count($childMatches)) {
                $matches = array_merge($matches, $childMatches);
            }
        }
        return $matches;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return NavPoint
     */
    public function current(): NavPoint
    {
        return current($this->navPoints);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next(): void
    {
        next($this->navPoints);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int|null scalar on success, or null on failure.
     */
    public function key(): ?int
    {
        return key($this->navPoints);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return (bool)current($this->navPoints);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        reset($this->navPoints);
    }

    /**
     * @return NavPoint
     */
    public function first()
    {
        return reset($this->navPoints);
    }

    /**
     * @return NavPoint
     */
    public function last()
    {
        return end($this->navPoints);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->navPoints);
    }
}
