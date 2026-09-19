# ASII-25 — Camino feliz y error crítico

## 1. Identificación

**Estudiante:** Cindy Maytté Ruano Calderón  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Semana:** 11

## 2. Escenario 1 — Camino feliz

### Flujo funcional

1. **QA Lead / Dashboard:** selecciona el tenant autorizado y pulsa `Planificar ejecución`.
2. **Planificación:** introduce commit y suite; el frontend valida campos y muestra confirmación.
3. **API:** crea el run con `Idempotency-Key`, `X-Tenant-ID` y correlation ID.
4. **CI Runner:** ejecuta la suite E2E en el commit indicado.
5. **Evidencia:** se genera el paquete y se calcula SHA-256 sobre el archivo final.
6. **Quality Gate:** cobertura, fallos críticos y p95 cumplen: p95 `1.42 s < 2 s`.
7. **QA Lead:** revisa trazabilidad y pulsa `Aprobar despliegue`.
8. **CI/PR:** actualiza el check y deja la versión lista para merge/despliegue.

### Desktop

```text
Dashboard -> Planificar -> Run RUNNING -> Gate PASS + READY -> Despliegue aprobado
```

La interfaz muestra métricas en columnas, log visible y acciones en línea.

### Móvil

```text
Card RUN -> formulario 1 columna -> Gate PASS -> botón inferior Aprobar
```

La evidencia queda en una card; hash y auditoría se consultan bajo demanda.

### Resultado esperado

- Estado de run: `approved`.
- Gate: `PASS`.
- Evidencia: `READY`.
- SHA-256 coincide con el artefacto descargable.
- PR puede avanzar sin romper aislamiento de tenant.

## 3. Escenario 2 — Error crítico y recuperación

### Variante A: fallo E2E

1. CI Runner crea el run y una suite crítica falla.
2. El log muestra la prueba y la causa accionable.
3. Quality Gate responde `FAIL` con `STATUS: REJECTED`.
4. La UI bloquea `Aprobar despliegue` y el check del PR queda fallido.
5. QA Lead o Developer corrige el commit y pulsa `Reintentar`.
6. El retry usa una nueva operación con `Idempotency-Key`; no duplica el run original.
7. El resultado se audita como intento relacionado.

### Variante B: inconsistencia de SHA-256

1. El empaquetador publica un artefacto.
2. La verificación compara el hash calculado con el hash registrado.
3. La comparación falla: estado `INVALID`, mensaje `Hash mismatch`.
4. La descarga queda deshabilitada y el Gate no puede cerrar la evidencia como válida.
5. La UI ofrece `Regenerar evidencia` o `Abrir incidencia`.
6. Tras regenerar, el backend verifica el nuevo hash antes de `READY`.

### Desktop

```text
Gate FAIL / REJECTED -> banner rojo accionable -> Retry o corregir PR
                                               -> no despliegue
```

### Móvil

```text
Card FAIL -> detalle compacto -> [Reintentar]
                    -> mensaje y correlation ID -> estado actualizado
```

El botón de despliegue permanece deshabilitado y no se oculta la razón del bloqueo.

## 4. Estados y mensajes

| Estado    | Mensaje                                           | Acción                        |
|-----------|---------------------------------------------------|-------------------------------|
| `RUNNING` | `Ejecutando suites E2E…`                          | Ver log / actualizar          |
| `PASS`    | `Quality Gate aprobado.`                          | Aprobar despliegue            |
| `FAIL`    | `Quality Gate rechazado: revise fallos críticos.` | Corregir / reintentar         |
| `PENDING` | `Evidencia en preparación.`                       | Consultar estado              |
| `INVALID` | `Hash mismatch: evidencia no verificable.`        | Regenerar / incidencia        |
| Timeout   | `No se confirmó la operación.`                    | Consultar por idempotency key |

## 5. Matriz de consistencia y accesibilidad

| Área      | Desktop                                               | Móvil                               | Criterio verificable                                       |
|-----------|-------------------------------------------------------|-------------------------------------|------------------------------------------------------------|
| Roles     | QA Lead aprueba; Developer consulta; CI actualiza API | Igual, con acciones ocultas por rol | Developer nunca ve aprobar si no tiene permiso.            |
| Estados   | Badge + métrica + mensaje                             | Card + badge + mensaje              | El estado no depende solo del color.                       |
| Teclado   | Tab, Enter, Escape en formulario                      | Teclado virtual y foco visible      | Tab recorre orden lógico; Escape cierra/abandona sin POST. |
| Contraste | PASS/FAIL/READY legibles                              | Mismo contraste en cards            | Mínimo WCAG AA, 4.5:1 para texto.                          |
| Evidencia | Tabla, hash y log                                     | Card, hash truncado y scroll        | Solo `READY` permite descarga autorizada.                  |
| Error     | Banner y acción contextual                            | Banner compacto y botón pulgar      | Retry idempotente, sin duplicados.                         |
| Datos     | JWT oculto, tenant parcial                            | JWT oculto, tenant parcial          | No se almacenan secretos en UI/local cache.                |

## 6. Pruebas de aceptación del prototipo

- `happy path`: Dashboard → Planificar → Ejecutar → Gate PASS → Despliegue.
- `critical error`: activar Error crítico → Gate FAIL/REJECTED → botón bloqueado → Retry.
- Desktop: viewport mínimo de 1024 px sin solapamientos.
- Móvil: 320, 360 y 430 px sin scroll horizontal.
- Teclado: todos los controles alcanzables; foco visible y Escape operativo.
- Seguridad: ningún escenario muestra JWT completo ni permite cambiar tenant sin autorización.
- Accesibilidad: mensajes de estado en live region y texto redundante para colores.
