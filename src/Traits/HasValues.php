<?php

namespace Cerbero\Dto\Traits;

use Cerbero\Dto\Exceptions\UnknownDtoPropertyException;
use Cerbero\Dto\Exceptions\UnsetDtoPropertyException;
use Cerbero\Dto\Manipulators\Listener;

use const Cerbero\Dto\IGNORE_UNKNOWN_PROPERTIES;
use const Cerbero\Dto\MUTABLE;
use const Cerbero\Dto\PARTIAL;

/**
 * Trait to interact with values.
 *
 */
trait HasValues
{
    /**
     * Determine whether the given property has a value (return FALSE if the value is NULL)
     *
     * @param string $property
     * @return bool
     */
    public function has(string $property): bool
    {
        try {
            return $this->get($property) !== null;
        } catch (UnknownDtoPropertyException $e) {
            return false;
        }
    }

    /**
     * Retrieve the given property value
     *
     * @param string $property
     * @return mixed
     * @throws UnknownDtoPropertyException
     */
    public function get(string $property)
    {
        $value = $this->getProperty($property)->value();

        return $this->getListener()->getting(static::class, $property, $value);
    }

    /**
     * Retrieve the listener instance
     *
     * @return Listener
     */
    protected function getListener(): Listener
    {
        return Listener::instance();
    }

    /**
     * Set the given property to the provided value
     *
     * @param string $property
     * @param mixed $value
     * @return self
     * @throws UnknownDtoPropertyException
     */
    public function set(string $property, $value): self
    {
        $flags = $this->getFlags();
        $dto = ($flags & MUTABLE) ? $this : $this->clone();

        try {
            $value = $this->getListener()->setting(static::class, $property, $value);
            $dto->setPropertyValueOrMap($property, $value);
        } catch (UnknownDtoPropertyException $e) {
            if (!($flags & IGNORE_UNKNOWN_PROPERTIES)) {
                throw $e;
            }
        }

        return $dto;
    }

    /**
     * Unset the given property
     *
     * @param string $property
     * @return self
     * @throws UnsetDtoPropertyException
     * @throws UnknownDtoPropertyException
     */
    public function unset(string $property): self
    {
        $flags = $this->getFlags();

        if (!($flags & PARTIAL)) {
            throw new UnsetDtoPropertyException(static::class, $property);
        }

        if (strpos($property, '.') !== false) {
            [$property, $nestedProperty] = explode('.', $property, 2);
            $unsetDto = $this->get($property)->unset($nestedProperty);
            return $this->set($property, $unsetDto);
        }

        if ($flags & MUTABLE) {
            unset($this->propertiesMap[$property]);
            return $this;
        }

        $data = $this->toArray();
        unset($data[$property]);

        return new static($data, $flags);
    }

    /**
     * Determine whether a given property has a value
     *
     * @param string $property
     * @return bool
     */
    public function __isset(string $property): bool
    {
        return $this->offsetExists($property);
    }

    /**
     * Retrieve the given property value
     *
     * @param string $property
     * @return mixed
     * @throws UnknownDtoPropertyException
     */
    public function &__get(string $property)
    {
        return $this->offsetGet($property);
    }

    /**
     * Set the given property to the provided value
     *
     * @param string $property
     * @param mixed $value
     * @return void
     * @throws \Cerbero\Dto\Exceptions\ImmutableDtoException
     * @throws UnknownDtoPropertyException
     */
    public function __set(string $property, $value): void
    {
        $this->offsetSet($property, $value);
    }
}
