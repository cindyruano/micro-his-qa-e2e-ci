# ASII-25 — Wireframes y reglas de interacción

## 1. Identificación

**Estudiante:** Cindy Maytté Ruano Calderón  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Semana:** 8

Los wireframes son de baja fidelidad y están orientados a validar jerarquía, estados y decisiones. No fijan una librería visual ni amplían el alcance del módulo.

## 2. Wireframe 1 — Panel Principal de Ejecuciones QA

```text
+------------------------------------------------------------------+
| Micro-HIS QA | Tenant: tenant-7f2a•• | Usuario: QA Lead          |
|------------------------------------------------------------------|
| EJECUCIONES QA                         [Planificar ejecución]    |
| Filtros: [Tenant] [Branch] [Commit] [Estado] [Buscar]            |
|------------------------------------------------------------------|
| Run ID        Commit       Suite       Gate       Evidencia      |
| 8e12••        a1b2c3d      E2E auth    PASS       READY          |
| 7ca9••        f8e7d6c      E2E all     RUNNING    PENDING        |
| 6bd0••        12aa90f      E2E EMR     FAIL       READY          |
| ---------------------------------------------------------------- |
| [1] [2] [3]       Última actualización: hace 8 s                 |
+------------------------------------------------------------------+
```

**Anotaciones:**

- A1: El selector de tenant solo lista contextos autorizados y muestra el ID enmascarado.
- A2: Los estados tienen texto, icono y color: `PASS`, `RUNNING`, `FAIL`.
- A3: `Planificar ejecución` es la acción primaria para QA Lead; Developer ve solo consulta.
- A4: La tabla no muestra JWT ni rutas internas de almacenamiento.

**Interacción:** filtros no destruyen resultados; al seleccionar una fila se abre el detalle; durante loading se conserva la tabla anterior con indicador de actualización.

## 3. Wireframe 2 — Modal de Planificación E2E

```text
+---------------------------------------------------------------+
| Planificar ejecución                                       X  |
| Tenant autorizado: [tenant-7f2a••] (solo lectura)             |
| Commit *:          [a1b2c3d4e5f6________________]             |
| Branch:            [feature/qa__________________]             |
| Suites *:          [x] auth  [x] admissions  [ ] laboratory   |
| Quality Gate:      cobertura >= [80]  p95 < [2] s             |
|                                                          (?)  |
| [Cancelar]                         [Validar y ejecutar]       |
+---------------------------------------------------------------+
```

**Validaciones:**

- `commit`: obligatorio, 7–64 caracteres, sin espacios.
- `suites`: al menos una; no se aceptan suites desconocidas.
- p95: número positivo y menor que el timeout máximo de plataforma.
- El tenant se toma del contexto, no de un campo editable.
- Antes de enviar, se muestra confirmación con commit, suite y tenant parcial.

**Feedback:** errores inline junto al campo; éxito `Ejecución creada con ID 8e12••`; timeout conserva la selección y ofrece retry idempotente.

## 4. Wireframe 3 — Detalle y visor de evidencias

```text
+----------------------------------------------------------------+
| Run 8e12•• | tenant-7f2a•• | commit a1b2c3d | Gate: PASS       |
|----------------------------------------------------------------|
| Métricas: cobertura 92% | fallos críticos 0 | p95 1.42 s       |
| Evidencia: READY                                               |
| Archivo                         Tamaño     SHA-256             |
| e2e-results.tar.gz              14.2 MB    9f2a...c81e [Verif] |
| logs.json                       180 KB     1ab0...77dd [Verif] |
|----------------------------------------------------------------|
| [Descargar paquete] [Copiar hash] [Abrir auditoría]            |
+----------------------------------------------------------------+
```

**Reglas:**

