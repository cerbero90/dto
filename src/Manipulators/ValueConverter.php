<?php

namespace Cerbero\Dto\Manipulators;

/**
 * The converter for a specific value.
 *
 */
interface ValueConverter
{
    /**
     * Convert the given value to be exported from a DTO.
     *
     * @param mixed $value
     * @return mixed
     */
    public function fromDto($value);

    /**
     * Convert the given value to be imported into a DTO.
     *
     * @param mixed $value
     * @return mixed
     */
    public function toDto($value);
}
