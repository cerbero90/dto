<?php

namespace Cerbero\Dto\Traits;

use Cerbero\Dto\DtoProperty;
use Cerbero\Dto\Exceptions\UnknownDtoPropertyException;

/**
 * Trait to interact with properties.
 *
 */
trait HasProperties
{
    /**
     * The properties map.
     *
     * @var array
     */
    protected $propertiesMap;

    /**
     * Retrieve the DTO properties map
     *
     * @return array
     */
    public function getPropertiesMap(): array
    {
        return $this->propertiesMap;
    }

    /**
     * Retrieve the DTO property names
     *
     * @return array
     */
    public function getPropertyNames(): array
    {
        return array_keys($this->getPropertiesMap());
    }

    /**
     * Retrieve the DTO properties
     *
     * @return DtoProperty[]
     */
    public function getProperties(): array
    {
        return array_values($this->getPropertiesMap());
    }

    /**
     * Determine whether the given property is set (even if its value is NULL)
     *
     * @param string $property
     * @return bool
     */
    public function hasProperty(string $property): bool
    {
        try {
            return !!$this->getProperty($property);
        } catch (UnknownDtoPropertyException $e) {
            return false;
        }
    }

    /**
     * Retrieve the given DTO property (support dot notation)
     *
     * @param string $property
     * @return DtoProperty
     * @throws UnknownDtoPropertyException
     */
    public function getProperty(string $property): DtoProperty
    {
        if (isset($this->propertiesMap[$property])) {
            return $this->propertiesMap[$property];
        }

        if (strpos($property, '.') === false) {
            throw new UnknownDtoPropertyException(static::class, $property);
        }

        [$property, $nestedProperty] = explode('.', $property, 2);
        $presumedDto = $this->get($property);

        if ($presumedDto instanceof self) {
            return $presumedDto->getProperty($nestedProperty);
        }

        throw new UnknownDtoPropertyException(static::class, $nestedProperty);
    }
}
