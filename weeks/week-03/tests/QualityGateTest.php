<?php

declare(strict_types=1);

use MicroHis\Application\RunQualityGate;
use MicroHis\Domain\QualityGateRun;
use MicroHis\Domain\TestPlan;
use MicroHis\Persistence\DeterministicE2ERunner;
use MicroHis\Persistence\InMemoryEvidenceRepository;
use MicroHis\Persistence\InMemoryQualityGateRunRepository;

return [
    'happy path plans, executes and preserves evidence' => static function (): void {
        $runs = new InMemoryQualityGateRunRepository();
        $evidence = new InMemoryEvidenceRepository();
        $run = (new RunQualityGate($runs, $evidence, new DeterministicE2ERunner()))
            ->execute('abc123', 'test', new TestPlan('smoke', ['login', 'evidence']));

        assertTrue($run->passes());
        assertSameValue('passed', $run->status);
        assertSameValue(1, count($runs->runs));
        assertSameValue($run->id, array_key_first($evidence->evidence));
    },
    'domain rejects an empty test plan' => static function (): void {
        assertThrows(static fn () => new TestPlan('', []), InvalidArgumentException::class);
        assertThrows(static fn () => new QualityGateRun('id', '', 'test', ['login' => true], 'passed'), InvalidArgumentException::class);
    },
    'persistence error is propagated and does not report success' => static function (): void {
        $failingRuns = new class implements \MicroHis\Domain\Contracts\QualityGateRunRepository {
            public function save(QualityGateRun $run): void
            {
                throw new RuntimeException('database unavailable');
            }
        };
        $evidence = new InMemoryEvidenceRepository();

        assertThrows(
            fn () => (new RunQualityGate($failingRuns, $evidence, new DeterministicE2ERunner()))
                ->execute('abc123', 'test', new TestPlan('smoke', ['login'])),
            RuntimeException::class,
        );
        assertSameValue([], $evidence->evidence);
    },
];
