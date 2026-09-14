# Evidencia ASII-25

## Identificación

- Commit evaluado: `a3cd0b9f060ffcab5eb77c7dceb6ad14ec62a54d` (estado del workspace al ejecutar la validación).
- Ambiente: local, SQLite reproducible; CI usa PostgreSQL efímero.
- Datos: fixtures sintéticos, sin secretos ni datos clínicos reales.

## Comandos y resultado observado

La salida se registra en esta tabla después de ejecutar la matriz final:

| Comando                                      | Resultado                                            | Estado                       |
|----------------------------------------------|------------------------------------------------------|------------------------------|
| `php -v`                                     | PHP 8.2.12                                           | Aprobado                     |
| `php artisan --version`                      | Laravel Framework 12.58.0                            | Aprobado                     |
| `php artisan migrate:fresh --seed --force`   | Migraciones y seeders correctos, incluidas tablas QA | Aprobado                     |
| `php artisan test`                           | 50 passed, 5 skipped, 115 assertions                 | Aprobado                     |
| `php artisan qa:quality-gate ...`            | `QUALITY GATE PASSED`, código 0                      | Aprobado                     |
| `... --induce-failure`                       | `QUALITY GATE FAILED`, código 1                      | Aprobado: bloqueo demostrado |
| `npm run build`                              | Vite 7.3.2, manifest generado                        | Aprobado                     |
| `git status --short`                         | Cambios del trabajo de integración y documentación   | Observado                    |
| `git log --oneline --decorate --graph -n 20` | 8 commits sustantivos en la rama                     | Aprobado                     |
| `git worktree list`                          | Worktree de feature y develop activos                | Aprobado                     |

## Artefactos

- Fuentes y renders: `diagramas/*.puml` y `diagramas/*.svg`.
- Persistencia: tablas `qa_runs`, `qa_gate_results`, `qa_evidence`.
- CI: workflow `branch-protection.yml`, artefacto `qa-evidence-<sha>`.
