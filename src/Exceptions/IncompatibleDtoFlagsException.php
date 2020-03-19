<?php

namespace Cerbero\Dto\Exceptions;

use Exception;

/**
 * Exception thrown when DTO flags are incompatible.
 *
 */
class IncompatibleDtoFlagsException extends Exception implements DtoException
{
    /**
     * Instantiate the class
     *
     * @param array $flags
     */
    public function __construct(array $flags)
    {
        $list = "'" . implode("', '", $flags) . "'";

        parent::__construct("The flags {$list} are incompatible");
    }
}
