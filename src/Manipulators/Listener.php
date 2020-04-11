<?php

namespace Cerbero\Dto\Manipulators;

/**
 * The DTO listener.
 *
 */
class Listener
{
    /**
     * The listener instance.
     *
     * @var self
     */
    protected static $instance;

    /**
     * The listeners map.
     *
     * @var array
     */
    protected $listenersMap = [];

    /**
     * The cached DTO listeners.
     *
     * @var array
     */
    protected $cachedListeners = [];

    /**
     * Instantiate the class
     */
    protected function __construct()
    {
        //
    }

    /**
     * Retrieve the listener instance
     *
     * @return self
     */
    public static function instance(): self
    {
        return static::$instance = static::$instance ?: new static();
    }

    /**
     * Set the listeners map
     *
     * @param array $listenersMap
     * @return self
     */
    public function listen(array $listenersMap): self
    {
        $this->listenersMap = $listenersMap;

        return $this;
    }

    /**
     * Add a listener for the given DTO
     *
     * @param string $dtoClass
     * @param string $listener
     * @return self
     */
    public function addListener(string $dtoClass, string $listener): self
    {
        $this->listenersMap[$dtoClass] = $listener;

        return $this;
    }

    /**
     * Remove the listener of the given DTO
     *
     * @param string $dtoClass
     * @return self
     */
    public function removeListener(string $dtoClass): self
    {
        unset($this->listenersMap[$dtoClass]);

        return $this;
    }

    /**
     * Retrieve the listeners map
     *
     * @return array
     */
    public function getListeners(): array
    {
        return $this->listenersMap;
    }

    /**
     * Call the DTO listener when retrieving a value
     *
     * @param string $dtoClass
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    public function getting(string $dtoClass, string $property, $value)
    {
        $method = 'get' . str_replace('_', '', ucwords($property, '_'));

        return $this->callListenerOrReturnValue($dtoClass, $method, $value);
    }

    /**
     * Retrieve the result of the given listener method or the provided value
     *
     * @param string $dtoClass
     * @param string $method
     * @param mixed $value
     * @return mixed
     */
    protected function callListenerOrReturnValue(string $dtoClass, string $method, $value)
    {
        $listener = $this->listenersMap[$dtoClass] ?? null;

        if (!method_exists($listener, $method)) {
            return $value;
        } elseif (empty($this->cachedListeners[$dtoClass])) {
            $this->cachedListeners[$dtoClass] = $this->resolveListener($listener);
        }

        return $this->cachedListeners[$dtoClass]->$method($value);
    }

    /**
     * Retrieve the instance of the given listener
     *
     * @param string $listener
     * @return mixed
     */
    protected function resolveListener(string $listener)
    {
        return new $listener();
    }

    /**
     * Call the DTO listener when setting a value
     *
     * @param string $dtoClass
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    public function setting(string $dtoClass, string $property, $value)
    {
        $method = 'set' . str_replace('_', '', ucwords($property, '_'));

        return $this->callListenerOrReturnValue($dtoClass, $method, $value);
    }
}
