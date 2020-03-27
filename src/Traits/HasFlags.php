<?php

namespace Cerbero\Dto\Traits;

use const Cerbero\Dto\ARRAY_DEFAULT_TO_EMPTY_ARRAY;
use const Cerbero\Dto\BOOL_DEFAULT_TO_FALSE;
use const Cerbero\Dto\NONE;
use const Cerbero\Dto\NULLABLE_DEFAULT_TO_NULL;

/**
 * Trait to interact with flags.
 *
 */
trait HasFlags
{
    /**
     * The default flags.
     *
     * @var int
     */
    protected static $defaultFlags = NONE;

    /**
     * The actual flags.
     *
     * @var int
     */
    protected $flags;

    /**
     * Retrieve the default flags
     *
     * @return int
     */
    public static function getDefaultFlags(): int
    {
        return static::$defaultFlags;
    }

    /**
     * Retrieve the DTO flags
     *
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * Retrieve the DTO flags excluding the flags for default values
     *
     * @return int
     */
    protected function getFlagsWithoutDefaults(): int
    {
        $flags = $this->getFlags();
        $flagsForDefaults = NULLABLE_DEFAULT_TO_NULL | BOOL_DEFAULT_TO_FALSE | ARRAY_DEFAULT_TO_EMPTY_ARRAY;

        return $flags ^ ($flags & $flagsForDefaults);
    }
}
