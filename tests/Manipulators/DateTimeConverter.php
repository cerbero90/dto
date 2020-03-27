<?php

namespace Cerbero\Dto\Manipulators;

use DateTime;

/**
 * The date time converter.
 *
 */
class DateTimeConverter implements ValueConverter
{
    /**
     * Convert the given value to be exported from a DTO.
     *
     * @param mixed $value
     * @return mixed
     */
    public function fromDto($value)
    {
        return $value->format('Y-m-d');
    }

    /**
     * Convert the given value to be imported into a DTO.
     *
     * @param mixed $value
     * @return mixed
     */
    public function toDto($value)
    {
        return new DateTime($value);
    }
}
