<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Dtos\NoPropertiesDto;
use Cerbero\Dto\Dtos\SampleDto;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DtoPropertyType.
 *
 */
class DtoPropertyTypeTest extends TestCase
{
    /**
     * @test
     */
    public function retrieves_info()
    {
        $type = new DtoPropertyType(SampleDto::class, true);

        $this->assertSame(SampleDto::class, $type->name());
        $this->assertTrue($type->isCollection());
        $this->assertTrue($type->isDto());
    }

    /**
     * @test
     */
    public function matches_collections_of_primitives()
    {
        $type = new DtoPropertyType('string', true);

        $this->assertTrue($type->matchesCollection(['foo']));
    }

    /**
     * @test
     */
    public function matches_mixed_collections()
    {
        $type = new DtoPropertyType('mixed', true);

        $this->assertTrue($type->matchesCollection(['foo', 123]));
    }

    /**
     * @test
     */
    public function matches_objects_collections()
    {
        $type = new DtoPropertyType(Dto::class, true);

        $this->assertTrue($type->matchesCollection([new NoPropertiesDto]));
    }

    /**
     * @test
     */
    public function matches_primitives()
    {
        $type = new DtoPropertyType('string', false);

        $this->assertTrue($type->matches('foo'));
    }

    /**
     * @test
     */
    public function matches_mixed_items()
    {
        $type = new DtoPropertyType('mixed', false);

        $this->assertTrue($type->matches(123));
    }

    /**
     * @test
     */
    public function matches_objects()
    {
        $type = new DtoPropertyType(Dto::class, false);

        $this->assertTrue($type->matches(new NoPropertiesDto));
    }

    /**
     * @test
     */
    public function mismatches_wrong_items()
    {
        $type = new DtoPropertyType('string', false);

        $this->assertFalse($type->matches(123));
    }

    /**
     * @test
     */
    public function mismatches_wrong_collections()
    {
        $type = new DtoPropertyType('string', true);

        $this->assertFalse($type->matchesCollection([123]));
    }

    /**
     * @test
     */
    public function mismatches_non_collections()
    {
        $type = new DtoPropertyType('int', true);

        $this->assertFalse($type->matchesCollection(123));
    }
}
