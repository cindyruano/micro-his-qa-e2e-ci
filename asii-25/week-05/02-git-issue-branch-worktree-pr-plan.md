# ASII-25 — Plan Git, Issue, Worktree y Pull Request

## 1. Identificación

**Estudiante:** Cindy Maytté Ruano Calderón  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Semana:** 5

## 2. Issue de trabajo

**Issue propuesta:** `#15 - QA, pruebas E2E, CI y guía de despliegue final`

La tarea define el contrato OpenAPI de runs, Quality Gates y evidencias; documenta la frontera cliente-servidor; añade resiliencia, observabilidad y diagrama; y establece los Quality Gates del Pull Request.

Criterios de terminado:

- OpenAPI 3.0 validable con respuestas `200`, `201`, `400`, `401`, `403` y `422`.
- Cabeceras JWT, `X-Tenant-ID` y correlation ID documentadas.
- Diagrama con clientes, API, runner, datos QA y evidencias.
- CI valida Markdown, contrato y pruebas críticas.
- No se introducen secretos ni infraestructura no justificada por métricas.

## 3. Rama oficial

```text
feature/asii-25-qa-pruebas-e2e-ci-y-guia-de-despliegue-fin-cindyruano
```

Commits pequeños y verificables:

```text
docs(asii-25): define contrato REST de ejecuciones QA
docs(asii-25): documenta frontera cliente-servidor
docs(asii-25): agrega diagrama y quality gates del PR
```

## 4. Git Worktree

```bash
git fetch origin
git worktree add ../shi-asii-25-week-05 \\
  -b feature/asii-25-qa-pruebas-e2e-ci-y-guia-de-despliegue-fin-cindyruano \\
  origin/main
cd ../shi-asii-25-week-05
git status --short
php -v
git branch --show-current
```

Al finalizar la revisión y después de conservar la rama remota:

```bash
git push -u origin feature/asii-25-qa-pruebas-e2e-ci-y-guia-de-despliegue-fin-cindyruano
git worktree remove ../shi-asii-25-week-05
```

## 5. Pull Request

Título recomendado:

```text
feat(asii-25): documenta API REST y frontera cliente-servidor QA
```

El PR debe incluir `Closes #15`, resumen del contrato, arquitectura, diagrama, decisión contra la sobreingeniería, evidencia local/CI y riesgos pendientes: runner real, métricas de carga y despliegue.

## 6. Quality Gate del PR

Checks obligatorios:

1. `php -l` sobre PHP si existe implementación.
2. Pruebas unitarias e integración con datos aislados.
3. Validación OpenAPI y JSON Schema.
4. Enlaces Markdown y existencia del SVG.
5. Escaneo de secretos y archivos generados.
6. Aislamiento por tenant y códigos HTTP del contrato.

Política de merge:

- Todos los checks pasan y existe revisión aprobatoria de QA Lead.
- La rama está actualizada con `main`.
- No se permite merge si falla una prueba crítica, el contrato es inválido o aparece un secreto.
- Se usa squash merge para conservar una unidad lógica asociada a la Issue.

## 7. Ciclo de vida

```text
Issue #15 -> Worktree aislado -> feature/asii-25-... -> validación local
-> Push -> Pull Request -> CI + Quality Gate + revisión QA
-> Squash merge -> cierre de Issue y eliminación del worktree
```
