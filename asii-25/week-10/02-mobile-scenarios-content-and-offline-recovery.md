# ASII-25 — Escenarios móviles, contenido y recuperación offline

## 1. Identificación

**Estudiante:** Cindy Maytté Ruano Calderón  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Semana:** 10

## 2. Escenario 1 — Jerarquía de contenido a 360 px

### Contexto

Un QA Lead consulta el dashboard en un teléfono de 360 px mientras una ejecución E2E está `RUNNING` y otra tiene Gate `FAIL`. La decisión inmediata es saber qué requiere atención sin leer headers HTTP o logs completos.

### Contenido visible

1. Tenant autorizado enmascarado: `tenant-7f2a••`.
2. Estado del run con texto, icono y color.
3. `run_id` abreviado, suite y commit abreviado.
4. Quality Gate: `PASS`, `FAIL` o `PENDING`.
5. Evidencia: `pending`, `ready` o `invalid`.
6. Acción principal contextual: `Ver detalle`, `Reintentar` o `Planificar`.

### Contenido colapsable

- Headers HTTP, correlation ID y detalles de autenticación.
- Branch, duración completa y timestamps históricos.
- Lista completa de archivos y hashes largos.
- Logs extensos, que se abren en visor con scroll independiente.
- Auditoría completa, disponible tras tocar `Ver auditoría`.

### Decisiones de diseño

- Las cards sustituyen la tabla porque las columnas no caben sin desplazamiento horizontal.
- El estado y la acción aparecen antes que los metadatos.
- Solo una acción primaria aparece por card para evitar errores táctiles.
- El drawer de filtros no ocupa la vista hasta que el usuario lo solicita.
- El acordeón anuncia su estado con `aria-expanded` y conserva el foco.

### Criterios de aceptación

- A 360 px no hay scroll horizontal de la página.
- El usuario identifica estado, tenant, Gate y siguiente acción en menos de una pantalla.
- Abrir/cerrar metadatos no cambia el estado del run.
- Los headers no se muestran por defecto ni incluyen JWT.
- El contraste y el texto comunican estados sin depender del color.

## 3. Escenario 2 — Conexión limitada y modo offline

### Contexto

Un Developer monitorea un run desde una red móvil inestable. La conexión se pierde después de crear la ejecución y durante el empaquetado de evidencias.

### Comportamiento por estado de red

| Estado       | Indicador                         | Lecturas permitidas       | Escrituras permitidas              |
|--------------|-----------------------------------|---------------------------|------------------------------------|
| Online       | `Online` y última sincronización  | Datos actuales            | Crear/reintentar con API           |
| Offline      | Banner persistente `Sin conexión` | Último snapshot cacheado  | Encolar solo acciones idempotentes |
| Reconnecting | `Reconectando… intento 2/3`       | Snapshot + estado local   | Retry con backoff                  |
| Sync failed  | `No se pudo sincronizar`          | Snapshot marcado obsoleto | `Reintentar` o cancelar            |

### Estrategia técnica

1. Cachear localmente solo estados y metadatos no sensibles del run: `run_id`, tenant parcial, commit, Gate, timestamps y estado de evidencia.
2. No guardar JWT, URLs permanentes, logs clínicos ni secretos en `localStorage`.
3. Usar Service Worker/Cache Storage para lecturas controladas y `Background Sync` para eventos idempotentes.
4. Usar una cola local con `operationId`, tipo, payload mínimo, tenant contextual y número de intentos.
5. Al volver online, refrescar el token si expiró y revalidar permisos antes de enviar.
6. Enviar `Idempotency-Key` para no duplicar creación, retry o empaquetado.
7. Resolver conflictos con la versión de servidor: el servidor gana; la UI informa el cambio.

Ejemplo conceptual de cola:

```js
const pendingOperation = {
  operationId: crypto.randomUUID(),
  type: 'GET_RUN_STATUS',
  runId: '8e12••',
  tenantId: 'tenant-7f2a••',
  attempts: 0,
  createdAt: new Date().toISOString()
};

// Persistir solo la operación mínima y no el JWT.
await localQueue.put(pendingOperation);
```

### Pérdida de conexión durante un POST

- Si no existe confirmación del servidor, la UI no muestra `created` ni reenvía automáticamente sin idempotencia.
- Muestra `No podemos confirmar la creación. Verificar estado`.
- Al recuperar conexión, consulta por `Idempotency-Key` antes de reintentar.
- El usuario puede cancelar la operación pendiente; la cancelación también se audita.

### Pérdida durante monitoreo

- Mantener el último estado visible con etiqueta `Actualizado hace X min`.
- Desactivar acciones que exigen datos actuales, como aprobar manualmente o descargar una URL temporal.
- Permitir revisar el snapshot local sin presentarlo como definitivo.
- Reanudar polling con backoff al volver online; detenerlo en `passed`, `failed` o `invalid`.

### Evidencias y caché

- Un archivo `zip/tar.gz` no se cachea completo por defecto en móvil.
- Se cachean nombre, tamaño, estado y hash; la descarga requiere conexión y autorización actual.
- Si el hash local no coincide con el recibido por backend, marcar `invalid` y ocultar descarga.
- La UI muestra espacio disponible y permite limpiar snapshots antiguos.

## 4. Reintentos, timeouts y feedback

- Backoff: 1 s, 2 s, 4 s, máximo tres intentos para estados consultables.
- No reintentar automáticamente `401`, `403` ni errores de validación `422`.
- Un timeout conserva formulario, filtros y último resultado válido.
- Mostrar correlation ID abreviado para soporte.
- Tras tres fallos, ofrecer `Abrir incidencia` con datos no sensibles.
- Los mensajes son accionables: `Reintentar`, `Renovar sesión`, `Ver estado` o `Cancelar`.

## 5. Matriz de contenido por rol móvil

| Rol       | Prioridad 1                            | Prioridad 2                  | Ocultar inicialmente              |
|-----------|----------------------------------------|------------------------------|-----------------------------------|
| QA Lead   | Gate, p95, fallos, confirmar           | suites, evidencia, auditoría | headers y logs completos          |
| Developer | estado del run, fallo accionable, hash | logs, commit, descarga       | configuración administrativa      |
| CI Runner | estado API, código, correlation ID     | reintentos, evidencia        | navegación visual y ayuda extensa |

## 6. Pruebas móviles de aceptación

- Viewports: 320, 360, 390 y 430 px; orientación vertical y horizontal.
- Red: online, offline, latencia alta, desconexión durante POST y recuperación.
- Dispositivo: teclado virtual, zoom 200%, touch targets de 44 px.
- Integridad: tenant ajeno, token expirado, hash alterado y URL temporal caducada.
- Resultado esperado: no duplicar runs, no mostrar secretos y no marcar un snapshot obsoleto como estado actual.
