<?php

namespace Cerbero\Dto;

use ArrayAccess;
use ArrayIterator;
use Cerbero\Dto\Exceptions\ImmutableDtoException;
use Cerbero\Dto\Exceptions\UnknownDtoPropertyException;
use Cerbero\Dto\Exceptions\UnsetDtoPropertyException;
use IteratorAggregate;
use JsonSerializable;
use Serializable;
use Traversable;

/**
 * The data transfer object.
 *
 */
abstract class Dto implements IteratorAggregate, ArrayAccess, Serializable, JsonSerializable
{
    /**
     * The default flags.
     *
     * @var int
     */
    protected static $defaultFlags = NONE;

    /**
     * The array converter.
     *
     * @var ArrayConverter
     */
    protected static $arrayConverter;

    /**
     * The actual flags.
     *
     * @var int
     */
    protected $flags;

    /**
     * The properties map.
     *
     * @var array
     */
    protected $propertiesMap;

    /**
     * Instantiate the class.
     *
     * @param array $data
     * @param int $flags
     */
    public function __construct(array $data = [], int $flags = NONE)
    {
        $this->flags = $this->mergeFlags(static::getDefaultFlags(), $flags);
        $this->propertiesMap = $this->mapData($data);
    }

    /**
     * Retrieve the merged flags
     *
     * @param int $initialFlags
     * @param int $flagsToMerge
     * @return int
     * @throws Exceptions\IncompatibleDtoFlagsException
     */
    protected function mergeFlags(int $initialFlags, int $flagsToMerge): int
    {
        return (new DtoFlagsHandler)->merge($initialFlags, $flagsToMerge);
    }

    /**
     * Retrieve the DTO mapped properties for the given data
     *
     * @param array $data
     * @return array
     * @throws Exceptions\DtoNotFoundException
     */
    protected function mapData(array $data): array
    {
        return DtoPropertiesMapper::for(static::class)->map($data, $this->getFlags());
    }

    /**
     * Retrieve an instance of DTO
     *
     * @param array $data
     * @param int $flags
     * @return self
     */
    public static function make(array $data = [], int $flags = NONE): self
    {
        return new static($data, $flags);
    }

    /**
     * Retrieve the default flags
     *
     * @return int
     */
    public static function getDefaultFlags(): int
    {
        return static::$defaultFlags;
    }

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
     * Retrieve the DTO properties map
     *
     * @return array
     */
    public function getPropertiesMap(): array
    {
        return $this->propertiesMap;
    }

    /**
     * Retrieve the DTO flags
     *
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * Retrieve the DTO property names
     *
     * @return array
     */
    public function getPropertyNames(): array
    {
        return array_keys($this->getPropertiesMap());
    }

    /**
     * Retrieve the DTO properties
     *
     * @return DtoProperty[]
     */
    public function getProperties(): array
    {
        return array_values($this->getPropertiesMap());
    }

    /**
     * Determine whether the given property is set (even if its value is NULL)
     *
     * @param string $property
     * @return bool
     */
    public function hasProperty(string $property): bool
    {
        try {
            return !!$this->getProperty($property);
        } catch (UnknownDtoPropertyException $e) {
            return false;
        }
    }

    /**
     * Retrieve the given DTO property (support dot notation)
     *
     * @param string $property
     * @return DtoProperty
     * @throws UnknownDtoPropertyException
     */
    public function getProperty(string $property): DtoProperty
    {
        if (isset($this->propertiesMap[$property])) {
            return $this->propertiesMap[$property];
        }

        if (strpos($property, '.') === false) {
            throw new UnknownDtoPropertyException(static::class, $property);
        }

        [$property, $nestedProperty] = explode('.', $property, 2);
        $presumedDto = $this->get($property);

        if ($presumedDto instanceof self) {
            return $presumedDto->getProperty($nestedProperty);
        }

        throw new UnknownDtoPropertyException(static::class, $nestedProperty);
    }

    /**
     * Determine whether the given property has a value (return FALSE if the value is NULL)
     *
     * @param string $property
     * @return bool
     */
    public function has(string $property): bool
    {
        try {
            return $this->get($property) !== null;
        } catch (UnknownDtoPropertyException $e) {
            return false;
        }
    }

    /**
     * Retrieve the given property value
     *
     * @param string $property
     * @return mixed
     * @throws UnknownDtoPropertyException
     */
    public function get(string $property)
    {
        return $this->getProperty($property)->value();
    }

    /**
     * Set the given property to the provided value
     *
     * @param string $property
     * @param mixed $value
     * @return self
     * @throws UnknownDtoPropertyException
     */
    public function set(string $property, $value): self
    {
        $flags = $this->getFlags();
        $dto = ($flags & MUTABLE) ? $this : $this->clone();

        try {
            $dto->getProperty($property)->setValue($value, $flags);
        } catch (UnknownDtoPropertyException $e) {
            if (!($flags & IGNORE_UNKNOWN_PROPERTIES)) {
                throw $e;
            }
        }

        return $dto;
    }

    /**
     * Retrieve a clone of the DTO
     *
     * @return self
     */
    public function clone(): self
    {
        return clone $this;
    }

    /**
     * Merge the given data in the DTO
     *
     * @param iterable $data
     * @param int $flags
     * @return self
     */
    public function merge(iterable $data, int $flags = NONE): self
    {
        $replacements = static::getArrayConverter()->convert($data);
        $mergedData = array_replace_recursive($this->toArray(), $replacements);
        $mergedFlags = $this->mergeFlags($this->getFlags(), $flags);

        if (!($this->getFlags() & MUTABLE)) {
            return new static($mergedData, $mergedFlags);
        }

        $this->flags = $mergedFlags;
        $this->propertiesMap = $this->mapData($mergedData);

        return $this;
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
     * Retrieve the DTO as a JSON string
     *
     * @param int $options
     * @return string|false
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
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
        $flags = $this->getFlags();

        if (!($flags & MUTABLE)) {
            throw new ImmutableDtoException(static::class);
        } elseif (!($flags & PARTIAL)) {
            throw new UnsetDtoPropertyException(static::class, $property);
        } elseif (!$this->hasProperty($property)) {
            throw new UnknownDtoPropertyException(static::class, $property);
        }

        unset($this->propertiesMap[$property]);
    }

    /**
     * Retrieve the serialized DTO
     *
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            $this->toArray(),
            $this->getFlags(),
        ]);
    }

    /**
     * Retrieve the unserialized DTO
     *
     * @param mixed $serialized
     * @return string
     */
    public function unserialize($serialized): void
    {
        [$data, $flags] = unserialize($serialized);

        $this->__construct($data, $flags);
    }

    /**
     * Retrieve the JSON serialized DTO
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Determine whether a given property has a value
     *
     * @param string $property
     * @return bool
     */
    public function __isset(string $property): bool
    {
        return $this->has($property);
    }

    /**
     * Retrieve the given property value
     *
     * @param string $property
     * @return mixed
     * @throws UnknownDtoPropertyException
     */
    public function &__get(string $property)
    {
        $value = $this->get($property);

        return $value;
    }

    /**
     * Set the given property to the provided value
     *
     * @param string $property
     * @param mixed $value
     * @return void
     * @throws ImmutableDtoException
     * @throws UnknownDtoPropertyException
     */
    public function __set(string $property, $value): void
    {
        $this->offsetSet($property, $value);
    }

    /**
     * Retrieve the string representation of the DTO
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Determine how to clone the DTO
     *
     * @return void
     */
    public function __clone()
    {
        foreach ($this->propertiesMap as &$property) {
            $property = clone $property;
        }
    }
}
