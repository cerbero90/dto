<?php

namespace Cerbero\Dto\Manipulators;

/**
 * The partial DTO listener.
 *
 */
class PartialDtoListener
{
    /**
     * Listen when setting nullable
     *
     * @param mixed $value
     * @return int
     */
    public function setNullable($value): int
    {
        return 123;
    }

    /**
     * Listen when getting nullable
     *
     * @param mixed $value
     * @return int
     */
    public function getNullable($value): int
    {
        return 321;
    }

    /**
     * Sample getter
     *
     * @param mixed $value
     * @return void
     */
    public function getFoo($value): void
    {
        return;
    }
}
