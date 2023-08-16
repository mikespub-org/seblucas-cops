<?php

namespace SebLucas\EPubMeta\Data;

use ArrayAccess;
use Countable;
use Exception;
use BadMethodCallException;
use Iterator;

/**
 * EPUB manifest structure
 *
 * @author Simon Schrape <simon@epubli.com>
 * @implements \Iterator<string, Item>
 * @implements \ArrayAccess<string, Item>
 */
class Manifest implements Iterator, Countable, ArrayAccess
{
    /** @var array|Item[] The map of all Items in this Manifest indexed by their IDs. */
    private $items = [];

    /**
     * Create and add an Item with the given properties.
     *
     * @param string $id The identifier of the new item.
     * @param string $href The relative path of the referenced file in the EPUB.
     * @param resource|null $handle A file handle to the referenced file in the EPUB.
     * @param int $size The size of the referenced file in the EPUB.
     * @param string|null $mediaType
     * @return Item The newly created Item.
     * @throws Exception If $id is already taken.
     */
    public function createItem($id, $href, $handle, $size, $mediaType = null)
    {
        if (isset($this->items[$id])) {
            throw new Exception("Item with ID $id already exists!");
        }
        $item = new Item($id, $href, $handle, $size, $mediaType);
        $this->items[$id] = $item;

        return $item;
    }

    /**
     * Return the current Item while iterating this Manifest.
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return Item
     */
    public function current(): Item
    {
        return current($this->items);
    }

    /**
     * Move forward to next Item while iterating this Manifest.
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next(): void
    {
        next($this->items);
    }

    /**
     * Return the ID of the current Item while iterating this Manifest.
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return string|null on success, or null on failure.
     */
    public function key(): ?string
    {
        return key($this->items);
    }

    /**
     * Checks if current Iterator position is valid.
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean true on success or false on failure.
     */
    public function valid(): bool
    {
        return (bool)current($this->items);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        reset($this->items);
    }

    /**
     * Get the first Item of this Manifest.
     *
     * @return Item
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * Get the last Item of this Manifest.
     *
     * @return Item
     */
    public function last()
    {
        return end($this->items);
    }

    /**
     * Count items of this Manifest.
     *
     * @link https://php.net/manual/en/countable.count.php
     * @return int The number of Items contained in this Manifest.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param string $offset An offset to check for.
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param string $offset The offset to retrieve.
     * @return Item
     */
    public function offsetGet($offset): Item
    {
        return $this->items[$offset];
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @throws BadMethodCallException
     */
    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException("Only reading array access is supported!");
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     * @throws BadMethodCallException
     */
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException("Only reading array access is supported!");
    }
}
