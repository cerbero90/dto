<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Dtos\PartialDto;
use Cerbero\Dto\Manipulators\Listener;
use Cerbero\Dto\Manipulators\DateTimeConverter;
use Cerbero\Dto\Manipulators\PartialDtoListener;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

/**
 * Tests for Listener.
 *
 */
class ListenerTest extends TestCase
{
    /**
     * This method is called before each test.
     * 
     */
    protected function setUp(): void
    {
        // reset Listener instance
        $instances = new ReflectionProperty(Listener::class, 'instance');
        $instances->setAccessible(true);
        $instances->setValue(null, null);
        $instances->setAccessible(false);
    }

    /**
     * @test
     */
    public function is_singleton()
    {
        $converter1 = Listener::instance();
        $converter2 = Listener::instance();

        $this->assertSame($converter1, $converter2);
    }

    /**
     * @test
     */
    public function calls_listener_when_setting_values()
    {
        $listener = Listener::instance()->listen([
            PartialDto::class => PartialDtoListener::class,
        ]);

        $value = $listener->setting(PartialDto::class, 'nullable', null);

        $this->assertSame(123, $value);
    }

    /**
     * @test
     */
    public function calls_listener_when_getting_values()
    {
        $listener = Listener::instance()->listen([
            PartialDto::class => PartialDtoListener::class,
        ]);

        $value = $listener->getting(PartialDto::class, 'nullable', null);

        $this->assertSame(321, $value);
    }

    /**
     * @test
     */
    public function returns_original_value_if_listener_method_does_not_exist()
    {
        $listener = Listener::instance()->listen([
            PartialDto::class => PartialDtoListener::class,
        ]);

        $value = $listener->getting(PartialDto::class, 'name', 'foo');

        $this->assertSame('foo', $value);
    }
}
