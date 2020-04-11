<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Dtos\SampleDto;
use Cerbero\Dto\Manipulators\ArrayConverter;
use Cerbero\Dto\Manipulators\DateTimeConverter;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DtoPropertyTypes.
 *
 */
class DtoPropertyTypesTest extends TestCase
{
    /**
     * The DTO property types.
     *
     * @var DtoPropertyTypes
     */
    private $types;

    /**
     * This method is called before each test.
     * 
     */
    protected function setUp(): void
    {
        $this->types = new DtoPropertyTypes;
    }

    /**
     * @test
     */
    public function adds_type()
    {
        $this->types->addType($type = new DtoPropertyType('bool', true));

        $this->assertSame($type, $this->types->all[0]);
        $this->assertFalse($this->types->includeNull);
        $this->assertFalse($this->types->includeArray);
        $this->assertTrue($this->types->includeBool);
        $this->assertTrue($this->types->expectCollection);
        $this->assertNull($this->types->expectedDto);
        $this->assertNull($this->types->expectedConverter);
        $this->assertSame('bool', $this->types->expectedPrimitive);
        $this->assertSame(['bool[]'], $this->types->declaredNames);

        $this->types->addType($type = new DtoPropertyType('null', false));

        $this->assertSame($type, $this->types->all[1]);
        $this->assertTrue($this->types->includeNull);
        $this->assertFalse($this->types->includeArray);
        $this->assertTrue($this->types->includeBool);
        $this->assertTrue($this->types->expectCollection);
        $this->assertNull($this->types->expectedDto);
        $this->assertNull($this->types->expectedConverter);
        $this->assertSame('bool', $this->types->expectedPrimitive);
        $this->assertSame(['bool[]', 'null'], $this->types->declaredNames);

        $this->types->addType($type = new DtoPropertyType('array', false));

        $this->assertSame($type, $this->types->all[2]);
        $this->assertTrue($this->types->includeNull);
        $this->assertTrue($this->types->includeArray);
        $this->assertTrue($this->types->includeBool);
        $this->assertTrue($this->types->expectCollection);
        $this->assertNull($this->types->expectedDto);
        $this->assertNull($this->types->expectedConverter);
        $this->assertSame('bool', $this->types->expectedPrimitive);
        $this->assertSame(['bool[]', 'null', 'array'], $this->types->declaredNames);

        $this->types->addType($type = new DtoPropertyType(SampleDto::class, false));

        $this->assertSame($type, $this->types->all[3]);
        $this->assertTrue($this->types->includeNull);
        $this->assertTrue($this->types->includeArray);
        $this->assertTrue($this->types->includeBool);
        $this->assertTrue($this->types->expectCollection);
        $this->assertSame(SampleDto::class, $this->types->expectedDto);
        $this->assertNull($this->types->expectedConverter);
        $this->assertSame('bool', $this->types->expectedPrimitive);
        $this->assertSame(['bool[]', 'null', 'array', SampleDto::class], $this->types->declaredNames);

        ArrayConverter::instance()->setConversions([DateTime::class => DateTimeConverter::class]);
        $this->types->addType($type = new DtoPropertyType(DateTime::class, false));

        $this->assertSame($type, $this->types->all[4]);
        $this->assertTrue($this->types->includeNull);
        $this->assertTrue($this->types->includeArray);
        $this->assertTrue($this->types->includeBool);
        $this->assertTrue($this->types->expectCollection);
        $this->assertSame(SampleDto::class, $this->types->expectedDto);
        $this->assertInstanceOf(DateTimeConverter::class, $this->types->expectedConverter);
        $this->assertSame('bool', $this->types->expectedPrimitive);
        $this->assertSame(['bool[]', 'null', 'array', SampleDto::class, DateTime::class], $this->types->declaredNames);

        ArrayConverter::instance()->setConversions([]);

        $this->assertTrue($this->types->includeNull);
        $this->assertTrue($this->types->includeArray);
        $this->assertTrue($this->types->includeBool);
        $this->assertTrue($this->types->expectCollection);
        $this->assertSame(SampleDto::class, $this->types->expectedDto);
        $this->assertInstanceOf(DateTimeConverter::class, $this->types->expectedConverter);
        $this->assertSame('bool', $this->types->expectedPrimitive);
        $this->assertSame(['bool[]', 'null', 'array', SampleDto::class, DateTime::class], $this->types->declaredNames);
    }

    /**
     * @test
     */
    public function matches_a_value()
    {
        $this->types->addType(new DtoPropertyType('bool', false));
        $this->types->addType(new DtoPropertyType('null', false));

        $this->assertTrue($this->types->match(false));
    }

    /**
     * @test
     */
    public function mismatches_a_value()
    {
        $this->types->addType(new DtoPropertyType('bool', false));
        $this->types->addType(new DtoPropertyType('null', false));

        $this->assertFalse($this->types->match(123));
    }
}
