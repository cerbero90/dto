<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Dtos\NoPropertiesDto;
use Cerbero\Dto\Dtos\PartialDto;
use Cerbero\Dto\Exceptions\ImmutableDtoException;
use Cerbero\Dto\Exceptions\UnknownDtoPropertyException;
use Cerbero\Dto\Exceptions\UnsetDtoPropertyException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dto.
 *
 */
class DtoTest extends TestCase
{
    /**
     * @test
     */
    public function maps_properties_and_merge_flags_when_instantiated()
    {
        $dto = new PartialDto(['name' => 'foo'], MUTABLE);

        $this->assertSame($dto->getFlags(), PARTIAL | MUTABLE);
        $this->assertCount(1, $dto->getPropertiesMap());
        $this->assertSame(['name'], $dto->getPropertyNames());
        $this->assertCount(1, $dto->getProperties());
        $this->assertTrue($dto->hasProperty('name'));
        $this->assertInstanceOf(DtoProperty::class, $dto->getProperty('name'));
        $this->assertTrue($dto->has('name'));
        $this->assertSame('foo', $dto->get('name'));
    }

    /**
     * @test
     */
    public function can_be_instantiated_statically()
    {
        $dto = PartialDto::make(['name' => 'foo'], IGNORE_UNKNOWN_PROPERTIES);

        $this->assertSame($dto->getFlags(), PARTIAL | IGNORE_UNKNOWN_PROPERTIES);
        $this->assertCount(1, $dto->getPropertiesMap());
        $this->assertSame(['name'], $dto->getPropertyNames());
        $this->assertCount(1, $dto->getProperties());
        $this->assertTrue($dto->hasProperty('name'));
        $this->assertInstanceOf(DtoProperty::class, $dto->getProperty('name'));
        $this->assertTrue($dto->has('name'));
        $this->assertSame('foo', $dto->get('name'));
    }

    /**
     * @test
     */
    public function retrieves_default_flags()
    {
        $this->assertSame(PARTIAL, PartialDto::getDefaultFlags());
    }

    /**
     * @test
     */
    public function sets_and_gets_singleton_of_array_converter()
    {
        $converter = PartialDto::getArrayConverter();

        $this->assertInstanceOf(ArrayConverter::class, $converter);

        PartialDto::setArrayConverter($converter);

        $this->assertSame($converter, PartialDto::getArrayConverter());
    }

    /**
     * @test
     */
    public function returns_false_if_property_is_missing()
    {
        $dto = new PartialDto(['name' => 'foo']);

        $this->assertFalse($dto->hasProperty('bar'));
    }

    /**
     * @test
     */
    public function fails_when_trying_to_get_a_missing_property()
    {
        $this->expectException(UnknownDtoPropertyException::class);
        $this->expectExceptionMessage("Unknown property 'bar' in the DTO [Cerbero\Dto\Dtos\PartialDto]");

        PartialDto::make(['name' => 'foo'])->getProperty('bar');
    }

    /**
     * @test
     */
    public function fails_when_trying_to_get_a_missing_property_with_dot_notation()
    {
        $this->expectException(UnknownDtoPropertyException::class);
        $this->expectExceptionMessage("Unknown property 'bar' in the DTO [Cerbero\Dto\Dtos\PartialDto]");

        PartialDto::make(['name' => 'foo'])->getProperty('bar.baz');
    }

    /**
     * @test
     */
    public function retrieves_nested_properties()
    {
        $property = PartialDto::make(['sample' => ['enabled' => true]])->getProperty('sample.enabled');

        $this->assertInstanceOf(DtoProperty::class, $property);
        $this->assertTrue($property->value());
    }

    /**
     * @test
     */
    public function fails_when_trying_to_get_a_missing_nested_property()
    {
        $this->expectException(UnknownDtoPropertyException::class);
        $this->expectExceptionMessage("Unknown property 'bar' in the DTO [Cerbero\Dto\Dtos\PartialDto]");

        PartialDto::make(['name' => 'foo'])->getProperty('name.bar');
    }

    /**
     * @test
     */
    public function fails_when_trying_to_get_a_missing_nested_dto_property()
    {
        $this->expectException(UnknownDtoPropertyException::class);
        $this->expectExceptionMessage("Unknown property 'missing' in the DTO [Cerbero\Dto\Dtos\SampleDto]");

        PartialDto::make(['sample' => ['enabled' => true]])->getProperty('sample.missing');
    }

