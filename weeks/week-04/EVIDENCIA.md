# Evidencia - Semana 4

## Entrega

- Arquitectura en capas con MVC en Presentation.
- Interfaz `QualityGateRepository` en el límite Domain/Application.
- Adaptadores `PdoQualityGateRepository` e `InMemoryQualityGateRepository`.
- El controlador no contiene SQL ni reglas del quality gate.
- Análisis y diagrama de integración con repositorio de datos compartido.

## Validación

| Comando                                        | Resultado esperado              |
|------------------------------------------------|---------------------------------|
| `php scripts/run-tests.php`                    | 4 pruebas, 0 fallos             |
| `php scripts/init-db.php`                      | SQLite inicializada             |
| `php bin/qa.php abc123 local`                  | `QUALITY GATE PASSED`, código 0 |
| `php bin/qa.php abc123 local --induce-failure` | `QUALITY GATE FAILED`, código 1 |
| `php -l` sobre los PHP de week-04              | Sin errores de sintaxis         |

## Trazabilidad

| Criterio                | Evidencia                                                                                     |
|-------------------------|-----------------------------------------------------------------------------------------------|
| Repositorio sustituible | `tests/QualityGateTest.php` ejecuta el caso de uso con InMemory.                              |
| PDO funcional           | `tests/PdoRepositoryTest.php` persiste y rehidrata una ejecución en SQLite.                   |
| MVC                     | `src/Presentation/Http/QualityGateController.php` delega en Application y usa `JsonView`.     |
| Controlador sin SQL     | Las sentencias preparadas están únicamente en `src/Persistence/PdoQualityGateRepository.php`. |
| Datos compartidos       | `docs/arquitectura.md` y `docs/diagrams/shared-repository.puml`.                              |
