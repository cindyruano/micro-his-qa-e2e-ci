# ASII-25 — Requisitos y diseño de calidad

## 1. Identificación

| Campo              | Valor                                                       |
|--------------------|-------------------------------------------------------------|
| Estudiante         | Cindy Maytté Ruano Calderón                                 |
| Módulo oficial     | ASII-25 — QA, pruebas E2E, CI y guía de despliegue final    |
| Actividad adaptada | Gestión de ejecuciones automatizadas y calidad de versiones |
| Rol / dominio      | QA / Aseguramiento de Calidad y CI/CD                       |
| Semana             | 2                                                           |
| Tema               | Proceso y modelo de diseño; principios SOLID                |
| Evidencia          | RF, RNF, criterios de aceptación y diseño DIP               |

## 2. Propósito y límite del diseño

Este documento define los requisitos del flujo de planificación de pruebas, ejecución E2E, conservación de evidencia y aplicación de Quality Gates para el HIS. El diseño se apoya en JWT, `X-Tenant-ID`, RBAC y el pipeline de CI/CD existente.

La evidencia describe contratos y responsabilidades del proceso. No implementa suites Playwright/Cypress, workflows definitivos de GitHub Actions ni infraestructura productiva.

## 3. Requisitos Funcionales

| ID    | Requisito                          | Criterio verificable                                                                                                                |
|-------|------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------|
| RF-01 | Planificar ejecuciones de calidad  | El sistema debe registrar commit, rama, entorno, tenant de prueba, suites y umbrales antes de iniciar la ejecución.                 |
| RF-02 | Disparar suites E2E                | El pipeline debe ejecutar las suites seleccionadas y registrar cada caso como aprobado, fallido, omitido o bloqueado.               |
| RF-03 | Evaluar el Quality Gate            | El sistema debe comparar cobertura, fallos críticos, estabilidad y duración contra umbrales configurados.                           |
| RF-04 | Recolectar evidencias y artefactos | El proceso debe conservar logs, resultados JUnit, cobertura, capturas y vídeos asociados al commit y a la ejecución.                |
| RF-05 | Bloquear o aprobar Pull Requests   | El pipeline debe actualizar el estado del PR: aprobado cuando el Gate pasa y bloqueado cuando existe un incumplimiento crítico.     |
| RF-06 | Auditar ejecuciones                | El sistema debe permitir consultar quién, cuándo, con qué versión y bajo qué tenant ejecutó una validación, junto con su resultado. |

## 4. Requisitos No Funcionales

| ID     | Requisito                            | Criterio verificable                                                                                                                                                                 |
|--------|--------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| RNF-01 | Tiempo de ejecución en CI            | La suite crítica debe iniciar automáticamente al recibir un PR y finalizar dentro del presupuesto definido por el pipeline, con timeout controlado y diagnóstico del incumplimiento. |
| RNF-02 | Aislamiento multitenant              | Cada ejecución debe usar datos sintéticos y un tenant aislado; una solicitud con otro `X-Tenant-ID` debe ser rechazada sin revelar información.                                      |
| RNF-03 | Seguridad e integridad de evidencias | Los artefactos no deben contener secretos, tokens JWT ni datos clínicos reales; una evidencia publicada debe ser inmutable o contar con hash y registro de integridad.               |
| RNF-04 | Mantenibilidad                       | Las reglas de calidad, el almacenamiento, el runner y el proveedor de CI deben poder sustituirse mediante interfaces sin modificar el servicio de orquestación.                      |

## 5. Criterios de aceptación

### CA-01 — Quality Gate aprobado

```gherkin
Característica: Evaluación de calidad de una versión

Escenario: Aprobar un PR cuando las validaciones cumplen los umbrales
  Dado que existe una ejecución asociada a un commit y un tenant de prueba aislado
  Y las suites E2E terminan sin fallos críticos
  Y la cobertura y la duración cumplen los umbrales configurados
  Cuando el Quality Gate evalúa los resultados
  Entonces la ejecución queda aprobada
  Y el pipeline publica los artefactos y métricas
  Y el PR recibe un estado habilitado para revisión o merge
```

### CA-02 — PR bloqueado por fallo o baja cobertura

```gherkin
Escenario: Bloquear un PR cuando falla una prueba crítica o no se alcanza la cobertura mínima
  Dado que el pipeline ejecuta la suite E2E asociada al PR
  Y una prueba crítica falla o la cobertura queda por debajo del umbral
  Cuando el Quality Gate evalúa los resultados
  Entonces la ejecución queda rechazada
  Y el pipeline publica el fallo, los logs y las evidencias disponibles
  Y el PR queda bloqueado para merge
  Y la auditoría registra la causa y el commit evaluado
```

### CA-03 — Validación de tenant en el Runner

```gherkin
Escenario: Impedir acceso entre tenants durante una ejecución E2E
  Dado que el Runner tiene un tenant de prueba asignado y un token válido
  Cuando intenta consultar un recurso usando un X-Tenant-ID diferente
  Entonces la API responde con el código de autorización o inexistencia definido por el contrato
  Y no devuelve datos del tenant ajeno
  Y el Runner registra la validación como aprobada
  Y la evidencia no contiene información clínica del tenant no autorizado
```

### CA-04 — Auditoría de artefactos

```gherkin
Escenario: Consultar una ejecución con sus evidencias
  Dado que una ejecución terminó y publicó resultados JUnit, cobertura y logs
  Cuando QA Lead consulta el identificador de la ejecución
  Entonces obtiene commit, entorno, tenant, fecha, resultado y enlaces a los artefactos
  Y los artefactos corresponden a la misma ejecución
  Y no se exponen secretos ni tokens
```

## 6. Diseño lógico del proceso

1. El desarrollador abre o actualiza un PR.
2. CI registra la versión, prepara el entorno y crea el contexto de tenant de prueba.
3. El Runner ejecuta las suites y solicita a la API validaciones de autenticación, RBAC y aislamiento.
4. El recolector almacena resultados y evidencias con el identificador de ejecución.
5. El evaluador compara las métricas contra el Quality Gate.
6. El adaptador del proveedor actualiza el estado del PR y el auditor conserva la trazabilidad.

Las dependencias variables del proceso, como el proveedor de CI, el almacenamiento, el evaluador y la validación de tenant, deben ingresar al orquestador mediante abstracciones. Esta decisión se desarrolla en el entregable de refactorización SOLID/DIP.
