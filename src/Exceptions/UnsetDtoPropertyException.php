<?php

namespace Cerbero\Dto\Exceptions;

use Exception;

/**
 * Exception thrown when trying to unset a property in a non-partial DTO.
 *
 */
class UnsetDtoPropertyException extends Exception implements DtoException
{
    /**
     * Instantiate the class.
     *
     * @param string $dtoClass
     * @param string $property
     */
    public function __construct(string $dtoClass, string $property)
    {
        parent::__construct("Unable to unset property '{$property}'. DTO [{$dtoClass}] does not accept partial data");
    }
}
