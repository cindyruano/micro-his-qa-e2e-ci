<?php

declare(strict_types=1);

namespace MicroHisWeek04\Application;

use MicroHisWeek04\Domain\Contracts\E2ERunner;
use MicroHisWeek04\Domain\Contracts\EvidenceStore;
use MicroHisWeek04\Domain\Contracts\QualityGateRepository;
use MicroHisWeek04\Domain\QualityGateRun;
use MicroHisWeek04\Domain\TestPlan;
use RuntimeException;

final class RunQualityGate
{
    public function __construct(
        private QualityGateRepository $repository,
        private EvidenceStore $evidence,
        private E2ERunner $runner,
    ) {
    }

    public function execute(string $commit, string $environment, TestPlan $plan): QualityGateRun
    {
        $controls = $this->runner->run($plan);
        $run = new QualityGateRun(
            bin2hex(random_bytes(8)),
            $commit,
            $environment,
            $controls,
            in_array(false, $controls, true) ? 'failed' : 'passed',
        );

        $this->repository->save($run);
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
