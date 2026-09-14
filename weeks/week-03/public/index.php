<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'service' => 'Micro-HIS QA',
    'status' => 'available',
    'flow' => ['planning', 'e2e', 'evidence', 'quality_gate'],
], JSON_THROW_ON_ERROR);
