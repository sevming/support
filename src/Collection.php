<?php

namespace Sevming\Support;

use \Countable;
use \ArrayAccess;
use \Traversable;
use \ArrayIterator;
use \JsonSerializable;
use \IteratorAggregate;
use \Serializable;
use Sevming\Support\Contracts\{Arrayable, Jsonable};

/**
 * Collection helper from Illuminate\Support\Collection.
 */
class Collection implements ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable, Serializable
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new collection.
     *
     * @param mixed $items
     *
     * @return void
     */
    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @param mixed $items
     *
     * @return static
     */
    public static function make($items = [])
    {
        return new static($items);
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
     * Get all items except for those with the specified keys.
     *
     * @param Collection|mixed $keys
     *
     * @return static
     */
    public function except($keys)
    {
        if ($keys instanceof self) {
            $keys = $keys->all();
        } elseif (!is_array($keys)) {
            $keys = func_get_args();
        }

        return new static(Arr::except($this->items, $keys));
    }

    /**
     * Get the items with the specified keys.
     *
     * @param mixed $keys
     *
     * @return static
     */
    public function only($keys)
    {
        if (is_null($keys)) {
            return new static($this->items);
        }

        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(Arr::only($this->items, $keys));
    }

    /**
     * Remove an item from the collection by key.
     *
     * @param string|array $keys
     *
     * @return $this
     */
    public function forget($keys)
    {
        Arr::forget($this->items, $keys);
        return new static($this->items);
    }

    /**
     * Merge the collection with the given items.
     *
     * @param mixed $items
     *
     * @return static
     */
    public function merge($items)
    {
        return new static(array_merge($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Get the first item from the collection passing the given truth test.
     *
     * @param callable|null $callback
     * @param mixed         $default
     *
     * @return mixed
     */
    public function first(callable $callback = null, $default = null)
    {
        return Arr::first($this->items, $callback, $default);
    }

    /**
     * Get the last item from the collection.
     *
     * @param callable|null $callback
     * @param mixed         $default
     *
     * @return mixed
     */
    public function last(callable $callback = null, $default = null)
    {
        return Arr::last($this->items, $callback, $default);
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();
        return Arr::has($this->items, $keys);
    }

    /**
     * Set an item to the collection.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        Arr::set($this->items, $key, $value);
        return $this;
    }

    /**
     * Get an item from the collection by key.
     *
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Determine if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    /**
     * "Paginate" the collection by slicing it into a smaller collection.
     *
     * @param int $page
     * @param int $perPage
     *
     * @return static
     */
    public function forPage($page, $perPage)
    {
        $offset = max(0, ($page - 1) * $perPage);
        return $this->slice($offset, $perPage);
    }

    /**
     * Slice the underlying collection array.
     *
     * @param int $offset
     * @param int $length
     *
     * @return static
     */
    public function slice($offset, $length = null)
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, $this->items);
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }

    /**
     * Generates a storable representation of a value
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->items);
    }

    /**
     * Creates a PHP value from a stored representation
     *
     * @param string $serialized
     *
     * @return $this
     */
    public function unserialize($serialized)
    {
        return $this->items = unserialize($serialized);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        if ($this->has($key)) {
            $this->forget($key);
        }
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Get a data by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Assigns a value to the specified data.
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Whether or not an data exists by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * Unset an data by key.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        $this->forget($key);
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param mixed $items
     *
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof JsonSerializable) {
            return (array)$items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array)$items;
    }
}
