<?php

namespace Cerbero\Dto\Dtos;

use Cerbero\Dto\Dto;

/**
 * DTO with default values.
 *
 * @property-read string $name
 * @property-read int $count
 * @property-read \DateTime|null $time
 */
class DtoWithDefaults extends Dto
{
    protected static $defaultValues = [
        'count' => 0,
        'time' => null,
    ];
}
