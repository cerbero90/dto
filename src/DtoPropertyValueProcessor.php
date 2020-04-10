<?php

namespace Cerbero\Dto;

/**
 * The processor for DTO property values.
 *
 */
class DtoPropertyValueProcessor
{
    /**
     * The property to process the value of.
     *
     * @var DtoProperty
     */
    protected $property;

    /**
     * Instantiate the class.
     *
     * @param DtoProperty $property
     */
    public function __construct(DtoProperty $property)
    {
        $this->property = $property;
    }

    /**
     * Retrieve the processed property value
     *
     * @return mixed
     */
    public function process()
    {
        if (is_null($rawValue = $this->property->getRawValue())) {
            return null;
        }

        if ($this->property->getTypes()->expectCollection) {
            return $this->processCollection($rawValue);
        }

        return $this->processValue($rawValue);
    }

    /**
     * Retrieve the processed value as a collection
     *
     * @param iterable $collection
     * @return array
     */
    protected function processCollection(iterable $collection): array
    {
        $processed = [];

        foreach ($collection as $value) {
            $processed[] = $this->processValue($value);
        }

        return $processed;
    }

    /**
     * Retrieve the processed value
     *
     * @param mixed $value
     * @return mixed
     */
    protected function processValue($value)
    {
        $types = $this->property->getTypes();

        if ($converter = $types->expectedConverter) {
            return $converter->toDto($value);
        } elseif ($types->expectedDto) {
            return $this->castValueIntoDto($value);
        } elseif (($this->property->getFlags() & CAST_PRIMITIVES) && $type = $types->expectedPrimitive) {
            settype($value, $type);
        }

        return $value;
    }

    /**
     * Retrieve the given value casted into a DTO
     *
     * @param mixed $value
     * @return Dto
     */
    protected function castValueIntoDto($value)
    {
        $dto = $this->property->getTypes()->expectedDto;

        return is_a($value, $dto) ? $value : $dto::make($value, $this->property->getFlags());
    }
}
