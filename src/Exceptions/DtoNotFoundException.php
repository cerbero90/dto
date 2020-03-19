<?php

namespace Cerbero\Dto\Exceptions;

use Exception;

/**
 * Exception thrown when a DTO cannot be found.
 *
 */
class DtoNotFoundException extends Exception implements DtoException
{
    /**
     * Instantiate the class
     *
     * @param string $dtoClass
     */
    public function __construct(string $dtoClass)
    {
        parent::__construct("Unable to find the DTO [{$dtoClass}]");
    }
}