    /**
     * @test
     */
    public function returns_false_when_trying_to_check_if_a_missing_property_has_a_value()
    {
        $dto = PartialDto::make(['name' => 'foo']);

        $this->assertFalse($dto->has('sample'));
    }

    /**
     * @test
     */
    public function returns_false_when_trying_to_check_if_a_nullable_property_has_a_value()
    {
        $dto = PartialDto::make(['nullable' => null]);

        $this->assertFalse($dto->has('nullable'));
    }

    /**
     * @test
     */
    public function retrieves_values()
    {
        $dto = PartialDto::make(['name' => 'foo']);

        $this->assertSame('foo', $dto->get('name'));
    }

    /**
     * @test
     */
    public function created_a_new_instance_with_modified_values_if_immutable()
    {
        $dto1 = PartialDto::make(['name' => 'foo']);
        $dto2 = $dto1->set('name', 'bar');

        $this->assertSame('foo', $dto1->name);
        $this->assertSame('bar', $dto2->name);
        $this->assertNotSame($dto1, $dto2);
    }

    /**
     * @test
     */
    public function modifies_existing_values_if_mutable()
    {
        $dto1 = PartialDto::make(['name' => 'foo'], MUTABLE);
        $dto2 = $dto1->set('name', 'bar');

        $this->assertSame('bar', $dto1->name);
        $this->assertSame('bar', $dto2->name);
        $this->assertSame($dto1, $dto2);
    }

    /**
     * @test
     */
    public function does_not_fail_setting_missing_properties_if_flagged()
    {
        $dto1 = PartialDto::make(['name' => 'foo'], IGNORE_UNKNOWN_PROPERTIES);
        $dto2 = $dto1->set('missing', 123);

        $this->assertFalse($dto2->hasProperty('missing'));
    }

    /**
     * @test
     */
    public function fails_when_setting_missing_properties()
    {
        $this->expectException(UnknownDtoPropertyException::class);
        $this->expectExceptionMessage("Unknown property 'missing' in the DTO [Cerbero\Dto\Dtos\PartialDto]");

        PartialDto::make(['name' => 'foo'])->set('missing', 123);
    }

    /**
     * @test
     */
    public function can_be_cloned()
    {
        $dto1 = PartialDto::make(['name' => 'foo']);
        $dto2 = $dto1->clone();

        $this->assertNotSame($dto1, $dto2);
        $this->assertSame($dto1->toArray(), $dto2->toArray());
    }

    /**
     * @test
     */
    public function merges_iterables()
    {
        $dto1 = PartialDto::make(['name' => 'foo']);
        $dataToMerge = [
            'name' => 'bar',
            'sample' => [
                'enabled' => true,
            ],
        ];

        $dto2 = $dto1->merge($dataToMerge, BOOL_DEFAULT_TO_FALSE);

        $this->assertNotSame($dto1, $dto2);
        $this->assertSame('foo', $dto1->name);
        $this->assertSame('bar', $dto2->name);
        $this->assertFalse($dto1->hasProperty('sample'));
        $this->assertTrue($dto2->hasProperty('sample'));
        $this->assertTrue($dto2->sample->enabled);
        $this->assertSame(PARTIAL, $dto1->getFlags());
        $this->assertSame(PARTIAL | BOOL_DEFAULT_TO_FALSE, $dto2->getFlags());
    }

    /**
     * @test
     */
    public function merges_dtos()
    {
        $dto1 = PartialDto::make(['name' => 'foo']);
        $dataToMerge = PartialDto::make([
            'name' => 'bar',
            'sample' => [
                'enabled' => true,
            ],
        ]);

        $dto2 = $dto1->merge($dataToMerge, ARRAY_DEFAULT_TO_EMPTY_ARRAY);

        $this->assertNotSame($dto1, $dto2);
        $this->assertSame('foo', $dto1->name);
        $this->assertSame('bar', $dto2->name);
        $this->assertFalse($dto1->hasProperty('sample'));
        $this->assertTrue($dto2->hasProperty('sample'));
        $this->assertTrue($dto2->sample->enabled);
        $this->assertSame(PARTIAL, $dto1->getFlags());
        $this->assertSame(PARTIAL | ARRAY_DEFAULT_TO_EMPTY_ARRAY, $dto2->getFlags());
    }

