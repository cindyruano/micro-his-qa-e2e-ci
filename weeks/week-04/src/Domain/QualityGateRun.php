<?php

declare(strict_types=1);

namespace MicroHisWeek04\Domain;

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
        if ($id === '' || $commit === '' || $environment === '' || $controls === []) {
            throw new InvalidArgumentException('A run requires id, commit, environment and controls.');
        }
        if (!in_array($status, ['passed', 'failed'], true)) {
            throw new InvalidArgumentException('Invalid quality gate status.');
        }
    }

    public function passes(): bool
    {
        return $this->status === 'passed' && !in_array(false, $this->controls, true);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'commit' => $this->commit,
            'environment' => $this->environment,
            'controls' => $this->controls,
            'status' => $this->status,
        ];
    }
}
