# ASII-25 — Flujo UX por rol

## 1. Identificación

| Campo              | Valor                                                       |
|--------------------|-------------------------------------------------------------|
| Estudiante         | Cindy Maytté Ruano Calderón                                 |
| Módulo oficial     | ASII-25 — QA, pruebas E2E, CI y guía de despliegue final    |
| Actividad adaptada | Gestión de ejecuciones automatizadas y calidad de versiones |
| Rol / dominio      | QA / Aseguramiento de Calidad y CI/CD                       |
| Semana             | 8                                                           |
| Tema               | Diseño de experiencia de usuario                            |

## 2. Principios UX del módulo QA

- `QARunnerUI` implementa el dashboard y la planificación de ejecuciones.
- `EvidenceLogViewer` implementa el detalle, los logs y la verificación de evidencias.
- La interfaz muestra siempre tenant, commit, `run_id` y estado actual.
- Los tokens JWT nunca se muestran completos; se presenta `••••••` y solo metadatos no sensibles.
- El `tenant_id` se enmascara parcialmente: `tenant-7f2a••••`.
- El SHA-256 se muestra completo solo bajo acción explícita de verificación y con copia controlada.
- Una acción destructiva o irreversible exige confirmación y explicación del impacto.
- Los estados de ejecución y evidencia son distintos: un Gate puede estar aprobado mientras el artefacto permanece `pending`.

## 3. Flujo por rol

### QA Lead

```text
Inicio: Dashboard QA
  -> Seleccionar tenant autorizado
  -> Planificar suites, commit y umbrales
  -> Validar formulario
  -> Confirmar ejecución
  -> Estado loading/queued
  -> Revisar resultados y trazabilidad
  -> Decisión: métricas cumplen?
       Sí -> aprobar/revisar evidencia -> confirmar versión
       No -> ver fallos -> reintentar o devolver al Developer
```

Decisiones y confirmaciones:

- No se permite iniciar sin suites, commit y tenant válido.
- El umbral p95 debe ser menor que 2 segundos y los fallos críticos igual a cero para la política estándar.
- La aprobación manual requiere seleccionar `Confirmar trazabilidad`.
- Un retry conserva el `run_id` de origen y genera una relación de reintento.

### Developer

```text
Inicio: enlace del PR o lista de ejecuciones
  -> Filtrar por tenant y commit
  -> Abrir detalle de run
  -> Esperar Gate si está queued/running
  -> Consultar logs y artefactos
  -> Verificar SHA-256
  -> Decisión: evidencia íntegra?
       Sí -> descargar archivo autorizado
       No -> solicitar regeneración / reportar incidencia
```

- El Developer solo consulta tenants autorizados.
- Un artefacto `pending` muestra progreso y no un enlace roto.
- Un hash inválido muestra error recuperable y acción `Regenerar evidencia`.
- La descarga no expone rutas internas ni tokens de almacenamiento.

### CI Runner / Bot

```text
Inicio: Webhook de PR o API
  -> Enviar JWT, tenant y correlation ID
  -> Crear run idempotente
  -> Consultar estado del Gate
  -> Decisión: Gate passed?
       Sí -> actualizar check y permitir siguiente etapa
       No -> bloquear despliegue y notificar
  -> Encolar empaquetado de evidencias
  -> Publicar resultado y auditoría
```

- El bot no usa navegación visual para ejecutar reglas; consume la API.
- Los errores de timeout se reintentan con backoff limitado.
- Un `401` detiene y solicita renovar el token; un `403` no se reintenta automáticamente.
- El bot nunca marca `passed` por ausencia de respuesta.

## 4. Matriz de estados UI

| Estado            | Condición                          | Interfaz                                   | Acción disponible                  | Mensaje                                                 |
|-------------------|------------------------------------|--------------------------------------------|------------------------------------|---------------------------------------------------------|
| Empty             | No existen runs para tenant/filtro | Ilustración simple, filtro visible         | `Planificar ejecución`             | `No hay ejecuciones para este tenant y filtro.`         |
| Loading           | API o runner procesando            | Skeleton, spinner con texto                | Cancelar si el contrato lo permite | `Cargando estado de la ejecución…`                      |
| Queued            | Run aceptado pero no iniciado      | Badge gris/azul                            | Ver detalles, actualizar           | `La ejecución está en cola.`                            |
| Running           | Suite en ejecución                 | Barra indeterminada y última actualización | Ver logs                           | `Ejecutando suites E2E.`                                |
| Success           | Gate aprobado y respuesta válida   | Badge verde, métricas y confirmación       | Ver evidencia, continuar PR        | `Quality Gate aprobado.`                                |
| Failed            | Gate rechazado                     | Badge rojo, razones y métricas             | Reintentar/corregir                | `Quality Gate rechazado: revise los indicadores.`       |
| Retryable network | Red interrumpida                   | Banner no bloqueante                       | `Reintentar`                       | `No se pudo conectar. Conservamos su selección.`        |
| Timeout           | API excede timeout                 | Banner y correlation ID                    | Reintentar con backoff             | `La operación tardó demasiado; no se duplicará el run.` |
| Expired token     | JWT expirado                       | Modal de autenticación                     | Renovar sesión                     | `Su sesión expiró. Renueve el token para continuar.`    |
| Forbidden         | Rol/tenant no autorizado           | Mensaje sin datos sensibles                | Cambiar contexto autorizado        | `No tiene permiso para consultar este recurso.`         |
| Evidence pending  | Gate finalizado, zip en cola       | Progreso y polling limitado                | Actualizar                         | `La evidencia se está empaquetando.`                    |
| Evidence invalid  | Hash no coincide                   | Alerta de integridad                       | Regenerar/reportar                 | `La evidencia no superó la verificación SHA-256.`       |

## 5. Reglas de ramificación y feedback

- Después de tres retries fallidos, la UI detiene el polling y muestra `Abrir incidencia`.
- El polling usa intervalos crecientes y se detiene al llegar a `success`, `failed` o `invalid`.
- Los formularios marcan el campo exacto, mantienen valores no sensibles y no limpian toda la pantalla.
- Las acciones de retry son idempotentes mediante `Idempotency-Key`.
- Los mensajes distinguen solución para el usuario de detalles técnicos disponibles en logs.
- Las notificaciones incluyen `X-Correlation-ID` para soporte sin mostrar JWT.

## 6. Ayuda contextual y protección de datos

| Elemento | Ayuda contextual                                   | Protección                                               |
|----------|----------------------------------------------------|----------------------------------------------------------|
| Tenant   | `Contexto que limita qué runs puede consultar.`    | ID parcial; selector solo con tenants autorizados.       |
| Commit   | `Versión exacta que ejecutará el runner.`          | Solo lectura desde el PR cuando procede.                 |
| p95      | `Percentil 95 de latencia de evaluación del Gate.` | Métrica agregada, sin payload clínico.                   |
| SHA-256  | `Huella para comprobar que el archivo no cambió.`  | Se verifica contra backend, no contra valor del cliente. |
| JWT      | `Credencial de sesión necesaria para la API.`      | Nunca se imprime ni se muestra completo.                 |

## 7. Accesibilidad y criterios de aceptación UX

- Todos los estados usan texto y color, nunca solo color.
- El foco de teclado sigue el orden: contexto, filtros, acción principal, resultados.
- Mensajes de error tienen `role="alert"`; cambios de estado tienen `aria-live="polite"`.
- El contraste mínimo cumple WCAG AA y los badges tienen texto legible.
- El usuario puede recuperar red, timeout y token expirado sin perder contexto ni duplicar una ejecución.
