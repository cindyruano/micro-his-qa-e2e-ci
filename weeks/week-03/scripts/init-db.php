<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$config = require __DIR__ . '/../config/database.php';
$directory = dirname($config['path']);
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

$pdo = new PDO('sqlite:' . $config['path']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
echo "Database initialized: {$config['path']}\n";
