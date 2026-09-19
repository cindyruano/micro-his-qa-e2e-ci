# ASII-25 — Checklist de usabilidad y accesibilidad

## 1. Identificación y alcance

| Campo                 | Valor                                                                     |
|-----------------------|---------------------------------------------------------------------------|
| Estudiante            | Cindy Maytté Ruano Calderón                                               |
| Módulo oficial        | ASII-25 — QA, pruebas E2E, CI y guía de despliegue final                  |
| Actividad adaptada    | Gestión de ejecuciones automatizadas y calidad de versiones               |
| Rol / dominio         | QA / Aseguramiento de Calidad y CI/CD                                     |
| Semana                | 9                                                                         |
| Superficies evaluadas | Dashboard de Runs, Planificación E2E, Visor de Evidencias y Quality Gates |
| Referencias           | Nielsen 10 heurísticas, WCAG 2.1/2.2 AA                                   |

**Escala:** `Cumple`, `Parcial`, `No cumple`, `No aplica`. La evaluación es documental sobre los wireframes y flujo de la Semana 8; los puntos no implementados deben verificarse después con pruebas manuales y automatizadas.

## 2. Checklist de las 10 heurísticas de Nielsen

|  # | Heurística                                  | Evaluación                                                                                                     | Evidencia / criterio                                                                                            | Estado    |
|---:|---------------------------------------------|----------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------|-----------|
|  1 | Visibilidad del estado del sistema          | Los runs muestran `queued`, `running`, `passed`, `failed`; la evidencia muestra `pending`, `ready`, `invalid`. | Cada mutación comunica estado, última actualización y `X-Correlation-ID`; no se muestra `PASS` durante timeout. | Parcial   |
|  2 | Correspondencia entre sistema y mundo real  | Usa términos `suite`, `commit`, `Quality Gate`, `cobertura` y `evidencia`.                                     | Cada término técnico tiene tooltip o ayuda contextual para QA/Developer.                                        | Cumple    |
|  3 | Control y libertad del usuario              | Permite cancelar/volver y reintentar operaciones recuperables.                                                 | Un retry no duplica el run; cancelar una ejecución activa exige confirmar impacto.                              | Parcial   |
|  4 | Consistencia y estándares                   | Badges, botones y estados mantienen el mismo significado en dashboard y detalle.                               | `PASS`, `FAIL`, `PENDING` tienen texto, icono y color consistentes.                                             | Cumple    |
|  5 | Prevención de errores                       | Valida commit, suites, tenant y umbrales antes de crear un run.                                                | Falta confirmación explícita antes de abortar una ejecución E2E activa.                                         | No cumple |
|  6 | Reconocimiento antes que recuerdo           | Filtros, tenant, commit, run ID y estados permanecen visibles.                                                 | El usuario no necesita recordar el `run_id` para ir del dashboard al detalle.                                   | Cumple    |
|  7 | Flexibilidad y eficiencia                   | QA Lead puede filtrar y el CI Runner usa API idempotente.                                                      | Debe existir acceso rápido a reintento, copiar correlation ID y revisar fallos.                                 | Parcial   |
|  8 | Diseño estético y minimalista               | La información prioriza métricas, estado y acciones principales.                                               | No presentar logs completos en la tabla; cargarlos bajo demanda en el visor.                                    | Cumple    |
|  9 | Ayudar a reconocer y recuperarse de errores | Se proponen mensajes para timeout, hash inválido y token expirado.                                             | `Error 500` debe sustituirse por causa accionable como `Hash mismatch`.                                         | No cumple |
| 10 | Ayuda y documentación                       | Tooltips explican p95, tenant, SHA-256 y estados.                                                              | Cada ayuda debe estar asociada al control y ser accesible por teclado.                                          | Parcial   |

## 3. Checklist WCAG 2.1/2.2 AA

### Perceptible

