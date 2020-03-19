<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Exceptions\IncompatibleDtoFlagsException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DtoFlagsHandler.
 *
 */
class DtoFlagsHandlerTest extends TestCase
{
    /**
     * The DTO flags handler.
     *
     * @var DtoFlagsHandler
     */
    private $handler;

    /**
     * This method is called before each test.
     * 
     */
    protected function setUp(): void
    {
        $this->handler = new DtoFlagsHandler;
    }

    /**
     * @test
     */
    public function validates_flags()
    {
        $this->expectException(IncompatibleDtoFlagsException::class);
        $this->expectExceptionMessage("The flags 'NULLABLE', 'NOT_NULLABLE' are incompatible");

        $this->handler->validateFlags(NULLABLE | NOT_NULLABLE);
    }

    /**
     * @test
     */
    public function overrides_flags()
    {
        $result = $this->handler->overrideFlags(MUTABLE | NULLABLE, BOOL_DEFAULT_TO_FALSE | NOT_NULLABLE);
        $this->assertSame(MUTABLE | BOOL_DEFAULT_TO_FALSE | NOT_NULLABLE, $result);

        $result = $this->handler->overrideFlags(IGNORE_UNKNOWN_PROPERTIES | NOT_NULLABLE, PARTIAL | NULLABLE);
        $this->assertSame(IGNORE_UNKNOWN_PROPERTIES | PARTIAL | NULLABLE, $result);
    }

    /**
     * @test
     */
    public function merges_flags()
    {
        $result = $this->handler->merge(NULLABLE_DEFAULT_TO_NULL | NULLABLE, NOT_NULLABLE | NONE);
        $this->assertSame(NULLABLE_DEFAULT_TO_NULL | NOT_NULLABLE | NONE, $result);

        $this->expectException(IncompatibleDtoFlagsException::class);
        $this->expectExceptionMessage("The flags 'NULLABLE', 'NOT_NULLABLE' are incompatible");
        $this->handler->merge(PARTIAL | MUTABLE, NULLABLE | NOT_NULLABLE);
    }
}
