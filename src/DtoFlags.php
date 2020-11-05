<?php

/**
 * The flags defining how DTOs behave.
 *
 */

namespace Cerbero\Dto;

/**
 * Do not apply any special behavior.
 *
 * @var int
 */
const NONE = 0;

/**
 * Ignore data not included in the DTO properties.
 *
 * @var int
 */
const IGNORE_UNKNOWN_PROPERTIES = 1;

/**
 * Allow changing the value of DTO properties.
 *
 * @var int
 */
const MUTABLE = 1 << 1;

/**
 * Set only the properties provided and ignore missing data.
 *
 * @var int
 */
const PARTIAL = 1 << 2;

/**
 * Cast primitives into their expected type.
 *
 * @var int
 */
const CAST_PRIMITIVES = 1 << 3;

/**
 * Preserve camel case properties when turning DTO into array.
 *
 * @var int
 */
const CAMEL_CASE_ARRAY = 1 << 4;
