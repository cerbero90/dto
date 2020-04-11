<?php

namespace Cerbero\Dto\Traits;

use Cerbero\Dto\Dto;
use Cerbero\Dto\DtoFlagsHandler;

use const Cerbero\Dto\ARRAY_DEFAULT_TO_EMPTY_ARRAY;
use const Cerbero\Dto\BOOL_DEFAULT_TO_FALSE;
use const Cerbero\Dto\CAST_PRIMITIVES;
use const Cerbero\Dto\MUTABLE;
use const Cerbero\Dto\NONE;
use const Cerbero\Dto\NOT_NULLABLE;
use const Cerbero\Dto\NULLABLE;
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
     * Determine whether the DTO flags include the given flags
     *
     * @param int $flags
     * @return bool
     */
    public function hasFlags(int $flags): bool
    {
        return ($this->getFlags() & $flags) === $flags;
    }

    /**
     * Set the DTO flags
     *
     * @param int $flags
     * @return Dto
     */
    public function setFlags(int $flags): Dto
    {
        $currentFlags = $this->getFlags();

        if (!($currentFlags & MUTABLE)) {
            return static::make($this->toArray(), $flags);
        }

        $this->flags = $flags;

        if (($currentFlags | $flags) & $this->getFlagsAffectingValues()) {
            $this->mapData($this->toArray());
        }

        return $this;
    }

    /**
     * Retrieve the flags that affect the values of a DTO
     *
     * @return int
     */
    protected function getFlagsAffectingValues(): int
    {
        return $this->getFlagsForDefaults() | NULLABLE | NOT_NULLABLE | CAST_PRIMITIVES;
    }

    /**
     * Retrieve the flags that determine default values
     *
     * @return int
     */
    protected function getFlagsForDefaults(): int
    {
        return NULLABLE_DEFAULT_TO_NULL | BOOL_DEFAULT_TO_FALSE | ARRAY_DEFAULT_TO_EMPTY_ARRAY;
    }

    /**
     * Add the given flags to the DTO flags
     *
     * @param int $flags
     * @return Dto
     */
    public function addFlags(int $flags): Dto
    {
        $mergedFlags = $this->mergeFlags($this->getFlags(), $flags);

        return $this->setFlags($mergedFlags);
    }

    /**
     * Retrieve the merged flags
     *
     * @param int $initialFlags
     * @param int $flagsToMerge
     * @return int
     * @throws \Cerbero\Dto\Exceptions\IncompatibleDtoFlagsException
     */
    protected function mergeFlags(int $initialFlags, int $flagsToMerge): int
    {
        return (new DtoFlagsHandler())->merge($initialFlags, $flagsToMerge);
    }

    /**
     * Remove the given flags from the DTO flags
     *
     * @param int $flags
     * @return Dto
     */
    public function removeFlags(int $flags): Dto
    {
        $currentFlags = $this->getFlags();
        $remainingFlags = $currentFlags ^ ($currentFlags & $flags);

        return $this->setFlags($remainingFlags);
    }

    /**
     * Retrieve the DTO flags excluding the flags for default values
     *
     * @return int
     */
    protected function getFlagsWithoutDefaults(): int
    {
        $flags = $this->getFlags();

        return $flags ^ ($flags & $this->getFlagsForDefaults());
    }
}
