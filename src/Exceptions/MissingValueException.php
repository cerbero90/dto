<?php

namespace Cerbero\Dto\Exceptions;

use Exception;

/**
 * Exception thrown when a value required by the DTO is missing.
 *
 */
class MissingValueException extends Exception implements DtoException
{
    /**
     * Instantiate the class.
     *
     * @param string $dtoClass
     * @param string $property
     */
    public function __construct(string $dtoClass, string $property)
    {
        parent::__construct("The DTO [{$dtoClass}] does not accept partial data but '{$property}' is missing");
    }
}
