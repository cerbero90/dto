<?php

namespace Cerbero\Dto;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;

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

        $this->assertNull($converter->getConversion(new SampleClass));

        $conversion = function () {
            return ['test' => true];
        };

        $converter->conversion(SampleClass::class, $conversion);

        $this->assertSame($conversion, $converter->getConversion(new SampleClass));
    }

    /**
     * @test
     */
    public function calls_registered_conversions()
    {
        $converter = ArrayConverter::instance();

        $converter->conversion(SampleClass::class, function (SampleClass $class) {
            return get_class($class);
        });

        $this->assertSame(SampleClass::class, $converter->convert(new SampleClass));
    }

    /**
     * @test
     */
    public function recursively_converts_iterables()
    {
        $data = [
            'key1' => 'value1',
            'key2' => [
                'key3' => new SampleClass,
            ]
        ];

        $expected = [
            'key1' => 'value1',
            'key2' => [
                'key3' => SampleClass::class,
            ]
        ];

        $converter = ArrayConverter::instance();

        $converter->conversion(SampleClass::class, function (SampleClass $class) {
            return get_class($class);
        });

        $this->assertSame($expected, $converter->convert($data));
    }
}
