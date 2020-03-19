<?php

namespace Cerbero\Dto\Exceptions;

use Exception;

/**
 * Exception thrown when a DTO does not have a doc comment.
 *
 */
class InvalidDocCommentException extends Exception implements DtoException
{
    /**
     * Instantiate the class
     *
     * @param string $dtoClass
     */
    public function __construct(string $dtoClass)
    {
        parent::__construct("The DTO [{$dtoClass}] does not have declared properties");
    }
}
