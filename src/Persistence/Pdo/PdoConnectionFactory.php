<?php

declare(strict_types=1);

namespace MicroHis\Persistence\Pdo;

use PDO;

/**
 * Crea y prepara la conexión PDO a partir de la configuración externa
 * (config/database.php). Aplica el esquema si la base aún no existe.
 */
final class PdoConnectionFactory
{
    public static function create(array $config): PDO
    {
        $sqlitePath = $config['sqlite_path'];
        $isNew = !file_exists($sqlitePath) || filesize($sqlitePath) === 0;

        $pdo = new PDO('sqlite:' . $sqlitePath, null, null, $config['options']);
        $pdo->exec('PRAGMA foreign_keys = ON;');

        if ($isNew) {
            $schema = file_get_contents(__DIR__ . '/../Schema/schema.sql');
            $pdo->exec($schema);
        }

        return $pdo;
    }

    /** Conexión SQLite en memoria, útil para pruebas rápidas y aisladas. */
    public static function createInMemory(): PDO
    {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $schema = file_get_contents(__DIR__ . '/../Schema/schema.sql');
        $pdo->exec($schema);

        return $pdo;
    }
}
