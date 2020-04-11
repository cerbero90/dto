<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Dtos\PartialDto;
use Cerbero\Dto\Manipulators\ArrayConverter;
use Cerbero\Dto\Manipulators\DateTimeConverter;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DtoPropertyValueProcessor.
 *
 */
class DtoPropertyValueProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function processes_null_values()
    {
        $types = (new DtoPropertyTypes)->addType(new DtoPropertyType('null', false));
        $property = DtoProperty::create('foo', null, $types, NONE);
        $processor = new DtoPropertyValueProcessor($property);

        $this->assertNull($processor->process());
    }

    /**
     * @test
     */
    public function processes_collections()
    {
        $types = (new DtoPropertyTypes)->addType(new DtoPropertyType('bool', true));
        $property = DtoProperty::create('foo', [true], $types, NONE);
        $processor = new DtoPropertyValueProcessor($property);

        $this->assertSame([true], $processor->process());
    }

    /**
     * @test
     */
    public function processes_values_with_converter()
    {
        ArrayConverter::instance()->setConversions([DateTime::class => DateTimeConverter::class]);
        $types = (new DtoPropertyTypes)->addType(new DtoPropertyType(DateTime::class, false));
        $property = DtoProperty::create('foo', '2000-01-01', $types, NONE);
        $processor = new DtoPropertyValueProcessor($property);

        $this->assertInstanceOf(DateTime::class, $processor->process());

        ArrayConverter::instance()->setConversions([]);
    }

    /**
     * @test
     */
    public function processes_values_with_dto()
    {
        $types = (new DtoPropertyTypes)->addType(new DtoPropertyType(PartialDto::class, false));
        $property = DtoProperty::create('foo', ['name' => 'bar'], $types, NONE);
        $processor = new DtoPropertyValueProcessor($property);

        $this->assertInstanceOf(PartialDto::class, $processor->process());
    }

    /**
     * @test
     */
    public function processes_values_with_dto_instance()
    {
        $types = (new DtoPropertyTypes)->addType(new DtoPropertyType(PartialDto::class, false));
        $property = DtoProperty::create('foo', new PartialDto, $types, NONE);
        $processor = new DtoPropertyValueProcessor($property);

        $this->assertInstanceOf(PartialDto::class, $processor->process());
    }

    /**
     * @test
     */
    public function processes_values_by_casting_primitives()
    {
        $types = (new DtoPropertyTypes)->addType(new DtoPropertyType('int', false));
        $property = DtoProperty::create('foo', '123', $types, CAST_PRIMITIVES);
        $processor = new DtoPropertyValueProcessor($property);

        $this->assertSame(123, $processor->process());
    }
}
