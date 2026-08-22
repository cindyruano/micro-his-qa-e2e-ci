<?php

declare(strict_types=1);

/**
 * Configuración de conexión, externa al código fuente (12-factor).
 * Se lee de variables de entorno; si no existen, se usan valores de
 * desarrollo por defecto (SQLite en archivo, sin credenciales sensibles).
 */
return [
    'driver' => env('DB_DRIVER', 'sqlite'),
    'sqlite_path' => env('DB_SQLITE_PATH', __DIR__ . '/../storage/database.sqlite'),
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
