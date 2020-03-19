<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Exceptions\UnexpectedValueException;

/**
 * The DTO property.
 *
 */
class DtoProperty
{
    /**
     * The property name.
     *
     * @var string
     */
    protected $name;

    /**
     * The property raw value.
     *
     * @var mixed
     */
    protected $rawValue;

    /**
     * The property types.
     *
     * @var DtoPropertyTypes
     */
    protected $types;

    /**
     * The DTO flags.
     *
     * @var int
     */
    protected $flags;

    /**
     * The processed value.
     *
     * @var mixed
     */
    protected $processedValue;

    /**
     * Whether the value has been processed.
     *
     * @var bool
     */
    protected $valueIsProcessed = false;

    /**
     * Instantiate the class.
     *
     * @param string $name
     * @param mixed $rawValue
     * @param DtoPropertyTypes $types
     * @param int $flags
     */
    protected function __construct(string $name, $rawValue, DtoPropertyTypes $types, int $flags)
    {
        $this->name = $name;
        $this->rawValue = $rawValue;
        $this->types = $types;
        $this->flags = $flags;
    }

    /**
     * Retrieve a DTO property instance after validating it
     *
     * @param string $name
     * @param mixed $rawValue
     * @param DtoPropertyTypes $types
     * @param int $flags
     * @return self
     * @throws UnexpectedValueException
     */
    public static function create(string $name, $rawValue, DtoPropertyTypes $types, int $flags): self
    {
        $instance = new static($name, $rawValue, $types, $flags);

        return $instance->validate();
    }

    /**
     * Validate the current property value depending on types and flags
     *
     * @return self
     * @throws UnexpectedValueException
     */
    public function validate(): self
    {
        if ($this->rawValue === null) {
            if ($this->isNullable()) {
                return $this;
            }

            throw new UnexpectedValueException($this);
        }

        if ($this->types->match($this->value())) {
            return $this;
        }

        throw new UnexpectedValueException($this);
    }

    /**
     * Determine whether this property is nullable
     *
     * @return bool
     */
    public function isNullable(): bool
    {
        if ($this->flags & NOT_NULLABLE) {
            return false;
        }

        return ($this->flags & NULLABLE) || $this->types->includeNull;
    }

    /**
     * Retrieve the processed value
     *
     * @return void
     */
    public function value()
    {
        if ($this->valueIsProcessed) {
            return $this->processedValue;
        }

        $this->processedValue = $this->types->expectedDto ? $this->castRawValueIntoDto() : $this->rawValue;
        $this->valueIsProcessed = true;

        return $this->processedValue;
    }

    /**
     * Retrieve the raw value casted into a DTO or a collection of DTOs
     *
     * @return Dto|Dto[]|null
     */
    protected function castRawValueIntoDto()
    {
        if ($this->rawValue === null) {
            return null;
        }

        $dto = $this->types->expectedDto;

        if (!$this->types->expectCollection) {
            return is_a($this->rawValue, $dto) ? $this->rawValue : $dto::make($this->rawValue, $this->flags);
        }

        return array_map(function ($data) use ($dto) {
            return is_a($data, $dto) ? $data : $dto::make($data, $this->flags);
        }, $this->rawValue);
    }

    /**
     * Retrieve the property name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retrieve the property raw value
     *
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->rawValue;
    }

    /**
     * Retrieve the property types
     *
     * @return DtoPropertyTypes
     */
    public function getTypes(): DtoPropertyTypes
    {
        return $this->types;
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
     * Set a new value to this property and validate it
     *
     * @param mixed $rawValue
     * @param int $flags
     * @return self
     */
    public function setValue($rawValue, int $flags): self
    {
        $this->rawValue = $rawValue;
        $this->flags = $flags;
        $this->valueIsProcessed = false;

        return $this->validate();
    }

    /**
     * Determine how to clone the DTO property
     *
     * @return void
     */
    public function __clone()
    {
        $this->types = clone $this->types;
    }
}