    /**
     * @test
     */
    public function merges_data_in_the_same_instance_if_mutable()
    {
        $dto1 = PartialDto::make(['name' => 'foo'], MUTABLE);
        $dataToMerge = [
            'name' => 'bar',
            'sample' => [
                'enabled' => true,
            ],
        ];

        $dto2 = $dto1->merge($dataToMerge, BOOL_DEFAULT_TO_FALSE);

        $this->assertSame($dto1, $dto2);
        $this->assertSame('bar', $dto1->name);
        $this->assertTrue($dto1->hasProperty('sample'));
        $this->assertTrue($dto1->sample->enabled);
        $this->assertSame(PARTIAL | BOOL_DEFAULT_TO_FALSE | MUTABLE, $dto1->getFlags());
    }

    /**
     * @test
     */
    public function can_be_converted_into_array()
    {
        $data = [
            'name' => 'bar',
            'sample' => [
                'enabled' => true,
            ],
        ];

        $dto = PartialDto::make($data);

        $this->assertSame($data, $dto->toArray());
    }

    /**
     * @test
     */
    public function can_be_converted_into_json()
    {
        $dto = PartialDto::make([
            'name' => 'bar',
            'sample' => [
                'enabled' => true,
            ],
        ]);

        $json = '{"name":"bar","sample":{"enabled":true}}';

        $this->assertSame($json, $dto->toJson());
    }

    /**
     * @test
     */
    public function retrieves_the_iterator()
    {
        $data = [
            'name' => 'bar',
            'sample' => [
                'enabled' => true,
            ],
        ];

        $dto = PartialDto::make($data);
        $iterator = $dto->getIterator();

        $this->assertSame($data, iterator_to_array($iterator));
    }

    /**
     * @test
     */
    public function returns_true_when_trying_to_check_if_a_set_attribute_has_a_value_as_an_array()
    {
        $dto = PartialDto::make(['name' => 'foo']);

        $this->assertTrue(isset($dto['name']));
    }

    /**
     * @test
     */
    public function returns_false_when_trying_to_check_if_a_missing_attribute_has_a_value_as_an_array()
    {
        $dto = PartialDto::make(['name' => 'foo']);

        $this->assertFalse(isset($dto['sample']));
    }

    /**
     * @test
     */
    public function returns_false_when_trying_to_check_if_a_nullable_attribute_has_a_value_as_an_array()
    {
        $dto = PartialDto::make(['nullable' => null]);

        $this->assertFalse(isset($dto['nullable']));
    }

    /**
     * @test
     */
    public function returns_property_value_when_reading_as_an_array()
    {
        $dto = PartialDto::make(['name' => 'foo']);

        $this->assertSame('foo', $dto['name']);
    }

    /**
     * @test
     */
    public function throws_exception_when_reading_a_property_not_set_as_an_array()
    {
        $this->expectException(UnknownDtoPropertyException::class);
        $this->expectExceptionMessage("Unknown property 'sample' in the DTO [Cerbero\Dto\Dtos\PartialDto]");

        $dto = PartialDto::make(['name' => 'foo']);
        $dto['sample'];
    }

    /**
     * @test
     */
    public function throws_exception_when_setting_value_of_immutable_dto_as_an_array()
    {
        $this->expectException(ImmutableDtoException::class);
        $this->expectExceptionMessage("Unable to alter values on the immutable DTO [Cerbero\Dto\Dtos\PartialDto]");

        $dto = PartialDto::make(['name' => 'foo']);
        $dto['name'] = 'bar';
    }

    /**
     * @test
     */
    public function sets_values_of_mutable_dtos_as_an_array()
    {
        $dto = PartialDto::make(['name' => 'foo'], MUTABLE);
        $dto['name'] = 'bar';

        $this->assertSame('bar', $dto->name);
    }

    /**
     * @test
     */
    public function fails_when_unsetting_a_property_of_immutable_dto()
    {
        $this->expectException(ImmutableDtoException::class);
        $this->expectExceptionMessage("Unable to alter values on the immutable DTO [Cerbero\Dto\Dtos\PartialDto]");

        $dto = PartialDto::make(['name' => 'foo']);
        unset($dto['name']);
    }

