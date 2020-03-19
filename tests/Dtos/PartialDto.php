<?php

namespace Cerbero\Dto\Dtos;

use Cerbero\Dto\Dto;

use const Cerbero\Dto\PARTIAL;

/**
 * Sample partial DTO.
 *
 * @property-read string $name
 * @property-read SampleDto $sample
 * @property-read int|null $nullable
 */
class PartialDto extends Dto
{
    protected static $defaultFlags = PARTIAL;
}
