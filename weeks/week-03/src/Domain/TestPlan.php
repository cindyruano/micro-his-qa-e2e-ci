<?php

declare(strict_types=1);

namespace MicroHis\Domain;

use InvalidArgumentException;

final class TestPlan
{
    /** @param list<string> $scenarios */
    public function __construct(
        public readonly string $name,
        public readonly array $scenarios,
    ) {
        if ($name === '' || $scenarios === []) {
            throw new InvalidArgumentException('A test plan needs a name and at least one scenario.');
        }
    }
}
