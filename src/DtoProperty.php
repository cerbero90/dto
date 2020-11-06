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
     * The property value processor.
     *
     * @var DtoPropertyValueProcessor
     */
    protected $valueProcessor;

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
        $this->valueProcessor = new DtoPropertyValueProcessor($this);
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
        $canBeDto = $this->rawValue instanceof Dto || is_array($this->rawValue);

        switch (true) {
            case $this->types->expectedDto && $canBeDto:
            case $this->rawValue === null && $this->types->includeNull:
            case $this->types->expectCollection && is_iterable($this->rawValue):
            case $this->types->match($this->value()):
                return $this;
        }

        throw new UnexpectedValueException($this);
    }

    /**
     * Retrieve the processed value
     *
     * @return void
     */
    public function value()
    {
        if (!$this->valueIsProcessed) {
            $this->processedValue = $this->valueProcessor->process();
            $this->valueIsProcessed = true;
        }

        return $this->processedValue;
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
        $this->valueProcessor = new DtoPropertyValueProcessor($this);
    }
}
