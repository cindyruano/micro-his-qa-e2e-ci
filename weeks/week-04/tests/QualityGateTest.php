<?php

declare(strict_types=1);

use MicroHisWeek04\Application\GetLatestQualityGate;
use MicroHisWeek04\Application\RunQualityGate;
use MicroHisWeek04\Domain\QualityGateRun;
use MicroHisWeek04\Domain\TestPlan;
use MicroHisWeek04\Persistence\DeterministicE2ERunner;
use MicroHisWeek04\Persistence\InMemoryEvidenceStore;
use MicroHisWeek04\Persistence\InMemoryQualityGateRepository;

return [
    'same use case works with in memory repository' => static function (): void {
        $repository = new InMemoryQualityGateRepository();
        $evidence = new InMemoryEvidenceStore();
        $run = (new RunQualityGate($repository, $evidence, new DeterministicE2ERunner()))
            ->execute('abc123', 'test', new TestPlan('smoke', ['login', 'evidence']));

        assertTrue($run->passes());
        assertSameValue($run, (new GetLatestQualityGate($repository))->execute());
        assertSameValue($run->id, array_key_first($evidence->items));
    },
    'domain rule rejects an empty plan' => static function (): void {
        assertThrows(static fn () => new TestPlan('', []), InvalidArgumentException::class);
        assertThrows(static fn () => new QualityGateRun('', 'sha', 'test', ['login' => true], 'passed'), InvalidArgumentException::class);
    },
    'failed gate is stored and reported as failure' => static function (): void {
        $repository = new InMemoryQualityGateRepository();
        $useCase = new RunQualityGate($repository, new InMemoryEvidenceStore(), new DeterministicE2ERunner(true));

        assertThrows(
            fn () => $useCase->execute('abc123', 'test', new TestPlan('smoke', ['login'])),
            RuntimeException::class,
        );
        assertSameValue('failed', $repository->latest()?->status);
    },
];
