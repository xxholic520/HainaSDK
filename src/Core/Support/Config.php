<?php


namespace Sammy1992\Haina\Core\Support;

/**
 * Class Config
 */
class Config implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Config constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get item form an array using "dot" notation.
     *
     * @param $key
     * @param null $default
     * @return array|mixed|null
     */
    public function get($key, $default = null)
    {
        $config = $this->config;

        if (isset($config[$key])) return $config[$key];

        if (false === strpos($key, '.')) return $default;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->config);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     * @return array|mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (true === isset($this->config[$offset])) {
            $this->config[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if (true === isset($this->config[$offset])) {
            unset($this->config[$offset]);
        }
    }
}