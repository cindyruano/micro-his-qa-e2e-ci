<?php

declare(strict_types=1);

function assertTrue(bool $condition, string $message = 'Expected true'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assertSameValue(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message ?: sprintf('Expected %s, got %s', var_export($expected, true), var_export($actual, true)));
    }
}

function assertThrows(callable $callback, string $exceptionClass): void
{
    try {
        $callback();
    } catch (Throwable $throwable) {
        assertSameValue($exceptionClass, $throwable::class);
        return;
    }

    throw new RuntimeException("Expected {$exceptionClass}.");
}