| Criterio                       | Aplicación en Micro-HIS QA                                                       | Verificación                                                  | Estado    |
|--------------------------------|----------------------------------------------------------------------------------|---------------------------------------------------------------|-----------|
| 1.1.1 Contenido no textual     | Iconos de Gate, estado y progreso incluyen texto alternativo o nombre accesible. | Inspección DOM y lector de pantalla.                          | Parcial   |
| 1.3.1 Información y relaciones | Campos, tablas, encabezados y badges tienen estructura semántica.                | HTML semántico, labels asociados y headers de tabla.          | Cumple    |
| 1.4.3 Contraste mínimo         | Texto normal y badges PASS/FAIL/PENDING alcanzan al menos 4.5:1.                 | Analizador de contraste con estados normal, hover y disabled. | No cumple |
| 1.4.11 Contraste no textual    | Foco, bordes de inputs y estados tienen 3:1 contra fondo.                        | Medición de controles y foco visible.                         | Parcial   |
| 1.4.5 Alternativas textuales   | Gráfico p95 no depende solo de una curva o color.                                | Valor numérico, unidad y texto accesible.                     | Cumple    |

### Operable

| Criterio                    | Aplicación en Micro-HIS QA                                            | Verificación                                              | Estado    |
|-----------------------------|-----------------------------------------------------------------------|-----------------------------------------------------------|-----------|
| 2.1.1 Teclado               | Dashboard, modal, filtros, tabs, retry y descarga operan con teclado. | Recorrido solo con Tab, Shift+Tab, Enter, Space y Escape. | No cumple |
| 2.1.2 Sin trampas de foco   | El modal devuelve foco al botón que lo abrió y permite Escape.        | Test de foco antes/durante/después del modal.             | No cumple |
| 2.4.3 Orden del foco        | Contexto -> filtros -> acción -> tabla -> detalle.                    | Secuencia lógica y sin saltos ocultos.                    | Parcial   |
| 2.4.7 / 2.4.11 Foco visible | Foco visible en controles y no oculto por overlays.                   | Captura de estados focus y revisión WCAG 2.2.             | Parcial   |
| 2.5.3 Etiqueta en nombre    | Nombre accesible contiene el texto visible de botones.                | Inspección con Accessibility Tree.                        | Cumple    |

### Comprensible

| Criterio                        | Aplicación en Micro-HIS QA                                   | Verificación                                      | Estado    |
|---------------------------------|--------------------------------------------------------------|---------------------------------------------------|-----------|
| 3.1.1 Idioma de página          | Documento declara idioma principal `es`.                     | `lang="es"` en documento raíz.                    | Parcial   |
| 3.2.1 Al recibir foco           | Enfocar un filtro no dispara una petición destructiva.       | Test de foco sin mutaciones inesperadas.          | Cumple    |
| 3.3.1 Identificación de errores | Campo inválido identifica causa y solución.                  | Mensajes inline asociados con `aria-describedby`. | Cumple    |
| 3.3.3 Sugerencia de corrección  | Commit, suites, p95 y tenant indican cómo corregir.          | Cada error incluye acción o ejemplo válido.       | Parcial   |
| 3.3.4 Prevención de errores     | Confirmación para abortar, reintentar y aprobar manualmente. | Revisión antes de acciones con impacto en CI/CD.  | No cumple |

### Robusto

| Criterio                 | Aplicación en Micro-HIS QA                                                           | Verificación                                  | Estado    |
|--------------------------|--------------------------------------------------------------------------------------|-----------------------------------------------|-----------|
| 4.1.2 Nombre, rol, valor | Botones, modal, badges y controles tienen nombre y estado accesibles.                | Accessibility Tree y validación automatizada. | Parcial   |
| 4.1.3 Mensajes de estado | Logs nuevos y cambio de Gate usan `aria-live="polite"`; errores usan `role="alert"`. | Lector de pantalla anuncia sin mover el foco. | No cumple |
| Compatibilidad           | Funciona con teclado, lector de pantalla y zoom del navegador.                       | NVDA/VoiceOver, 200% zoom y viewport móvil.   | Parcial   |

## 4. Método de evaluación

1. Ejecutar cada flujo con `QA Lead`, `Developer` y `CI Runner`.
2. Repetir con teclado y lector de pantalla.
3. Medir contraste en los cuatro estados y en foco/hover.
4. Simular red caída, timeout, JWT expirado, `403` y hash inválido.
5. Registrar captura, pasos, criterio afectado, severidad y evidencia.
6. Convertir cada hallazgo aceptado en una tarea del backlog.
