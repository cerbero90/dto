<?php

namespace Cerbero\Dto;

/**
 * The flags defining how DTOs behave.
 *
 */

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
 * Set DTO properties supposed to be an array to empty arrays by default.
 *
 * @var int
 */
const ARRAY_DEFAULT_TO_EMPTY_ARRAY = 1 << 2;

/**
 * Set nullable DTO properties to null by default.
 *
 * @var int
 */
const NULLABLE_DEFAULT_TO_NULL = 1 << 3;

/**
 * Set boolean DTO properties to false by default.
 *
 * @var int
 */
const BOOL_DEFAULT_TO_FALSE = 1 << 4;

/**
 * Set only the properties provided and ignore missing data.
 *
 * @var int
 */
const PARTIAL = 1 << 5;

/**
 * Make all DTO properties nullable.
 *
 * @var int
 */
const NULLABLE = 1 << 6;

/**
 * Make all DTO properties required.
 *
 * @var int
 */
const NOT_NULLABLE = 1 << 7;
