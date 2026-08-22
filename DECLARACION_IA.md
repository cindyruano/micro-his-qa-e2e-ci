# Declaración de Uso de Inteligencia Artificial

**Estudiante:** Cindy Maytté Ruano Calderón
**GitHub:** `cindyruano`
**Actividad:** Micro-HIS — QA, pruebas E2E, CI y guía de despliegue final
**Fecha:** 21 de agosto de 2026

## 1. Herramienta utilizada

- **Herramienta:** Claude (Anthropic), asistente de IA conversacional con
  ejecución de código.
- **Modalidad de uso:** Generación asistida de código y documentación dentro
  de un entorno de ejecución controlado, con validación humana posterior.

## 2. Propósito del uso

Se utilizó la herramienta para acelerar el andamiaje inicial (boilerplate) del
micro-monolito en PHP 8.2+ vanilla: estructura de carpetas por capas
(Presentation, Application, Domain, Persistence), autoload PSR-4 manual sin
Composer, y un primer borrador de las reglas de dominio, casos de uso,
repositorios PDO y pruebas automatizadas correspondientes al módulo asignado
(QA, pruebas E2E, CI y guía de despliegue final).

## 3. Prompts relevantes (resumen)

1. Solicitud de construcción del módulo a partir de la consigna oficial de la
   actividad (instrucciones comunes, entregables, criterios de aceptación y
   la consigna individual del módulo 25 — QA/E2E/CI).
2. Solicitud de generación de pruebas automatizadas cubriendo explícitamente
   camino feliz, una regla de dominio y un error de persistencia mediante
   base de prueba real.
3. Solicitud de evidencia Git (historial, commits, `git log --oneline`) y de
   un documento final en formato Word con portada, índice, introducción,
   desarrollo, conclusión y bibliografía.

## 4. Partes aceptadas sin modificación

- Estructura de carpetas por capas y convención de nombres de clases.
- Value Object `ExecutionStatus` (máquina de estados) y `Environment`.
- Esquema SQL (`schema.sql`) con llaves foráneas.

## 5. Partes modificadas o corregidas por el estudiante

- **Corrección de orden de validación en `TestExecution::finish()`:** la
  primera versión generada validaba la regla "requiere evidencia" antes que
  la validez de la transición de estado, lo que hacía que un intento de
  saltar directamente de `PLANNED` a `PASSED` (transición inválida) fuera
  reportado incorrectamente como "falta evidencia" en lugar de "transición
  inválida". Se identificó el fallo al ejecutar la suite de pruebas (1 de 11
  pruebas fallaba) y se corrigió el orden de las validaciones: primero se
  verifica la transición de estado, luego la evidencia.
- Ajuste de mensajes de error y nombres de comandos de la CLI para
  reflejar el vocabulario del dominio de QA (planes, ejecuciones, evidencias,
  quality gate) en español, consistente con el resto de la documentación de
  la estudiante.
- Revisión y validación manual de los 11 casos de prueba, ejecutándolos
  localmente antes de considerarlos evidencia válida para la entrega.

## 6. Validación humana

La estudiante ejecutó localmente:

```bash
php tests/run_tests.php
```

confirmando el resultado `Total: 11 | Aprobadas: 11 | Fallidas: 0` antes de
incorporar el código al repositorio, y ejecutó manualmente el flujo completo
vía CLI (`plan:create`, `execution:start`, `evidence:attach`,
`execution:finish`, `gate:evaluate`) verificando tanto el caso de aprobación
como el caso de rechazo del quality gate.

## 7. Alcance no cubierto por la IA

La defensa oral, la explicación de las decisiones de diseño y cualquier
modificación en vivo solicitada durante la evaluación serán realizadas
directamente por la estudiante, sin apoyo de IA, conforme a las instrucciones
comunes de la actividad.
