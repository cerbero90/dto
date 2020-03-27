<?php

namespace Cerbero\Dto\Traits;

/**
 * Trait to turn a DTO into a string.
 *
 */
trait TurnsIntoString
{
    /**
     * Retrieve the DTO as a JSON string
     *
     * @param int $options
     * @return string|false
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Retrieve the JSON serialized DTO
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Retrieve the string representation of the DTO
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
