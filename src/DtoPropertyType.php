<?php

namespace Cerbero\Dto;

/**
 * The type of a DTO property.
 *
 */
class DtoPropertyType
{
    /**
     * The primitive types map
     *
     */
    public const TYPES_MAP = [
        'int' => 'integer',
        'bool' => 'boolean',
        'float' => 'double',
    ];

    /**
     * The actual type name.
     *
     * @var string
     */
    protected $name;

    /**
     * Whether it is a collection of the same type.
     *
     * @var bool
     */
    protected $isCollection;

    /**
     * Instantiate the class
     *
     * @param string $name
     * @param bool $isCollection
     */
    public function __construct(string $name, bool $isCollection)
    {
        $this->name = $name;
        $this->isCollection = $isCollection;
    }

    /**
     * Retrieve the type name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Determine whether the type is a collection
     *
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->isCollection;
    }

    /**
     * Determine whether the current property type matches the given value
     *
     * @param mixed $value
     * @return bool
     */
    public function matches($value): bool
    {
        if ($this->isCollection()) {
            return $this->matchesCollection($value);
        }

        $name = $this->name();

        return $name == 'mixed' ||
            is_a($value, $name) ||
            gettype($value) == (static::TYPES_MAP[$name] ?? $name);
    }

    /**
     * Determine whether the current property type matches the given collection items
     *
     * @param mixed $collection
     * @return bool
     */
    public function matchesCollection($collection): bool
    {
        if (!is_iterable($collection)) {
            return false;
        }

        $this->isCollection = false;

        foreach ($collection as $item) {
            if (!$this->matches($item)) {
                return false;
            }
        }

        return $this->isCollection = true;
    }

    /**
     * Determine whether the current property type is a DTO
     *
     * @return bool
     */
    public function isDto(): bool
    {
        return is_subclass_of($this->name(), Dto::class);
    }
}
