<?php

namespace Cerbero\Dto\Traits;

use Cerbero\Dto\Dto;

use const Cerbero\Dto\MUTABLE;
use const Cerbero\Dto\NONE;

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
        if (!($this->getFlags() & MUTABLE)) {
            return static::make($this->toArray(), $flags);
        }

        $this->flags = $flags;

        return $this;
    }

    /**
     * Add the given flags to the DTO flags
     *
     * @param int $flags
     * @return Dto
     */
    public function addFlags(int $flags): Dto
    {
        $mergedFlags = $this->getFlags() | $flags;

        return $this->setFlags($mergedFlags);
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
}
