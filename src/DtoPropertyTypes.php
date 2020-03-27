<?php

namespace Cerbero\Dto;

/**
 * The wrapper for DTO property types.
 *
 * @property DtoPropertyType[] $all
 * @property bool $includeNull
 * @property bool $includeArray
 * @property bool $includeBool
 * @property bool $expectCollection
 * @property string $expectedDto
 * @property array $declaredNames
 */
class DtoPropertyTypes
{
    /**
     * The DTO property types.
     *
     * @var DtoPropertyType[]
     */
    protected $all = [];

    /**
     * Whether one of the types is 'null'.
     *
     * @var bool
     */
    protected $includeNull = false;

    /**
     * Whether one of the types is 'array'.
     *
     * @var bool
     */
    protected $includeArray = false;

    /**
     * Whether one of the types is 'bool'.
     *
     * @var bool
     */
    protected $includeBool = false;

    /**
     * Whether the types expect a collection.
     *
     * @var bool
     */
    protected $expectCollection = false;

    /**
     * The expected DTO.
     *
     * @var string
     */
    protected $expectedDto;

    /**
     * The types name with the [] suffix if collections.
     *
     * @var array
     */
    protected $declaredNames = [];

    /**
     * Add the given DTO property type
     *
     * @param DtoPropertyType $type
     * @return self
     */
    public function addType(DtoPropertyType $type): self
    {
        $this->all[] = $type;
        $this->includeNull = $this->includeNull || $type->name() == 'null';
        $this->includeArray = $this->includeArray || $type->name() == 'array';
        $this->includeBool = $this->includeBool || $type->name() == 'bool';
        $this->expectCollection = $this->expectCollection || $type->isCollection();
        $this->expectedDto = $this->getExpectedDto($type);
        $this->declaredNames[] = $type->declaredName();

        return $this;
    }

    /**
     * Retrieve the expected DTO if any
     *
     * @param DtoPropertyType $type
     * @return string|null
     */
    protected function getExpectedDto(DtoPropertyType $type): ?string
    {
        if ($this->expectedDto) {
            return $this->expectedDto;
        }

        return $type->isDto() ? $type->name() : null;
    }

    /**
     * Determine whether the given value matches at least one of the property types
     *
     * @param mixed $value
     * @return bool
     */
    public function match($value): bool
    {
        foreach ($this->all as $type) {
            if ($type->matches($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the types have a default value depending on the given flags
     *
     * @param int $flags
     * @return mixed
     */
    public function haveDefaultValue(int $flags)
    {
        return $this->arraysHaveDefault($flags) ||
            ($this->includeBool && ($flags & BOOL_DEFAULT_TO_FALSE)) ||
            ($this->includeNull && ($flags & NULLABLE_DEFAULT_TO_NULL));
    }

    /**
     * Determine whether array types have a default value
     *
     * @param int $flags
     * @return bool
     */
    protected function arraysHaveDefault(int $flags): bool
    {
        $includeArray = $this->includeArray || $this->expectCollection;

        return $includeArray && ($flags & ARRAY_DEFAULT_TO_EMPTY_ARRAY);
    }

    /**
     * Retrieve the types default value depending on the given flags
     *
     * @param int $flags
     * @return mixed
     */
    public function getDefaultValue(int $flags)
    {
        switch (true) {
            case ($this->includeArray || $this->expectCollection) && ($flags & ARRAY_DEFAULT_TO_EMPTY_ARRAY):
                return [];
            case $this->includeBool && ($flags & BOOL_DEFAULT_TO_FALSE):
                return false;
        }
    }

    /**
     * Retrieve the given property
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->{$name};
    }
}