    /**
     * @test
     */
    public function fails_when_unsetting_a_property_of_not_partial_dto()
    {
        $error = "Unable to unset property 'name'. DTO [Cerbero\Dto\Dtos\NoPropertiesDto] does not accept partial data";

        $this->expectException(UnsetDtoPropertyException::class);
        $this->expectExceptionMessage($error);

        $dto = NoPropertiesDto::make([], MUTABLE);
        unset($dto['name']);
    }

    /**
     * @test
     */
    public function fails_when_unsetting_a_property_that_is_not_set()
    {
        $this->expectException(UnknownDtoPropertyException::class);
        $this->expectExceptionMessage("Unknown property 'name' in the DTO [Cerbero\Dto\Dtos\PartialDto]");

        $dto = PartialDto::make([], MUTABLE);
        unset($dto['name']);
    }

    /**
     * @test
     */
    public function unsets_properties_of_mutable_and_partial_dtos()
    {
        $dto = PartialDto::make(['name' => 'foo'], MUTABLE);
        unset($dto['name']);

        $this->assertFalse($dto->hasProperty('name'));
    }

    /**
     * @test
     */
    public function serializes()
    {
        $dto1 = PartialDto::make(['name' => 'foo'], NULLABLE);
        $serialized = $dto1->serialize();

        [$data, $flags] = unserialize($serialized);

        $this->assertSame(['name' => 'foo'], $data);
        $this->assertSame(NULLABLE | PARTIAL, $flags);

        $dto2 = PartialDto::make();
        $dto2->unserialize($serialized);

        $this->assertSame(['name' => 'foo'], $dto2->toArray());
        $this->assertSame(NULLABLE | PARTIAL, $dto2->getFlags());
    }

    /**
     * @test
     */
    public function turns_data_into_json_when_json_serialized()
    {
        $dto = PartialDto::make(['name' => 'foo']);

        $this->assertSame(['name' => 'foo'], $dto->jsonSerialize());
        $this->assertSame('{"name":"foo"}', json_encode($dto));
    }

    /**
     * @test
     */
    public function returns_true_when_trying_to_check_if_a_set_attribute_has_a_value()
    {
        $dto = PartialDto::make(['name' => 'foo']);

        $this->assertTrue(isset($dto->name));
    }

    /**
     * @test
     */
    public function returns_false_when_trying_to_check_if_a_missing_attribute_has_a_value()
    {
        $dto = PartialDto::make(['name' => 'foo']);

        $this->assertFalse(isset($dto->sample));
    }

    /**
     * @test
     */
    public function returns_false_when_trying_to_check_if_a_nullable_attribute_has_a_value()
    {
        $dto = PartialDto::make(['nullable' => null]);

        $this->assertFalse(isset($dto->nullable));
    }

    /**
     * @test
     */
    public function returns_property_value_when_reading()
    {
        $dto = PartialDto::make(['name' => 'foo']);

        $this->assertSame('foo', $dto->name);
    }

    /**
     * @test
     */
    public function throws_exception_when_reading_a_property_not_set()
    {
        $this->expectException(UnknownDtoPropertyException::class);
        $this->expectExceptionMessage("Unknown property 'sample' in the DTO [Cerbero\Dto\Dtos\PartialDto]");

        $dto = PartialDto::make(['name' => 'foo']);
        $dto->sample;
    }

    /**
     * @test
     */
    public function throws_exception_when_setting_value_of_immutable_dto()
    {
        $this->expectException(ImmutableDtoException::class);
        $this->expectExceptionMessage("Unable to alter values on the immutable DTO [Cerbero\Dto\Dtos\PartialDto]");

        $dto = PartialDto::make(['name' => 'foo']);
        $dto->name = 'bar';
    }

    /**
     * @test
     */
    public function sets_values_of_mutable_dtos()
    {
        $dto = PartialDto::make(['name' => 'foo'], MUTABLE);
        $dto->name = 'bar';

        $this->assertSame('bar', $dto->name);
    }

    /**
     * @test
     */
    public function turns_into_a_string()
    {
        $dto = PartialDto::make(['name' => 'foo'], MUTABLE);

        $this->assertSame('{"name":"foo"}', (string) $dto);
    }
}
