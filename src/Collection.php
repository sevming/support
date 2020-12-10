<?php

namespace Sevming\Support;

use ArrayAccess;
use Countable;
use JsonSerializable;
use Serializable;

/**
 * Collection From Illuminate\Support\Collection
 */
class Collection implements ArrayAccess, Countable, JsonSerializable, Serializable
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Collection constructor.
     *
     * @param $items
     */
    public function __construct($items)
    {
        $this->items = $items;
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param string|array $keys
     *
     * @return bool
     */
    public function has($keys)
    {
        return Arr::has($this->items, $keys);
    }

    /**
     * Get an item from the collection by key.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Set an item in the collection by key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * Remove an item from the collection by key.
     *
     * @param string|array $keys
     */
    public function forget($keys)
    {
        Arr::forget($this->items, $keys);
    }

    /**
     * Return specific items.
     *
     * @param array|string $keys
     *
     * @return array
     */
    public function only($keys)
    {
        return Arr::only($this->items, $keys);
    }

    /**
     * Get all items except for those with the specified keys.
     *
     * @param string|array $keys
     *
     * @return array
     */
    public function except($keys)
    {
        return Arr::except($this->items, $keys);
    }

    /**
     * @inheritdoc
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @inheritdoc
     *
     * @param mixed $offset
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritdoc
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->forget($offset);
    }

    /**
     * @inheritdoc
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @inheritdoc
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->items);
    }

    /**
     * @inheritdoc
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        return $this->items = unserialize($serialized);
    }
}