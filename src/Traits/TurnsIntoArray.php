<?php

namespace Cerbero\Dto\Traits;

use ArrayIterator;
use Cerbero\Dto\Exceptions\ImmutableDtoException;
use Cerbero\Dto\Exceptions\UnknownDtoPropertyException;
use Cerbero\Dto\Exceptions\UnsetDtoPropertyException;
use Cerbero\Dto\Manipulators\ArrayConverter;
use Traversable;

use const Cerbero\Dto\MUTABLE;

/**
 * Trait to turn a DTO into an array.
 *
 */
trait TurnsIntoArray
{
    /**
     * The array converter.
     *
     * @var ArrayConverter
     */
    protected static $arrayConverter;

    /**
     * Retrieve the array converter
     *
     * @return ArrayConverter
     */
    public static function getArrayConverter(): ArrayConverter
    {
        return static::$arrayConverter ?: ArrayConverter::instance();
    }

    /**
     * Set the given array converter
     *
     * @param ArrayConverter
     * @return void
     */
    public static function setArrayConverter(ArrayConverter $converter): void
    {
        static::$arrayConverter = $converter;
    }

    /**
     * Retrieve the DTO as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->getPropertiesMap() as $name => $property) {
            $data[$name] = static::getArrayConverter()->convert($property->value());
        }

        return $data;
    }

    /**
     * Retrieve the DTO as an iterator
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->toArray(), ArrayIterator::ARRAY_AS_PROPS);
    }

    /**
     * Determine whether a given property has a value
     *
     * @param mixed $property
     * @return bool
     */
    public function offsetExists($property): bool
    {
        return $this->has($property);
    }

    /**
     * Retrieve the given property value
     *
     * @param mixed $property
     * @return mixed
     * @throws UnknownDtoPropertyException
     */
    public function &offsetGet($property)
    {
        $value = $this->get($property);

        return $value;
    }

    /**
     * Set the given property to the provided value
     *
     * @param mixed $property
     * @param mixed $value
     * @return void
     * @throws ImmutableDtoException
     * @throws UnknownDtoPropertyException
     */
    public function offsetSet($property, $value): void
    {
        if (!($this->getFlags() & MUTABLE)) {
            throw new ImmutableDtoException(static::class);
        }

        $this->set($property, $value);
    }

    /**
     * Set the given property to the provided value
     *
     * @param mixed $property
     * @return void
     * @throws ImmutableDtoException
     * @throws UnsetDtoPropertyException
     * @throws UnknownDtoPropertyException
     */
    public function offsetUnset($property): void
    {
        if (!($this->getFlags() & MUTABLE)) {
            throw new ImmutableDtoException(static::class);
        }

        $this->unset($property);
    }
}
