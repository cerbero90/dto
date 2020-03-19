<?php

namespace Cerbero\Dto\Dtos;

use Cerbero\Dto\Dto;
use Cerbero\Dto\SampleClass;

/**
 * Sample DTO.
 *
 * @property-read \stdClass|null $object
 * @property-read NoPropertiesDto[] $dtos
 * @property-read SampleClass $sample
 * @property-read string $name
 * @property-read bool $enabled
 */
class SampleDto extends Dto
{
    //
}
