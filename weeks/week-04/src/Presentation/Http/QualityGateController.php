<?php

declare(strict_types=1);

namespace MicroHisWeek04\Presentation\Http;

use MicroHisWeek04\Application\GetLatestQualityGate;
use MicroHisWeek04\Application\RunQualityGate;
use MicroHisWeek04\Domain\TestPlan;
use RuntimeException;

final class QualityGateController
{
    public function __construct(
        private RunQualityGate $runQualityGate,
        private GetLatestQualityGate $getLatest,
        private JsonView $view,
    ) {
    }

    public function store(QualityGateRequest $request): void
    {
        $runnerRequest = $request;
        try {
            $run = $this->runQualityGate->execute(
                $runnerRequest->commit,
                $runnerRequest->environment,
                new TestPlan($runnerRequest->plan, $runnerRequest->scenarios),
            );
            $this->view->render($run, 201);
        } catch (RuntimeException) {
            $this->view->render($this->getLatest->execute(), 422);
        }
    }

    public function show(): void
    {
        $this->view->render($this->getLatest->execute());
    }
}
