<?php

declare(strict_types=1);

namespace MicroHisWeek04\Domain;

use InvalidArgumentException;

final class TestPlan
{
    /** @param list<string> $scenarios */
    public function __construct(
        public readonly string $name,
        public readonly array $scenarios,
    ) {
        if ($name === '' || $scenarios === []) {
            throw new InvalidArgumentException('A plan needs a name and scenarios.');
        }
    }
}
