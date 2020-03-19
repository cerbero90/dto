<?php

namespace Cerbero\Dto\Exceptions;

use Cerbero\Dto\DtoProperty;
use TypeError;

/**
 * Exception thrown when a value set in a DTO property is unexpected.
 *
 */
class UnexpectedValueException extends TypeError implements DtoException
{
    /**
     * The failing DTO property.
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

        parent::__construct($this->getExceptionMessage());
    }

    /**
     * Retrieve the exception message
     *
     * @return string
     */
    protected function getExceptionMessage(): string
    {
        $name = $this->property->getName();
        $declaredNames = $this->property->getTypes()->declaredNames;
        $typeNames = implode("', '", $declaredNames);
        $types = count($declaredNames) == 1 ? "of type '{$typeNames}'" : "one of these types: '{$typeNames}'";
        $value = $this->property->getRawValue();
        $actualType = gettype($value);

        if ($value === null) {
            $value = 'null';
        } elseif (is_array($value)) {
            $value = 'array';
        } elseif (is_object($value)) {
            $value = get_class($value);
        }

        return "Invalid type: expected '{$name}' to be {$types}. Got `{$value}` ({$actualType}) instead";
    }
}
