# ASII-25 — Alcance, actores y casos de uso

## 1. Identificación de la actividad

| Campo              | Valor                                                       |
|--------------------|-------------------------------------------------------------|
| Módulo             | ASII-25                                                     |
| Estudiante         | Cindy Maytté Ruano Calderón                                 |
| Módulo oficial     | QA, pruebas E2E, CI y guía de despliegue final              |
| Actividad adaptada | Gestión de ejecuciones automatizadas y calidad de versiones |
| Semana             | 1                                                           |
| Tipo de evidencia  | Diseño conceptual de QA, CI y trazabilidad                  |

## 2. Contexto del repositorio

| Base existente                        | Propuesta ASII-25                               |
|---------------------------------------|-------------------------------------------------|
| JWT, `X-Tenant-ID`, RBAC y CI/CD base | Pipeline de validación de calidad               |
| API y módulos Laravel del HIS         | Suites E2E para flujos críticos                 |
| Middleware y permisos del tenant      | Métricas de calidad y reportes                  |
| Pruebas automatizadas parciales       | Quality Gates para aprobar o bloquear versiones |

## 3. Problema y objetivo

El HIS requiere garantizar que los despliegues e integraciones cumplan métricas de calidad sin romper la compatibilidad multitenant ni la seguridad RBAC.
El objetivo de la Semana 1 es modelar la ejecución de pruebas E2E en CI, la evaluación del Quality Gate, la decisión sobre Pull Requests, la auditoría de ejecuciones y el reporte de cobertura y fallos.

## 4. Límite y alcance de la Semana 1

El sistema modelado comprende la preparación de una ejecución, el disparo de suites E2E, la validación contra la API HIS y su tenant de prueba, el cálculo de métricas, la evaluación del Quality Gate y la publicación de evidencias.

Incluye:
- Diseño del flujo de pruebas E2E.
- Definición de ejecuciones en CI.
- Gestión de Quality Gates.
- Reportes de calidad, cobertura y fallos.
- Verificación conceptual de JWT, `X-Tenant-ID`, RBAC y aislamiento entre tenants.

Fuera de alcance:
- Código final de suites Playwright/Cypress.
- Scripts definitivos de GitHub Actions.
- Configuración de infraestructura real.

## 5. Actores principales

| Actor                                | Tipo                    | Responsabilidad                                                        |
|--------------------------------------|-------------------------|------------------------------------------------------------------------|
| `QA Engineer / Lead QA`              | Primario                | Define criterios, revisa resultados y decide la calidad de la versión. |
| `Desarrollador / Contribuidor`       | Secundario              | Propone cambios, abre el PR y corrige fallos reportados.               |
| `Pipeline de CI/CD / GitHub Actions` | Soporte / sistema       | Ejecuta validaciones, conserva artefactos y aplica el Quality Gate.    |
| `Control de acceso y Tenant HIS`     | Verificación de entorno | Valida JWT, `X-Tenant-ID`, RBAC y aislamiento de datos.                |

## 6. Reglas de negocio

1. Toda ejecución debe usar datos deterministas y un tenant de prueba aislado.
2. Las pruebas críticas deben validar flujo principal, excepciones y códigos HTTP esperados.
3. Un Quality Gate fallido bloquea la aprobación del PR y registra la causa.
4. Una ejecución aprobada publica métricas, logs y evidencias sin secretos reales.
5. Ninguna prueba puede acceder o persistir datos de otro tenant.
6. Cada ejecución debe ser auditable mediante commit, fecha, suite, resultado y artefactos.

## 7. Casos de uso

| ID    | Caso de uso                       | Actor principal                | Resultado esperado                                              |
|-------|-----------------------------------|--------------------------------|-----------------------------------------------------------------|
| UC-01 | Registrar ejecución de calidad    | `QA Engineer / Lead QA`        | La ejecución queda asociada a versión, commit y entorno.        |
| UC-02 | Ejecutar suite E2E crítica        | Pipeline CI/CD                 | La suite devuelve resultados reproducibles por caso.            |
| UC-03 | Validar autenticación y sesión    | Control de acceso y Tenant HIS | JWT, tenant y expiración se validan sin exponer secretos.       |
| UC-04 | Verificar aislamiento multitenant | Control de acceso y Tenant HIS | Se bloquea el acceso a datos de otro tenant.                    |
| UC-05 | Verificar autorización RBAC       | Control de acceso y Tenant HIS | Las operaciones permitidas y denegadas responden correctamente. |
| UC-06 | Calcular métricas de calidad      | Pipeline CI/CD                 | Se consolidan cobertura, fallos, duración y estabilidad.        |
| UC-07 | Evaluar Quality Gate              | `QA Engineer / Lead QA`        | La versión se marca como aprobada o fallida según umbrales.     |
| UC-08 | Bloquear o aprobar Pull Request   | GitHub / PR                    | El PR queda bloqueado o habilitado para revisión y merge.       |
| UC-09 | Auditar ejecución y artefactos    | `QA Engineer / Lead QA`        | Se conserva evidencia trazable, sin datos sensibles.            |
| UC-10 | Reportar cobertura y fallos       | Pipeline CI/CD                 | Se publica un reporte accionable para el equipo.                |

## 8. Diagrama UML de casos de uso

![Diagrama UML de casos de uso](diagrams/caso-uso.svg)
