<?php

declare(strict_types=1);

/**
 * Bootstrap mínimo del micro-monolito.
 * No se usa Composer ni ningún framework: el autoload es un mapeo
 * PSR-4 manual (namespace -> carpeta) resuelto con spl_autoload_register.
 */

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'MicroHis\\Domain\\'       => __DIR__ . '/src/Domain/',
        'MicroHis\\Application\\'  => __DIR__ . '/src/Application/',
        'MicroHis\\Persistence\\'  => __DIR__ . '/src/Persistence/',
        'MicroHis\\Presentation\\' => __DIR__ . '/src/Presentation/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) === 0) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
                return;
            }
        }
    }
});

// Configuración fuera del código: variables de entorno con valores por defecto para desarrollo/pruebas.
function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

// Carga opcional de un archivo .env simple (formato KEY=VALUE), sin dependencias externas.
$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        if (getenv($k) === false) {
            putenv("{$k}={$v}");
        }
    }
}
