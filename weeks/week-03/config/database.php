<?php

declare(strict_types=1);

return [
    'path' => getenv('MICRO_HIS_DB') ?: __DIR__ . '/../storage/micro-his.sqlite',
];
