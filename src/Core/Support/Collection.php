<?php
declare (strict_types=1);

namespace Sammy1992\Haina\Core\Support;

use Closure;
use ArrayIterator;
use ArrayAccess;
use Sammy1992\Haina\Core\Contracts\Arrayable;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Serializable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Serializable, Arrayable
{
    /**
     * The collection data.
     *
     * @var array
     */
    protected $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $key => $item) {
            $this->set($key, $item);
        }
    }

    /**
     * Adds an element at the end of the collection.
     *
     * @param $item
     * @return true
     */
    public function add($item): bool
    {
        $this->items[] = $item;
        return true;
    }

    /**
     * Clears the collection, removing all elements.
     */
    public function clear(): void
    {
        Arr::forget($this->items, array_keys($this->items));
    }

    /**
     * Checks whether an element is contained in the collection.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param $item
     * @return bool
     */
    public function contains($item): bool
    {
        return in_array($item, $this->items, true);
    }

    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Removes the element at the specified index from the collection.
     *
     * @param $key
     */
    public function remove($key)
    {
        Arr::forget($this->items, $key);
    }

    /**
     * Removes the specified element from the collection, if it is found.
     *
     * @param $item
     * @return bool
     */
    public function removeElement($item): bool
    {
        $key = array_search($item, $this->items, true);

        if (false === $key) return false;

        unset($this->items[$key]);

        return true;
    }

    /**
     * Checks whether the collection contains an element with the specified key/index.
     *
     * @param $key
     * @return bool
     */
    public function containsKey($key): bool
    {
        return isset($this->items[$key]) || array_key_exists($key, $this->items);
    }

    /**
     * Gets the element at the specified key/index.
     *
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_null($key)) return null;

        return Arr::get($this->items, $key, $default);
    }

    /**
     * Gets all keys/indices of the collection.
     *
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->items);
    }

    /**
     * Gets all values of the collection.
     *
     * @return array
     */
    public function getValues(): array
    {
        return array_values($this->items);
    }

    /**
     * Sets an element in the collection at the specified key/index.
     *
     * @param $key
     * @param $value
     */
    public function set($key, $value): void
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     * @return mixed
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     */
    public function last()
    {
        return end($this->items);
    }

    /**
     * Gets the element of the collection at the current iterator position.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->items);
    }

    /**
     * Moves the internal iterator position to the next element and returns this element.
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->items);
    }

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param \Closure $p
     * @return bool
     */
    public function exists(Closure $p): bool
    {
        foreach ($this->items as $key => $item) {
            if ($p($item, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns all the elements of this collection that satisfy the predicate p.
     * The order of the elements is preserved.
     *
     * @param Closure $p
     * @return Collection
     */
    public function filter(Closure $p): self
    {
        return new static(array_filter($this->items, $p, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Tests whether the given predicate p holds for all elements of this collection.
     *
     * @param Closure $p
     * @return bool
     */
    public function forAll(Closure $p): bool
    {
        foreach ($this->items as $key => $item) {
            if (!$p($item, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @param Closure $func
     * @return Collection
     */
    public function map(Closure $func): self
    {
        return new static(array_map($func, $this->items));
    }

    /**
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param Closure $p
     * @return array
     */
    public function partition(Closure $p): array
    {
        $matches = $noMatches = [];

        foreach ($this->items as $key => $item) {
            if ($p($item, $key)) {
                $matches[] = $item;
            } else {
                $noMatches[] = $item;
            }
        }

        return [new static($matches), new static($noMatches)];
    }

    /**
     * Gets the index/key of a given element. The comparison of two elements is strict,
     * that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param $item
     * @return false|int|string
     */
    public function indexOf($item)
    {
        return array_search($item, $this->items, true);
    }

    /**
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     *
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Keys have to be preserved by this method. Calling this method will only return the
     * selected slice and NOT change the elements contained in the collection slice is called on.
     *
     * @param int $offset
     * @param int|null $length
     * @return array
     */
    public function slice(int $offset, ?int $length = null): array
    {
        return array_slice($this->items, $offset, $length, true);
    }

    /**
     * Get all items
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * @see https://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     *
     * @see https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    /**
     * @see https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @see https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @see https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * implementing Countable
     *
     * @see https://www.php.net/manual/en/class.countable.php
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     *  implementing JsonSerializable
     *
     * @see https://www.php.net/manual/zh/class.jsonserializable.php
     *
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->items;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @see https://php.net/manual/en/serializable.serialize.php
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->items);
    }

    /**
     * @see https://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized
     * @return mixed|void
     */
    public function unserialize($serialized)
    {
        return $this->unserialize($this->items);
    }
}