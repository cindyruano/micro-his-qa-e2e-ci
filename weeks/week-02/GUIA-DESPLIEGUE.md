# Guía de reproducción local

## Requisitos

PHP 8.2+, Composer, extensiones `pdo_sqlite` y `pdo_pgsql` para la integración PostgreSQL, Node.js/npm y Git.

## Preparación

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

Para PostgreSQL, configure `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` y `QA_POSTGRES_TESTS=true` en `.env`. Nunca guarde credenciales en Git.

## Verificación

```powershell
php -v
php artisan --version
php artisan migrate:fresh --seed --force
php artisan test
php artisan qa:quality-gate --commit=$(git rev-parse HEAD) --environment=local
```

El gate exitoso debe retornar `0`. Para demostrar el bloqueo:

```powershell
php artisan qa:quality-gate --commit=$(git rev-parse HEAD) --environment=local --induce-failure
```

Este último comando debe retornar `1` y no debe usarse en CI normal. El workflow existente instala dependencias, inicia PostgreSQL efímero, ejecuta migraciones, pruebas y el gate con el SHA real del workflow.

## Diagnóstico

Si `php artisan test` falla por `APP_KEY`, cree `.env` desde `.env.example` y ejecute `php artisan key:generate`. Si la prueba PostgreSQL se omite, revise que el servicio esté accesible y que `QA_POSTGRES_TESTS=true` esté configurado.
