<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Dtos\CamelCaseDto;
use Cerbero\Dto\Dtos\NoDocCommentDto;
use Cerbero\Dto\Dtos\NoPropertiesDto;
use Cerbero\Dto\Dtos\PartialDto;
use Cerbero\Dto\Dtos\SampleDto;
use Cerbero\Dto\Dtos\SampleDtoWithParent;
use Cerbero\Dto\Exceptions\DtoNotFoundException;
use Cerbero\Dto\Exceptions\InvalidDocCommentException;
use Cerbero\Dto\Exceptions\MissingValueException;
use Cerbero\Dto\Exceptions\UnknownDtoPropertyException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

/**
 * Tests for DtoPropertiesMapper.
 *
 */
class DtoPropertiesMapperTest extends TestCase
{
    /**
     * This method is called before each test.
     *
     */
    protected function setUp(): void
    {
        // reset DtoPropertiesMapper instances
        $instances = new ReflectionProperty(DtoPropertiesMapper::class, 'instances');
        $instances->setAccessible(true);
        $instances->setValue(null, []);
        $instances->setAccessible(false);
    }

    /**
     * @test
     */
    public function fails_if_dto_is_missing()
    {
        $this->expectException(DtoNotFoundException::class);
        $this->expectExceptionMessage('Unable to find the DTO [MissingDto]');

        DtoPropertiesMapper::for('MissingDto');
    }

    /**
     * @test
     */
    public function is_singleton()
    {
        $instance1 = DtoPropertiesMapper::for(SampleDto::class);
        $instance2 = DtoPropertiesMapper::for(SampleDto::class);

        $this->assertSame($instance1, $instance2);
    }

    /**
     * @test
     */
    public function fails_if_doc_comment_is_missing()
    {
        $this->expectException(InvalidDocCommentException::class);
        $this->expectExceptionMessage('The DTO [Cerbero\Dto\Dtos\NoDocCommentDto] does not have declared properties');

        DtoPropertiesMapper::for(NoDocCommentDto::class)->map([], NONE);
    }

    /**
     * @test
     */
    public function quits_returning_empty_array_if_properties_in_doc_comment_are_missing()
    {
        $mappedProperties = DtoPropertiesMapper::for(NoPropertiesDto::class)->map([], NONE);

        $this->assertEmpty($mappedProperties);
    }

    /**
     * @test
     */
    public function does_not_fail_on_missing_properties_if_dto_is_partial()
    {
        $mappedProperties = DtoPropertiesMapper::for(SampleDto::class)->map([], PARTIAL);

        $this->assertEmpty($mappedProperties);
    }

    /**
     * @test
     */
    public function fails_on_missing_properties_if_dto_is_not_partial()
    {
        $error = "The DTO [Cerbero\Dto\Dtos\SampleDto] does not accept partial data but 'object' is missing";

        $this->expectException(MissingValueException::class);
        $this->expectExceptionMessage($error);

        DtoPropertiesMapper::for(SampleDto::class)->map([], NONE);
    }

    /**
     * @test
     */
    public function fails_if_extra_data_is_not_ignored()
    {
        $this->expectException(UnknownDtoPropertyException::class);
        $this->expectExceptionMessage("Unknown property 'extra' in the DTO [Cerbero\Dto\Dtos\SampleDto]");

        $data = [
            'object' => new stdClass,
            'dtos' => [new NoPropertiesDto],
            'sample' => new SampleClass,
            'partial' => new PartialDto,
            'name' => 'foo',
            'enabled' => true,
            'extra' => 123,
        ];

        DtoPropertiesMapper::for(SampleDto::class)->map($data, NONE);
    }

    /**
     * @test
     */
    public function gets_property_names()
    {
        $names = DtoPropertiesMapper::for(SampleDto::class)->getNames();
        $expected = [
            'object',
            'dtos',
            'sample',
            'partial',
            'name',
            'enabled',
        ];

        $this->assertSame($expected, $names);
    }