- `pending`: muestra progreso y desactiva descarga.
- `ready`: permite descarga mediante URL autorizada y temporal.
- `invalid`: oculta descarga, explica fallo y ofrece `Regenerar evidencia`.
- `Verificar` solicita al backend comprobar el hash; el navegador no decide integridad.
- `Copiar hash` requiere acción explícita y muestra confirmación no sensible.

## 5. Wireframe 4 — Evaluación del Quality Gate

```text
+----------------------------------------------------------------+
| Quality Gate - Run 8e12••                                      |
|----------------------------------------------------------------|
| Cobertura                 92%       [PASS]                     |
| Fallos críticos            0        [PASS]                     |
| p95 evaluación           1.42 s     [PASS] (< 2 s)             |
| Aislamiento tenant       validado   [PASS]                     |
| -------------------------------------------------------------- |
| DECISIÓN: PASS                                                 |
| Evidencia: empaquetando (no bloquea la decisión)               |
| [Reintentar evaluación] [Confirmar trazabilidad]               |
+----------------------------------------------------------------+
```

**Reglas:**

- `PASS` requiere todos los checks obligatorios; ningún indicador aislado permite aprobar.
- `FAIL` muestra razones accionables, no solo un color rojo.
- `Reintentar` usa backoff y `Idempotency-Key`; tras tres fallos ofrece abrir incidencia.
- La confirmación manual queda disponible solo para QA Lead.
- Un timeout no se muestra como `PASS` ni borra la decisión anterior.

## 6. Wireframe 5 — Estado vacío, carga y error recuperable

```text
+----------------------------------------------------------------+
| Ejecuciones del tenant tenant-7f2a••                           |
|----------------------------------------------------------------|
| [Estado vacío]                                                 |
| No hay ejecuciones para este filtro.                           |
| [Planificar ejecución] [Limpiar filtros]                       |
| -------------------------------------------------------------- |
| [Error recuperable]                                            |
| No se pudo conectar. Correlation ID: c-91••                    |
| [Reintentar] [Abrir diagnóstico]                               |
+----------------------------------------------------------------+
```

Variantes de la misma vista:

- **Loading:** skeleton en filas y `Cargando ejecuciones…` con `aria-live="polite"`.
- **Token expirado:** modal `Su sesión expiró` con `Renovar sesión`; no se repite POST automáticamente.
- **403:** `No tiene permiso para este tenant`; no se revelan existencia ni métricas.
- **Timeout:** conserva filtros y formulario; retry limitado, sin duplicar runs.

## 7. Reglas generales de interacción

### Validación y estados

1. Validar cliente para feedback inmediato y servidor para seguridad definitiva.
2. Mostrar el campo inválido, la causa y cómo corregirlo.
3. Deshabilitar solo la acción en curso, no todo el dashboard.
4. Mantener el último estado válido mientras llega una respuesta nueva.
5. Separar `gate_passed` de `evidence_ready`.

### Ayuda contextual

- Tooltip de p95: `Percentil 95 de latencia de evaluación; el umbral estándar es menor que 2 s.`
- Tooltip de tenant: `Define el límite de datos que puede consultar esta sesión.`
- Tooltip de SHA-256: `Huella criptográfica del archivo final; no es un token de acceso.`
- Mensajes técnicos detallados van a auditoría; la UI muestra una acción comprensible.

### Protección de datos

- JWT siempre enmascarado y nunca incluido en logs de frontend.
- `tenant_id`, `run_id` y commit parcialmente visibles según el rol.
- URLs de descarga temporales y emitidas por backend autorizado.
- Hash SHA-256 visible como evidencia de integridad, nunca como secreto.
- No mostrar datos clínicos en logs, capturas ni mensajes de error.

### Accesibilidad

- Contraste WCAG AA y texto además de color.
- Navegación por teclado con foco visible.
- `role="alert"` para errores y `aria-live="polite"` para cambios de estado.
- Etiquetas asociadas a cada campo y orden lógico de tabulación.
- Botones con verbos claros: `Reintentar`, `Verificar`, `Descargar`, `Renovar sesión`.
