<?php

namespace Cerbero\Dto\Exceptions;

use Exception;

/**
 * Exception thrown when trying to set an unknown DTO property.
 *
 */
class UnknownDtoPropertyException extends Exception implements DtoException
{
    /**
     * Instantiate the class.
     *
     * @param string $dtoClass
     * @param string $property
     */
    public function __construct(string $dtoClass, string $property)
    {
        parent::__construct("Unknown property '{$property}' in the DTO [{$dtoClass}]");
    }
}
