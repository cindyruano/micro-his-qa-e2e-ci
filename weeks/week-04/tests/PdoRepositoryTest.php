<?php

declare(strict_types=1);

use MicroHisWeek04\Domain\QualityGateRun;
use MicroHisWeek04\Persistence\PdoQualityGateRepository;
return [
    'pdo repository persists and rehydrates a run' => static function (): void {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
        $repository = new PdoQualityGateRepository($pdo);
        $run = new QualityGateRun('run-1', 'abc123', 'test', ['login' => true], 'passed');
        $repository->save($run);
        $found = $repository->latest();

        assertSameValue('run-1', $found?->id);
        assertSameValue(['login' => true], $found?->controls);
        assertSameValue(1, (int) $pdo->query('SELECT COUNT(*) FROM qa_runs')->fetchColumn());
    },
];
