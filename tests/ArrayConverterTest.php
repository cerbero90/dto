<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Manipulators\ArrayConverter;
use Cerbero\Dto\Manipulators\DateTimeConverter;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

/**
 * Tests for ArrayConverter.
 *
 */
class ArrayConverterTest extends TestCase
{
    /**
     * This method is called before each test.
     * 
     */
    protected function setUp(): void
    {
        // reset ArrayConverter instance
        $instances = new ReflectionProperty(ArrayConverter::class, 'instance');
        $instances->setAccessible(true);
        $instances->setValue(null, null);
        $instances->setAccessible(false);
    }

    /**
     * @test
     */
    public function is_singleton()
    {
        $converter1 = ArrayConverter::instance();
        $converter2 = ArrayConverter::instance();

        $this->assertSame($converter1, $converter2);
    }

    /**
     * @test
     */
    public function sets_and_gets_conversions()
    {
        $converter = ArrayConverter::instance();

        $this->assertEmpty($converter->getConversions());

        $conversions = ['foo' => 'bar'];

        $converter->setConversions($conversions);

        $this->assertSame($conversions, $converter->getConversions());
    }

    /**
     * @test
     */
    public function adds_and_removes_conversions()
    {
        $converter = ArrayConverter::instance();

        $this->assertEmpty($converter->getConversions());

        $converter = $converter->addConversion('foo', 'bar');

        $this->assertSame(['foo' => 'bar'], $converter->getConversions());
        $this->assertInstanceOf(ArrayConverter::class, $converter);

        $converter = $converter->removeConversion('foo');

        $this->assertEmpty($converter->getConversions());
        $this->assertInstanceOf(ArrayConverter::class, $converter);
    }

    /**
     * @test
     */
    public function calls_registered_conversions()
    {
        $data = [
            'key1' => 'value1',
            'key2' => [
                'key3' => new DateTime('2020-01-01'),
            ],
        ];

        $expected = [
            'key1' => 'value1',
            'key2' => [
                'key3' => '2020-01-01',
            ],
        ];

        $converter = ArrayConverter::instance()->setConversions([
            'DateTime' => DateTimeConverter::class,
        ]);

        $this->assertSame($expected, $converter->convert($data, true));
    }

    /**
     * @test
     */
    public function gets_converter_by_instance()
    {
        $converter = ArrayConverter::instance()->setConversions([
            'DateTime' => DateTimeConverter::class,
        ]);

        $dateTimeConverter = $converter->getConverterByInstance(new DateTime());

        $this->assertInstanceOf(DateTimeConverter::class, $dateTimeConverter);
        $this->assertSame($dateTimeConverter, $converter->getConverterByInstance(new DateTime()));

        $this->assertNull($converter->getConverterByInstance(new stdClass()));
    }

    /**
     * @test
     */
    public function gets_converter_by_class()
    {
        $converter = ArrayConverter::instance()->setConversions([
            'DateTime' => DateTimeConverter::class,
        ]);

        $dateTimeConverter = $converter->getConverterByClass(DateTime::class);

        $this->assertInstanceOf(DateTimeConverter::class, $dateTimeConverter);
        $this->assertSame($dateTimeConverter, $converter->getConverterByClass(DateTime::class));

        $this->assertNull($converter->getConverterByClass(stdClass::class));
    }

    /**
     * @test
     */
    public function convert_items_with_camel_case_keys_into_array_preserving_the_case()
    {
        $data = [
            'keyOne' => 'value1',
            'keyTwo' => [
                'keyThree' => '2020-01-01',
            ],
        ];

        $expected = [
            'keyOne' => 'value1',
            'keyTwo' => [
                'keyThree' => '2020-01-01',
            ],
        ];

        $this->assertSame($expected, ArrayConverter::instance()->convert($data, false));
    }

    /**
     * @test
     */
    public function convert_items_with_camel_case_keys_into_array_with_snake_case_keys()
    {
        $data = [
            'keyOne' => 'value1',
            'keyTwo' => [
                'keyThree' => '2020-01-01',
            ],
        ];

        $expected = [
            'key_one' => 'value1',
            'key_two' => [
                'key_three' => '2020-01-01',
            ],
        ];

        $this->assertSame($expected, ArrayConverter::instance()->convert($data, true));
    }
}
