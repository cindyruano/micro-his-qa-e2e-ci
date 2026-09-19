# ASII-25 — Hallazgos y backlog priorizado

## 1. Identificación

**Estudiante:** Cindy Maytté Ruano Calderón  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Semana:** 9

## 2. Criterio de severidad

- **P1 crítico:** bloquea una operación, puede causar ejecución incorrecta, pérdida de evidencia o incumplimiento WCAG esencial.
- **P2 alto/moderado:** degrada recuperación, comprensión o acceso a una función importante.
- **P3 bajo:** mejora claridad o eficiencia sin bloquear el flujo principal.

## 3. Registro de seis hallazgos

### H-01 — Contraste insuficiente en `PENDING`

- **Tipo:** Accesibilidad, WCAG 1.4.3.
- **Severidad:** P1.
- **Observación:** El badge `PENDING` usa texto de bajo contraste sobre fondo claro y el estado puede depender visualmente del color.
- **Impacto:** Personas con baja visión pueden confundir una evidencia en cola con una lista o error; se afecta la lectura del estado operativo.
- **Corrección propuesta:** Definir colores con contraste mínimo 4.5:1 para texto normal, añadir icono y texto, y probar normal/hover/foco/disabled.
- **Criterio verificable:** El badge supera 4.5:1 en herramienta de contraste y comunica `PENDING` sin color.

### H-02 — Modal no se cierra con `Escape`

- **Tipo:** Accesibilidad, WCAG 2.1.1 y 2.1.2.
- **Severidad:** P1.
- **Observación:** El modal `Planificar prueba` no tiene cierre por teclado ni devolución de foco.
- **Impacto:** Usuarios de teclado o lector de pantalla pueden quedar atrapados y no completar/cancelar la planificación.
- **Corrección propuesta:** Implementar focus trap controlado, `Escape` para cancelar sin enviar, botón cerrar con nombre accesible y retorno de foco al disparador.
- **Criterio verificable:** Con teclado, Tab permanece en el modal, Escape lo cierra y el foco vuelve a `Planificar ejecución`.

### H-03 — Logs en vivo no anunciados

- **Tipo:** Accesibilidad, WCAG 4.1.3.
- **Severidad:** P1.
- **Observación:** El visor agrega líneas de log sin `aria-live="polite"` ni resumen de cambios.
- **Impacto:** Un usuario de lector de pantalla no sabe que una suite avanzó, falló o terminó.
- **Corrección propuesta:** Añadir región de estado `aria-live="polite"`, anunciar solo eventos resumidos y mantener el foco estable.
- **Criterio verificable:** NVDA/VoiceOver anuncia `Suite auth completada` sin leer todo el log ni mover el foco.

### H-04 — Falta confirmación al abortar ejecución

- **Tipo:** Usabilidad, heurística Nielsen #5.
- **Severidad:** P1.
- **Observación:** La acción de abortar una ejecución E2E activa no explica que detendrá el runner y dejará evidencia parcial.
- **Impacto:** Se pueden perder resultados, desperdiciar una ejecución CI o dejar un estado ambiguo.
- **Corrección propuesta:** Modal de confirmación con run ID, tenant, consecuencia y opciones `Continuar`/`Abortar ejecución`.
- **Criterio verificable:** Un clic accidental no aborta; el run solo cambia a `aborted` tras confirmación y queda auditado.

### H-05 — Error ambiguo de SHA-256

- **Tipo:** Usabilidad, heurística Nielsen #9.
- **Severidad:** P2.
- **Observación:** La interfaz muestra `Error 500` cuando la huella no coincide.
- **Impacto:** El Developer no sabe si reintentar, reportar integridad o revisar permisos.
- **Corrección propuesta:** Mostrar `Hash mismatch`, explicar que el archivo no coincide y ofrecer `Regenerar evidencia`/`Abrir incidencia`.
- **Criterio verificable:** Ante hash distinto, aparece mensaje accionable, correlation ID y no se habilita descarga del archivo inválido.

### H-06 — Sin indicador durante desempaquetado

- **Tipo:** Usabilidad, heurística Nielsen #1.
- **Severidad:** P2.
- **Observación:** Al procesar `zip/tar.gz`, la vista parece detenida y no comunica progreso ni estado.
- **Impacto:** El usuario repite la acción, duplica solicitudes o interpreta que CI falló.
- **Corrección propuesta:** Mostrar estado `Desempaquetando evidencia`, última actualización, progreso indeterminado y bloqueo idempotente de la acción.
- **Criterio verificable:** Mientras procesa, la UI muestra estado actualizado y una acción repetida no crea otro job.

## 4. Backlog priorizado

| Orden | ID   | Severidad | Impacto en QA/CI/CD                            | Corrección técnica                                          | Criterio de aceptación                             |
|------:|------|-----------|------------------------------------------------|-------------------------------------------------------------|----------------------------------------------------|
|     1 | H-02 | P1        | Bloquea planificación por teclado              | Focus trap, `Escape`, retorno de foco y `aria-modal`.       | Test teclado abre/cierra sin pérdida de foco.      |
|     2 | H-03 | P1        | Oculta progreso/fallos al QA Lead              | `aria-live="polite"`, resumen de eventos y `role="status"`. | Lector anuncia cambios sin leer todo el log.       |
|     3 | H-01 | P1        | Puede inducir decisión errónea sobre evidencia | CSS de contraste, icono y texto de estado.                  | Contraste >= 4.5:1 y lectura sin color.            |
|     4 | H-04 | P1        | Puede abortar una ejecución CI válida          | Modal de confirmación y transición auditada.                | Sin confirmación no cambia el estado.              |
|     5 | H-05 | P2        | Retrasa recuperación de integridad             | Mapeo de error `HASH_MISMATCH` y acción regenerar.          | Mensaje accionable y descarga bloqueada.           |
|     6 | H-06 | P2        | Genera retries o jobs duplicados               | Estado de procesamiento, polling y `Idempotency-Key`.       | Una acción produce un solo job y muestra progreso. |

## 5. Plan de corrección

### Sprint 1 — P1 accesibilidad y seguridad operacional

1. Corregir contraste y estados redundantes.
2. Implementar gestión completa de foco del modal.
3. Añadir anuncios accesibles a logs y estados del Gate.
4. Incorporar confirmación y auditoría de abortar.

### Sprint 2 — P2 recuperación y comprensión

1. Definir catálogo de errores UX/API, incluyendo `HASH_MISMATCH`.
2. Añadir progreso de empaquetado/desempaquetado.
3. Cubrir retry, timeout y acciones idempotentes en pruebas E2E.

## 6. Evidencia de cierre

Cada tarea debe adjuntar: captura antes/después, criterio WCAG o heurístico, prueba automatizada o manual, navegador/lector utilizado, `run_id`, commit y resultado del Quality Gate. El backlog se cierra solo cuando la corrección pasa la prueba y no introduce regresión en tenant, evidencia ni CI.
