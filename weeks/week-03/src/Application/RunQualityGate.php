<?php

declare(strict_types=1);

namespace MicroHis\Application;

use MicroHis\Domain\Contracts\E2ERunner;
use MicroHis\Domain\Contracts\EvidenceRepository;
use MicroHis\Domain\Contracts\QualityGateRunRepository;
use MicroHis\Domain\QualityGateRun;
use MicroHis\Domain\TestPlan;
use RuntimeException;

final class RunQualityGate
{
    public function __construct(
        private QualityGateRunRepository $runs,
        private EvidenceRepository $evidence,
        private E2ERunner $e2e,
    ) {
    }

    public function execute(string $commit, string $environment, TestPlan $plan): QualityGateRun
    {
        $controls = $this->e2e->run($plan);
        $run = new QualityGateRun(
            id: bin2hex(random_bytes(8)),
            commit: $commit,
            environment: $environment,
            controls: $controls,
            status: in_array(false, $controls, true) ? 'failed' : 'passed',
        );

        $this->runs->save($run);
        $this->evidence->save($run->id, [
            'plan' => $plan->name,
            'scenarios' => $plan->scenarios,
            'controls' => $controls,
            'status' => $run->status,
        ]);

        if (!$run->passes()) {
            throw new RuntimeException('QUALITY GATE FAILED');
        }

        return $run;
    }
}
