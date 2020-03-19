<?php

namespace Cerbero\Dto;

/**
 * The array converter.
 *
 */
class ArrayConverter
{
    /**
     * The class instance.
     *
     * @var self
     */
    protected static $instance;

    /**
     * The registered conversions.
     *
     * @var array
     */
    protected $conversions = [];

    /**
     * Instantiate the class
     *
     */
    protected function __construct()
    {
        //
    }

    /**
     * Retrieve the class instance
     *
     * @return self
     */
    public static function instance(): self
    {
        return static::$instance = static::$instance ?: new static;
    }

    /**
     * Register a conversion for the given target
     *
     * @param string $target
     * @param callable $callback
     * @return self
     */
    public function conversion(string $target, callable $callback): self
    {
        $this->conversions = [$target => $callback] + $this->conversions;

        return $this;
    }

    /**
     * Retrieve the conversion for the given target
     *
     * @param mixed $target
     * @return callable|null
     */
    public function getConversion($target): ?callable
    {
        foreach ($this->conversions as $type => $conversion) {
            if (is_a($target, $type)) {
                return $conversion;
            }
        }

        return null;
    }

    /**
     * Convert the given item into an array
     *
     * @param mixed $item
     * @return mixed
     */
    public function convert($item)
    {
        if ($convert = $this->getConversion($item)) {
            return $convert($item);
        }

        if (is_iterable($item)) {
            $result = [];

            foreach ($item as $key => $value) {
                $result[$key] = $this->convert($value);
            }

            return $result;
        }

        return $item;
    }
}
