<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Dtos\NoPropertiesDto;
use Cerbero\Dto\Exceptions\UnexpectedValueException;
use Cerbero\Dto\Manipulators\ArrayConverter;
use Cerbero\Dto\Manipulators\DateTimeConverter;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DtoProperty.
 *
 */
class DtoPropertyTest extends TestCase
{
    /**
     * @test
     */
    public function validation_fails_if_property_should_not_be_null()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Invalid type: expected 'foo' to be of type 'string'. Got `null` (NULL) instead");

        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('string', false));

        DtoProperty::create('foo', null, $types, NONE);
    }

    /**
     * @test
     */
    public function validation_fails_if_nullable_property_is_flagged_as_not_nullable()
    {
        $error = "Invalid type: expected 'foo' to be one of these types: 'string', 'null'. Got `null` (NULL) instead";

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($error);

        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('string', false));
        $types->addType(new DtoPropertyType('null', false));

        DtoProperty::create('foo', null, $types, NOT_NULLABLE);
    }

    /**
     * @test
     */
    public function validation_fails_if_property_is_supposed_to_be_a_dto_but_cannot()
    {
        $dto = NoPropertiesDto::class;
        $error = "Invalid type: expected 'foo' to be of type '{$dto}'. Got `null` (NULL) instead";

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($error);

        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType($dto, false));

        DtoProperty::create('foo', null, $types, NONE);
    }

    /**
     * @test
     */
    public function validation_fails_if_property_is_supposed_to_be_a_collection_but_cannot()
    {
        $error = "Invalid type: expected 'foo' to be of type 'bool[]'. Got `null` (NULL) instead";

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($error);

        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('bool', true));

        DtoProperty::create('foo', null, $types, NONE);
    }

    /**
     * @test
     */
    public function validation_succeeds_when_setting_property_to_null_on_nullable_dto()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('string', false));

        $property = DtoProperty::create('foo', null, $types, NULLABLE);

        $this->assertInstanceOf(DtoProperty::class, $property);
    }

    /**
     * @test
     */
    public function validation_succeeds_when_setting_nullable_property_to_null()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('null', false));

        $property = DtoProperty::create('foo', null, $types, NONE);

        $this->assertInstanceOf(DtoProperty::class, $property);
    }

    /**
     * @test
     */
    public function validation_succeeds_when_setting_dto_from_array()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType(NoPropertiesDto::class, false));

        $property = DtoProperty::create('foo', [], $types, NONE);

        $this->assertInstanceOf(DtoProperty::class, $property);
    }

    /**
     * @test
     */
    public function validation_succeeds_when_setting_dto_from_dto()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType(NoPropertiesDto::class, false));

        $property = DtoProperty::create('foo', new NoPropertiesDto, $types, NONE);

        $this->assertInstanceOf(DtoProperty::class, $property);
    }

    /**
     * @test
     */
    public function validation_succeeds_when_setting_a_collection_from_an_iterable()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('bool', true));

        $property = DtoProperty::create('foo', [true], $types, NONE);

        $this->assertInstanceOf(DtoProperty::class, $property);
    }

    /**
     * @test
     */
    public function validation_fails_if_property_value_does_not_match_types()
    {
        $error = "Invalid type: expected 'foo' to be one of these types: 'string', 'null'. Got `123` (integer) instead";

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($error);

        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('string', false));
        $types->addType(new DtoPropertyType('null', false));

        DtoProperty::create('foo', 123, $types, NONE);
    }

    /**
     * @test
     */
    public function validation_fails_if_array_does_not_match_types()
    {
        $error = "Invalid type: expected 'foo' to be one of these types: 'string', 'null'. Got `array` (array) instead";

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($error);

        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('string', false));
        $types->addType(new DtoPropertyType('null', false));

        DtoProperty::create('foo', [123], $types, NONE);
    }

    /**
     * @test
     */
    public function validation_fails_if_object_does_not_match_types()
    {
        $error = "Invalid type: expected 'foo' to be of type 'string'. Got `Cerbero\Dto\SampleClass` (object) instead";

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($error);

        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('string', false));

        DtoProperty::create('foo', new SampleClass, $types, NONE);
    }

    /**
     * @test
     */
    public function validation_succeeds_if_property_value_matches_types()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('string', true));

        $property = DtoProperty::create('foo', ['foo'], $types, NONE);

        $this->assertInstanceOf(DtoProperty::class, $property);
    }

    /**
     * @test
     */
    public function can_be_flagged_as_not_nullable()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('int', false));
        $types->addType(new DtoPropertyType('null', false));

        $property = DtoProperty::create('foo', 123, $types, NOT_NULLABLE);

        $this->assertFalse($property->isNullable());
    }

    /**
     * @test
     */
    public function can_be_flagged_as_nullable()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('int', false));

        $property = DtoProperty::create('foo', 123, $types, NULLABLE);

        $this->assertTrue($property->isNullable());
    }

    /**
     * @test
     */
    public function can_be_nullable()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('null', false));

        $property = DtoProperty::create('foo', null, $types, NONE);

        $this->assertTrue($property->isNullable());
    }

    /**
     * @test
     */
    public function processes_raw_values_once()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType('int', false));

        $property = DtoProperty::create('foo', 123, $types, NULLABLE);
        $processedValue = $property->value();

        $this->assertSame($processedValue, $property->getRawValue());
        $this->assertSame($processedValue, $property->value());
    }

    /**
     * @test
     */
    public function processes_dtos_once()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType(NoPropertiesDto::class, false));

        $property = DtoProperty::create('foo', [], $types, NONE);
        $processedValue = $property->value();

        $this->assertNotSame($processedValue, $property->getRawValue());
        $this->assertInstanceOf(NoPropertiesDto::class, $processedValue);
        $this->assertSame($processedValue, $property->value());
    }


    /**
     * @test
     */
    public function processes_nullable_dtos_once()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType(NoPropertiesDto::class, false));
        $types->addType(new DtoPropertyType('null', false));

        $property = DtoProperty::create('foo', null, $types, NONE);
        $processedValue = $property->value();

        $this->assertSame($processedValue, $property->getRawValue());
        $this->assertSame($processedValue, $property->value());
    }

    /**
     * @test
     */
    public function processes_dto_collections_once()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType(NoPropertiesDto::class, true));

        $property = DtoProperty::create('foo', [[]], $types, NONE);
        $processedValue = $property->value();

        $this->assertNotSame($processedValue, $property->getRawValue());
        $this->assertInstanceOf(NoPropertiesDto::class, $processedValue[0]);
        $this->assertSame($processedValue[0], $property->value()[0]);
    }

    /**
     * @test
     */
    public function retrieves_info()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType(NoPropertiesDto::class, false));

        $property = DtoProperty::create('foo', [], $types, MUTABLE);

        $this->assertSame('foo', $property->getName());
        $this->assertSame([], $property->getRawValue());
        $this->assertSame($types, $property->getTypes());
        $this->assertSame(MUTABLE, $property->getFlags());
        $this->assertInstanceOf(NoPropertiesDto::class, $property->value());
    }

    /**
     * @test
     */
    public function overrides_value_and_flags()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType(NoPropertiesDto::class, false));

        $property = DtoProperty::create('foo', [], $types, MUTABLE);
        $property->setValue(null, NULLABLE);

        $this->assertSame('foo', $property->getName());
        $this->assertSame(null, $property->getRawValue());
        $this->assertSame($types, $property->getTypes());
        $this->assertSame(NULLABLE, $property->getFlags());
        $this->assertSame(null, $property->value());
    }

    /**
     * @test
     */
    public function clones_types()
    {
        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType(NoPropertiesDto::class, false));

        $property = DtoProperty::create('foo', [], $types, MUTABLE);
        $clone = clone $property;

        $this->assertSame($types, $property->getTypes());
        $this->assertNotSame($types, $clone->getTypes());
    }

    /**
     * @test
     */
    public function array_converter_can_alter_processed_values()
    {
        ArrayConverter::instance()->setConversions([
            DateTime::class => DateTimeConverter::class,
        ]);

        $types = new DtoPropertyTypes;
        $types->addType(new DtoPropertyType(DateTime::class, false));

        $property = DtoProperty::create('foo', '2020-01-01', $types, MUTABLE);

        $this->assertInstanceOf(DateTime::class, $property->value());
    }
}
