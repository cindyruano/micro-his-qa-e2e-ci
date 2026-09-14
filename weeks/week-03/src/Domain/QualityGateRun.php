<?php

declare(strict_types=1);

namespace MicroHis\Domain;

use InvalidArgumentException;

final class QualityGateRun
{
    /** @param array<string, bool> $controls */
    public function __construct(
        public readonly string $id,
        public readonly string $commit,
        public readonly string $environment,
        public readonly array $controls,
        public readonly string $status,
    ) {
        if ($commit === '' || $environment === '' || $controls === []) {
            throw new InvalidArgumentException('A run requires commit, environment and controls.');
        }

        if (!in_array($status, ['passed', 'failed'], true)) {
            throw new InvalidArgumentException('Invalid quality gate status.');
        }
    }

    public function passes(): bool
    {
        return $this->status === 'passed' && !in_array(false, $this->controls, true);
    }
}
