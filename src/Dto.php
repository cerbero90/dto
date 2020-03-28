<?php

namespace Cerbero\Dto;

use ArrayAccess;
use Cerbero\Dto\Traits\HasFlags;
use Cerbero\Dto\Traits\HasProperties;
use Cerbero\Dto\Traits\HasValues;
use Cerbero\Dto\Traits\ManipulatesData;
use Cerbero\Dto\Traits\TurnsIntoArray;
use Cerbero\Dto\Traits\TurnsIntoString;
use IteratorAggregate;
use JsonSerializable;
use Serializable;

/**
 * The data transfer object.
 *
 */
abstract class Dto implements IteratorAggregate, ArrayAccess, Serializable, JsonSerializable
{
    use HasProperties;
    use HasValues;
    use HasFlags;
    use ManipulatesData;
    use TurnsIntoArray;
    use TurnsIntoString;

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
        return (new DtoFlagsHandler())->merge($initialFlags, $flagsToMerge);
    }

    /**
     * Retrieve the DTO mapped properties for the given data
     *
     * @param array $data
     * @return array
     * @throws Exceptions\DtoNotFoundException
     * @throws Exceptions\InvalidDocCommentException
     * @throws Exceptions\MissingValueException
     * @throws Exceptions\UnexpectedValueException
     * @throws Exceptions\UnknownDtoPropertyException
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
     * Retrieve a clone of the DTO
     *
     * @return self
     */
    public function clone(): self
    {
        return clone $this;
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
