# Evidencia - Semana 3

## Alcance validado

- PHP vanilla 8.2+ con autoload propio.
- Separación Presentation, Application, Domain y Persistence.
- PDO SQLite con sentencias preparadas y transacción para `qa_runs` y `qa_gate_results`.
- Evidencia JSON en `qa_evidence`.
- Dobles en memoria para camino feliz y error de persistencia.

## Ejecución observada

Comandos ejecutados desde `docs/asii-25/week-03`:

| Comando                                        | Resultado                         |
|------------------------------------------------|-----------------------------------|
| `php scripts/run-tests.php`                    | 4 passed, 0 failed                |
| `php scripts/init-db.php`                      | Base SQLite inicializada          |
| `php bin/qa.php abc123 local`                  | `QUALITY GATE PASSED`, código `0` |
| `php bin/qa.php abc123 local --induce-failure` | `QUALITY GATE FAILED`, código `1` |

## Cobertura mínima

| Caso                  | Prueba                                                                    |
|-----------------------|---------------------------------------------------------------------------|
| Camino feliz          | Planifica escenarios, ejecuta el doble E2E, persiste run y evidencia.     |
| Regla de dominio      | Rechaza plan vacío y datos obligatorios ausentes.                         |
| Persistencia real     | SQLite verifica una ejecución, dos controles y una evidencia.             |
| Error de persistencia | Un repositorio falso lanza excepción y no se guarda evidencia como éxito. |

## Artefactos arquitectónicos

- [Vista arquitectónica C4/UML](docs/arquitectura.md)
- [Componentes](docs/diagrams/components.puml)
- [Secuencia](docs/diagrams/sequence.puml)
- [Clases y patrones](docs/diagrams/classes.puml)