    /**
     * @test
     */
    public function maps_properties()
    {
        $data = [
            'object' => new stdClass,
            'dtos' => [new NoPropertiesDto],
            'sample' => new SampleClass,
            'partial' => new PartialDto,
            'name' => 'foo',
            'enabled' => true,
        ];

        $names = array_keys($data);
        $map = DtoPropertiesMapper::for(SampleDto::class)->map($data, NONE);

        $this->assertCount(6, $map);

        foreach ($map as $name => $propery) {
            $this->assertContains($name, $names);
            $this->assertInstanceOf(DtoProperty::class, $propery);
            $this->assertSame($name, $propery->getName());
            $this->assertSame($data[$name], $propery->getRawValue());
            $this->assertSame(NONE, $propery->getFlags());
        }

        $types = $map['object']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(2, $types->all);
        $this->assertSame('stdClass', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());
        $this->assertSame('null', $types->all[1]->name());
        $this->assertFalse($types->all[1]->isCollection());

        $types = $map['dtos']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('Cerbero\Dto\Dtos\NoPropertiesDto', $types->all[0]->name());
        $this->assertTrue($types->all[0]->isCollection());

        $types = $map['sample']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('Cerbero\Dto\SampleClass', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());

        $types = $map['name']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('string', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());

        $types = $map['enabled']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('bool', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());
    }

    /**
     * @test
     */
    public function maps_with_parent_properties()
    {
        $data = [
            'name' => 'foo',
            'enabled' => true,
        ];

        $names = array_keys($data);
        $map = DtoPropertiesMapper::for(SampleDtoWithParent::class)->map($data, NONE);

        $this->assertCount(2, $map);

        foreach ($map as $name => $propery) {
            $this->assertContains($name, $names);
            $this->assertInstanceOf(DtoProperty::class, $propery);
            $this->assertSame($name, $propery->getName());
            $this->assertSame($data[$name], $propery->getRawValue());
            $this->assertSame(NONE, $propery->getFlags());
        }

        $types = $map['name']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('string', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());

        $types = $map['enabled']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('bool', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());
    }

    /**
     * @test
     */
    public function keep_map_but_update_values_and_flags_on_dto_remap()
    {
        $data = [
            'object' => new stdClass,
            'dtos' => [new NoPropertiesDto],
            'sample' => new SampleClass,
            'partial' => new PartialDto,
            'name' => 'foo',
            'enabled' => true,
        ];

        DtoPropertiesMapper::for(SampleDto::class)->map($data, NONE);

        $data = [
            'object' => null,
            'dtos' => [],
            'sample' => new SampleClass,
            'partial' => new PartialDto,
            'name' => 'foo',
            'enabled' => false,
        ];

        $map = DtoPropertiesMapper::for(SampleDto::class)->map($data, IGNORE_UNKNOWN_PROPERTIES);

        $this->assertCount(6, $map);

        foreach ($map as $name => $propery) {
            $this->assertInstanceOf(DtoProperty::class, $propery);
            $this->assertSame($name, $propery->getName());
            $this->assertSame(IGNORE_UNKNOWN_PROPERTIES, $propery->getFlags());
        }

        $types = $map['object']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(2, $types->all);
        $this->assertSame('stdClass', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());
        $this->assertSame('null', $types->all[1]->name());
        $this->assertFalse($types->all[1]->isCollection());
        $this->assertNull($map['object']->getRawValue());

        $types = $map['dtos']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('Cerbero\Dto\Dtos\NoPropertiesDto', $types->all[0]->name());
        $this->assertTrue($types->all[0]->isCollection());
        $this->assertEmpty($map['dtos']->getRawValue());

        $types = $map['sample']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('Cerbero\Dto\SampleClass', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());

        $types = $map['name']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('string', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());

        $types = $map['enabled']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('bool', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());
        $this->assertFalse($map['enabled']->getRawValue());
    }

    /**
     * @test
     */
    public function maps_snake_case_properties_into_camel_case_properties()
    {
        $data = [
            'is_admin' => true,
            'partial_dto' => ['name' => 'foo'],
        ];

        $namesMap = [
            'isAdmin' => 'is_admin',
            'partialDto' => 'partial_dto',
        ];

        $names = array_keys($namesMap);
        $map = DtoPropertiesMapper::for(CamelCaseDto::class)->map($data, NONE);

        $this->assertCount(2, $map);

        foreach ($map as $name => $propery) {
            $this->assertContains($name, $names);
            $this->assertInstanceOf(DtoProperty::class, $propery);
            $this->assertSame($name, $propery->getName());
            $this->assertSame($data[$namesMap[$name]], $propery->getRawValue());
            $this->assertSame(NONE, $propery->getFlags());
        }

        $types = $map['isAdmin']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('bool', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());

        $types = $map['partialDto']->getTypes();
        $this->assertInstanceOf(DtoPropertyTypes::class, $types);
        $this->assertCount(1, $types->all);
        $this->assertSame('Cerbero\Dto\Dtos\PartialDto', $types->all[0]->name());
        $this->assertFalse($types->all[0]->isCollection());
    }
}
