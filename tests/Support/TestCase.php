<?php

declare(strict_types=1);

namespace MicroHis\Tests\Support;

use Throwable;

/**
 * Micro-framework de pruebas en PHP vanilla (sin PHPUnit ni ninguna
 * dependencia externa, acorde al requisito "ningún framework").
 * Cada test es un método público que empieza con "test".
 */
abstract class TestCase
{
    private int $assertions = 0;

    public function run(): array
    {
        $results = [];
        foreach (get_class_methods($this) as $method) {
            if (!str_starts_with($method, 'test')) {
                continue;
            }
            $this->assertions = 0;
            try {
                $this->setUp();
                $this->$method();
                $results[] = ['test' => static::class . '::' . $method, 'status' => 'PASS', 'assertions' => $this->assertions];
            } catch (Throwable $e) {
                $results[] = [
                    'test' => static::class . '::' . $method,
                    'status' => 'FAIL',
                    'assertions' => $this->assertions,
                    'error' => $e->getMessage(),
                ];
            }
        }
        return $results;
    }

    protected function setUp(): void
    {
        // Hook opcional para subclases.
    }

    protected function assertTrue(bool $condition, string $message = 'Se esperaba true'): void
    {
        $this->assertions++;
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    protected function assertFalse(bool $condition, string $message = 'Se esperaba false'): void
    {
        $this->assertTrue(!$condition, $message);
    }

    protected function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertions++;
        if ($expected != $actual) {
            $msg = $message !== '' ? $message : sprintf(
                'Se esperaba %s pero se obtuvo %s',
                var_export($expected, true),
                var_export($actual, true)
            );
            throw new \RuntimeException($msg);
        }
    }

    protected function assertThrows(string $expectedExceptionClass, callable $callback, string $message = ''): void
    {
        $this->assertions++;
        try {
            $callback();
        } catch (Throwable $e) {
            if ($e instanceof $expectedExceptionClass) {
                return;
            }
            throw new \RuntimeException(
                ($message !== '' ? $message . ' ' : '') .
                "Se esperaba {$expectedExceptionClass} pero se lanzó " . get_class($e) . ': ' . $e->getMessage()
            );
        }
        throw new \RuntimeException(
            ($message !== '' ? $message . ' ' : '') . "Se esperaba que se lanzara {$expectedExceptionClass} pero no se lanzó ninguna excepción."
        );
    }
}
