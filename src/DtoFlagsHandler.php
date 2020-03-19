<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Exceptions\IncompatibleDtoFlagsException;

/**
 * The DTO flags handler.
 *
 */
class DtoFlagsHandler
{
    /**
     * Merge the given flags
     *
     * @param int $initialFlags
     * @param int $flagsToMerge
     * @return int
     */
    public function merge(int $initialFlags, int $flagsToMerge): int
    {
        $this->validateFlags($initialFlags);
        $this->validateFlags($flagsToMerge);

        return $this->overrideFlags($initialFlags, $flagsToMerge);
    }

    /**
     * Check whether the given flags are incompatible
     *
     * @param int $flags
     * @return void
     * @throws IncompatibleDtoFlagsException
     */
    public function validateFlags(int $flags): void
    {
        $incompatible = NULLABLE | NOT_NULLABLE;

        if (($flags & $incompatible) === $incompatible) {
            throw new IncompatibleDtoFlagsException(['NULLABLE', 'NOT_NULLABLE']);
        }
    }

    /**
     * Override the DTO default flags with the given flags
     *
     * @param int $initialFlags
     * @param int $overridingFlags
     * @return int
     */
    public function overrideFlags(int $initialFlags, int $overridingFlags): int
    {
        if (($initialFlags & NULLABLE) && ($overridingFlags & NOT_NULLABLE)) {
            $initialFlags ^= NULLABLE;
        }

        if (($initialFlags & NOT_NULLABLE) && ($overridingFlags & NULLABLE)) {
            $initialFlags ^= NOT_NULLABLE;
        }

        return $initialFlags | $overridingFlags;
    }
}
