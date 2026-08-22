# Guía de Despliegue Final — Micro-HIS QA/E2E/CI

## 1. Requisitos previos

- PHP 8.2 o superior con extensiones `pdo_sqlite` y `sqlite3` habilitadas
  (o `pdo_mysql`/`pdo_pgsql` si se cambia el driver en producción).
- Acceso de escritura a la carpeta `storage/` (para la base SQLite y evidencias).
- Servidor web con soporte de front controller (Apache/Nginx) o el servidor
  embebido de PHP para ambientes de prueba.

## 2. Configuración (fuera del código)

1. Copiar `.env.example` a `.env`.
2. Ajustar `DB_DRIVER` y `DB_SQLITE_PATH` (o las variables equivalentes si se
   usa otro motor). **Nunca** commitear el archivo `.env` real (ver `.gitignore`).
3. Verificar permisos de escritura sobre la ruta configurada en `DB_SQLITE_PATH`.

## 3. Quality gate como criterio de despliegue

El pipeline de CI (`.github/workflows/ci.yml`) ejecuta la suite de pruebas
automatizadas en cada cambio. Adicionalmente, antes de promover una versión a
un ambiente superior (STAGING → PRODUCTION), debe ejecutarse:

```bash
php bin/console.php gate:evaluate <ID_DEL_PLAN>
```

- Código de salida `0` → el quality gate fue **aprobado**: la tasa de
  ejecuciones `PASSED` alcanza o supera el umbral definido en el plan.
- Código de salida `1` → el quality gate fue **rechazado**: no se debe
  continuar con el despliegue hasta corregir las fallas.

Esta verificación debe incorporarse como un paso obligatorio (gate) en el
pipeline de despliegue, de forma que un pipeline no pueda promover una
versión a producción si el gate no fue aprobado.

## 4. Pasos de despliegue sugeridos

1. **Build:** clonar la rama/etiqueta evaluada del repositorio.
2. **Config:** aplicar `.env` del ambiente destino (nunca reutilizar el de
   desarrollo).
3. **Migración de esquema:** al iniciar, `PdoConnectionFactory::create()`
   aplica `src/Persistence/Schema/schema.sql` si la base aún no existe. Para
   bases existentes, aplicar el esquema manualmente de forma controlada.
4. **Pruebas:** ejecutar `php tests/run_tests.php`; el pipeline debe detenerse
   si el código de salida no es `0`.
5. **Quality gate:** ejecutar `php bin/console.php gate:evaluate <planId>`
   para la versión candidata; el pipeline debe detenerse si es rechazado.
6. **Despliegue:** publicar `public/` como raíz web (front controller) y/o
   habilitar `bin/console.php` como herramienta operativa de QA en el
   servidor destino.
7. **Rollback:** conservar la etiqueta/commit anterior desplegada; en caso de
   fallas post-despliegue, redesplegar dicha referencia.

## 5. Conservación de evidencia

Las evidencias adjuntadas (`evidence:attach`) deben apuntar a rutas o URLs
persistentes (por ejemplo, un bucket de almacenamiento o un directorio
respaldado), no a rutas temporales que se limpien entre ejecuciones del
pipeline, para mantener la trazabilidad exigida en la auditoría de QA.
