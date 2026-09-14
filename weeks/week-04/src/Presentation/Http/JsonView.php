<?php

declare(strict_types=1);

namespace MicroHisWeek04\Presentation\Http;

use MicroHisWeek04\Domain\QualityGateRun;

final class JsonView
{
    public function render(?QualityGateRun $run, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'data' => $run?->toArray(),
            'status_code' => $statusCode,
        ], JSON_THROW_ON_ERROR);
    }
}
