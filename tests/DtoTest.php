<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Dtos\CamelCaseDto;
use Cerbero\Dto\Dtos\DtoWithDefaults;
use Cerbero\Dto\Dtos\NoPropertiesDto;
use Cerbero\Dto\Dtos\PartialDto;
use Cerbero\Dto\Exceptions\ImmutableDtoException;
use Cerbero\Dto\Exceptions\UnknownDtoPropertyException;
use Cerbero\Dto\Exceptions\UnsetDtoPropertyException;
use Cerbero\Dto\Manipulators\Listener;
use Cerbero\Dto\Manipulators\PartialDtoListener;
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
    public function determines_whether_flags_are_set()
    {
        $dto = PartialDto::make([], MUTABLE);

        $this->assertTrue($dto->hasFlags(PARTIAL));
        $this->assertTrue($dto->hasFlags(PARTIAL | MUTABLE));
        $this->assertFalse($dto->hasFlags(PARTIAL | MUTABLE | IGNORE_UNKNOWN_PROPERTIES));
    }

    /**
     * @test
     */
    public function sets_flags_in_same_instance_if_mutable()
    {
        $dto1 = PartialDto::make([], MUTABLE);
        $dto2 = $dto1->setFlags(PARTIAL | IGNORE_UNKNOWN_PROPERTIES);

        $this->assertSame($dto1, $dto2);
        $this->assertSame(PARTIAL | IGNORE_UNKNOWN_PROPERTIES, $dto1->getFlags());
        $this->assertSame(PARTIAL | IGNORE_UNKNOWN_PROPERTIES, $dto2->getFlags());
    }

    /**
     * @test
     */
    public function sets_flags_in_new_instance_if_immutable()
    {
        $dto1 = PartialDto::make([]);
        $dto2 = $dto1->setFlags(PARTIAL | CAST_PRIMITIVES);

        $this->assertNotSame($dto1, $dto2);
        $this->assertSame(PARTIAL, $dto1->getFlags());
        $this->assertSame(PARTIAL | CAST_PRIMITIVES, $dto2->getFlags());
    }

    /**
     * @test
     */
    public function adds_flags_in_same_instance_if_mutable()
    {
        $dto1 = PartialDto::make([], MUTABLE | CAMEL_CASE_ARRAY);
        $dto2 = $dto1->addFlags(PARTIAL | CAMEL_CASE_ARRAY);

        $this->assertSame($dto1, $dto2);
        $this->assertSame(PARTIAL | MUTABLE | CAMEL_CASE_ARRAY, $dto1->getFlags());
        $this->assertSame(PARTIAL | MUTABLE | CAMEL_CASE_ARRAY, $dto2->getFlags());
    }

    /**
     * @test
     */
    public function adds_flags_in_new_instance_if_immutable()
    {
        $dto1 = PartialDto::make([], CAST_PRIMITIVES);
        $dto2 = $dto1->addFlags(PARTIAL | IGNORE_UNKNOWN_PROPERTIES);

        $this->assertNotSame($dto1, $dto2);
        $this->assertSame(PARTIAL | CAST_PRIMITIVES, $dto1->getFlags());
        $this->assertSame(PARTIAL | CAST_PRIMITIVES | IGNORE_UNKNOWN_PROPERTIES, $dto2->getFlags());
    }

    /**
     * @test
     */
    public function removes_flags_in_same_instance_if_mutable()
    {
        $dto1 = PartialDto::make([], MUTABLE);
        $dto2 = $dto1->removeFlags(MUTABLE | CAST_PRIMITIVES);

        $this->assertSame($dto1, $dto2);
        $this->assertSame(PARTIAL, $dto1->getFlags());
        $this->assertSame(PARTIAL, $dto2->getFlags());
    }

    /**
     * @test
     */
    public function removes_flags_in_new_instance_if_immutable()
    {
        $dto1 = PartialDto::make([], IGNORE_UNKNOWN_PROPERTIES);
        $dto2 = $dto1->removeFlags(PARTIAL | IGNORE_UNKNOWN_PROPERTIES);

        $this->assertNotSame($dto1, $dto2);
        $this->assertSame(PARTIAL | IGNORE_UNKNOWN_PROPERTIES, $dto1->getFlags());
        // PARTIAL stays even if it was removed as it is a default flag in PartialDto
        $this->assertSame(PARTIAL, $dto2->getFlags());
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
    public function retrieves_values_via_listener()
    {
        Listener::instance()->listen([
            PartialDto::class => PartialDtoListener::class,
        ]);

        $dto = PartialDto::make(['nullable' => 0]);

        $this->assertSame(321, $dto->get('nullable'));

        Listener::instance()->listen([]);
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
    public function sets_values_via_listener()
    {
        Listener::instance()->listen([
            PartialDto::class => PartialDtoListener::class,
        ]);

        $dto = PartialDto::make(['nullable' => 100])->set('nullable', 0);

        $this->assertSame(123, $dto->getProperty('nullable')->value());

        Listener::instance()->listen([]);
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
    public function sets_properties_not_yet_mapped()
    {
        $dto1 = PartialDto::make(['name' => 'foo']);
        $dto2 = $dto1->set('sample.enabled', true);

        $this->assertFalse($dto1->hasProperty('sample.enabled'));
        $this->assertTrue($dto2->get('sample.enabled'));
    }

    /**
     * @test
     */
    public function sets_properties_not_yet_mapped_in_same_instance_if_mutable()
    {
        $dto1 = PartialDto::make(['name' => 'foo'], MUTABLE);
        $dto2 = $dto1->set('nullable', 123);

        $this->assertTrue($dto1->hasProperty('nullable'));
        $this->assertSame(123, $dto2->get('nullable'));
        $this->assertSame($dto1, $dto2);
    }

    /**
     * @test
     */
    public function sets_nested_properties_not_yet_mapped()
    {
        $dto1 = PartialDto::make(['name' => 'foo']);
        $dto2 = $dto1->set('sample.partial.name', 'bar');

        $this->assertFalse($dto1->hasProperty('sample.partial.name'));
        $this->assertSame('bar', $dto2->get('sample.partial.name'));
    }

    /**
     * @test
     */
    public function unset_its_own_property_when_mutable()
    {
        $dto = PartialDto::make(['name' => 'foo'], MUTABLE);
        $dto->unset('name');

        $this->assertFalse($dto->hasProperty('name'));
    }

    /**
     * @test
     */
    public function unset_property_in_a_new_instance_when_immutable()
    {
        $dto1 = PartialDto::make(['name' => 'foo']);
        $dto2 = $dto1->unset('name');

        $this->assertTrue($dto1->hasProperty('name'));
        $this->assertFalse($dto2->hasProperty('name'));
        $this->assertNotSame($dto1, $dto2);
    }

    /**
     * @test
     */
    public function fails_when_unsetting_a_property_of_not_partial_dto()
    {
        $error = "Unable to unset property 'name'. DTO [Cerbero\Dto\Dtos\NoPropertiesDto] does not accept partial data";

        $this->expectException(UnsetDtoPropertyException::class);
        $this->expectExceptionMessage($error);

        NoPropertiesDto::make([])->unset('name');
    }

    /**
     * @test
     */
    public function unsets_nested_properties_of_mutabled_dtos_in_same_instance()
    {
        $dto1 = PartialDto::make(['sample' => ['enabled' => true]], MUTABLE);
        $dto2 = $dto1->unset('sample.enabled');

        $this->assertFalse($dto1->hasProperty('sample.enabled'));
        $this->assertFalse($dto2->hasProperty('sample.enabled'));
        $this->assertSame($dto1, $dto2);
        $this->assertInstanceOf(PartialDto::class, $dto1);
        $this->assertInstanceOf(PartialDto::class, $dto2);
    }

    /**
     * @test
     */
    public function unsets_nested_properties_of_immutabled_dtos_in_cloned_instance()
    {
        $dto1 = PartialDto::make(['sample' => ['enabled' => true]]);
        $dto2 = $dto1->unset('sample.enabled');

        $this->assertTrue($dto1->hasProperty('sample.enabled'));
        $this->assertFalse($dto2->hasProperty('sample.enabled'));
        $this->assertNotSame($dto1, $dto2);
        $this->assertInstanceOf(PartialDto::class, $dto1);
        $this->assertInstanceOf(PartialDto::class, $dto2);
    }

    /**
     * @test
     */
    public function fails_when_unsetting_a_missing_nested_property()
    {
        $this->expectException(UnknownDtoPropertyException::class);
        $this->expectExceptionMessage("Unknown property 'sample' in the DTO [Cerbero\Dto\Dtos\PartialDto]");

        PartialDto::make(['name' => 'foo'])->unset('sample.name');
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

        $dto2 = $dto1->merge($dataToMerge, CAST_PRIMITIVES);

        $this->assertNotSame($dto1, $dto2);
        $this->assertSame('foo', $dto1->name);
        $this->assertSame('bar', $dto2->name);
        $this->assertFalse($dto1->hasProperty('sample'));
        $this->assertTrue($dto2->hasProperty('sample'));
        $this->assertTrue($dto2->sample->enabled);
        $this->assertSame(PARTIAL, $dto1->getFlags());
        $this->assertSame(PARTIAL | CAST_PRIMITIVES, $dto2->getFlags());
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

        $dto2 = $dto1->merge($dataToMerge, CAMEL_CASE_ARRAY);

        $this->assertNotSame($dto1, $dto2);
        $this->assertSame('foo', $dto1->name);
        $this->assertSame('bar', $dto2->name);
        $this->assertFalse($dto1->hasProperty('sample'));
        $this->assertTrue($dto2->hasProperty('sample'));
        $this->assertTrue($dto2->sample->enabled);
        $this->assertSame(PARTIAL, $dto1->getFlags());
        $this->assertSame(PARTIAL | CAMEL_CASE_ARRAY, $dto2->getFlags());
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

        $dto2 = $dto1->merge($dataToMerge, CAST_PRIMITIVES);

        $this->assertSame($dto1, $dto2);
        $this->assertSame('bar', $dto1->name);
        $this->assertTrue($dto1->hasProperty('sample'));
        $this->assertTrue($dto1->sample->enabled);
        $this->assertSame(PARTIAL | CAST_PRIMITIVES | MUTABLE, $dto1->getFlags());
    }

    /**
     * @test
     */
    public function keeps_only_some_properties()
    {
        $dto1 = PartialDto::make([
            'name' => 'bar',
            'sample' => [
                'enabled' => true,
            ],
        ], CAST_PRIMITIVES);

        $dto2 = $dto1->only(['sample'], MUTABLE);

        $this->assertSame(['name', 'sample'], $dto1->getPropertyNames());
        $this->assertSame(['sample'], $dto2->getPropertyNames());
        $this->assertSame(PARTIAL | CAST_PRIMITIVES, $dto1->getFlags());
        $this->assertSame(PARTIAL | CAST_PRIMITIVES | MUTABLE, $dto2->getFlags());
        $this->assertNotSame($dto1, $dto2);
    }

    /**
     * @test
     */
    public function keeps_only_some_properties_in_same_instance_if_mutable()
    {
        $dto1 = PartialDto::make([
            'name' => 'bar',
            'sample' => [
                'enabled' => true,
            ],
        ], MUTABLE);

        $dto2 = $dto1->only(['sample'], IGNORE_UNKNOWN_PROPERTIES);
        $this->assertSame(['sample'], $dto1->getPropertyNames());
        $this->assertSame(['sample'], $dto2->getPropertyNames());
        $this->assertSame(PARTIAL | MUTABLE | IGNORE_UNKNOWN_PROPERTIES, $dto1->getFlags());
        $this->assertSame(PARTIAL | MUTABLE | IGNORE_UNKNOWN_PROPERTIES, $dto2->getFlags());
        $this->assertSame($dto1, $dto2);
    }

    /**
     * @test
     */
    public function excludes_some_properties()
    {
        $dto1 = PartialDto::make([
            'name' => 'bar',
            'sample' => [
                'enabled' => true,
            ],
        ]);

        $dto2 = $dto1->except(['sample'], CAMEL_CASE_ARRAY);

        $this->assertSame(['name', 'sample'], $dto1->getPropertyNames());
        $this->assertSame(['name'], $dto2->getPropertyNames());
        $this->assertSame(PARTIAL, $dto1->getFlags());
        $this->assertSame(PARTIAL | CAMEL_CASE_ARRAY, $dto2->getFlags());
        $this->assertNotSame($dto1, $dto2);
    }

    /**
     * @test
     */
    public function excludes_some_properties_in_same_instance_if_mutable()
    {
        $dto1 = PartialDto::make([
            'name' => 'bar',
            'sample' => [
                'enabled' => true,
            ],
        ], MUTABLE);

        $dto2 = $dto1->except(['sample'], CAST_PRIMITIVES);
        $this->assertSame(['name'], $dto1->getPropertyNames());
        $this->assertSame(['name'], $dto2->getPropertyNames());
        $this->assertSame(PARTIAL | MUTABLE | CAST_PRIMITIVES, $dto1->getFlags());
        $this->assertSame(PARTIAL | MUTABLE | CAST_PRIMITIVES, $dto2->getFlags());
        $this->assertSame($dto1, $dto2);
    }

    /**
     * @test
     */
    public function can_temporarily_mutate_when_immutable()
    {
        $dto1 = PartialDto::make(['name' => 'foo']);

        $this->assertSame(0, $dto1->getFlags() & MUTABLE);
        $this->assertSame(['name' => 'foo'], $dto1->toArray());

        $dto2 = $dto1->mutate(function ($dto) {
            $dto->nullable = 123;
        });

        $this->assertSame($dto1, $dto2);
        $this->assertSame(0, $dto2->getFlags() & MUTABLE);
        $this->assertSame(['name' => 'foo', 'nullable' => 123], $dto1->toArray());
    }

    /**
     * @test
     */
    public function can_temporarily_mutate_when_mutable()
    {
        $dto1 = PartialDto::make(['name' => 'foo'], MUTABLE);

        $this->assertSame(MUTABLE, $dto1->getFlags() & MUTABLE);
        $this->assertSame(['name' => 'foo'], $dto1->toArray());

        $dto2 = $dto1->mutate(function ($dto) {
            $dto->nullable = 123;
        });

        $this->assertSame($dto1, $dto2);
        $this->assertSame(MUTABLE, $dto2->getFlags() & MUTABLE);
        $this->assertSame(['name' => 'foo', 'nullable' => 123], $dto1->toArray());
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
    public function turns_camel_case_properties_into_snake_case_keys_when_conving_into_array()
    {
        $data = [
            'isAdmin' => true,
            'partialDto' => [
                'name' => 'bar',
                'sample' => [
                    'enabled' => true,
                ],
            ],
        ];

        $expected = [
            'is_admin' => true,
            'partial_dto' => [
                'name' => 'bar',
                'sample' => [
                    'enabled' => true,
                ],
            ],
        ];

        $dto = CamelCaseDto::make($data);

        $this->assertSame($expected, $dto->toArray());
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
    public function fails_when_unsetting_a_property_of_not_partial_dto_as_an_array()
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
        $dto1 = PartialDto::make(['name' => 'foo'], CAMEL_CASE_ARRAY);
        $serialized = $dto1->serialize();

        [$data, $flags] = unserialize($serialized);

        $this->assertSame(['name' => 'foo'], $data);
        $this->assertSame(CAMEL_CASE_ARRAY | PARTIAL, $flags);

        $dto2 = PartialDto::make();
        $dto2->unserialize($serialized);

        $this->assertSame(['name' => 'foo'], $dto2->toArray());
        $this->assertSame(CAMEL_CASE_ARRAY | PARTIAL, $dto2->getFlags());
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

    /**
     * @test
     */
    public function can_set_default_values()
    {
        $expected = [
            'name' => 'foo',
            'count' => 0,
            'time' => null,
        ];

        $dto = DtoWithDefaults::make(['name' => 'foo']);

        $this->assertSame($expected, $dto->toArray());
    }

    /**
     * @test
     */
    public function only_property_names_and_values_are_shown_on_debug()
    {
        $dto = PartialDto::make(['name' => 'foo']);
        $id = spl_object_id($dto);
        $expected = <<<DMP
object(Cerbero\Dto\Dtos\PartialDto)#{$id} (1) {
  ["name"]=>
  string(3) "foo"
}

DMP;

        ob_start();

        var_dump($dto);

        $this->assertSame($expected, ob_get_clean());
    }
}
