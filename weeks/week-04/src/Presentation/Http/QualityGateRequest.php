<?php

declare(strict_types=1);

namespace MicroHisWeek04\Presentation\Http;

final class QualityGateRequest
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly string $commit,
        public readonly string $environment,
        public readonly string $plan,
        public readonly array $scenarios,
        public readonly bool $induceFailure,
    ) {
    }

    /** @param array<string, mixed> $input */
    public static function fromArray(array $input): self
    {
        return new self(
            (string) ($input['commit'] ?? 'local'),
            (string) ($input['environment'] ?? 'local'),
            (string) ($input['plan'] ?? 'micro-his-qa'),
            array_values(array_map('strval', $input['scenarios'] ?? ['e2e_login', 'evidence_saved'])),
            (bool) ($input['induce_failure'] ?? false),
        );
    }
}
