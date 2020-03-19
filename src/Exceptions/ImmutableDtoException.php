<?php

namespace Cerbero\Dto\Exceptions;

use Exception;

/**
 * Exception thrown when trying to alter a value on an immutable DTO.
 *
 */
class ImmutableDtoException extends Exception implements DtoException
{
    /**
     * Instantiate the class
     *
     * @param string $dtoClass
     */
    public function __construct(string $dtoClass)
    {
        parent::__construct("Unable to alter values on the immutable DTO [{$dtoClass}]");
    }
}
