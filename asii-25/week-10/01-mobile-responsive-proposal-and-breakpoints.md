# ASII-25 — Propuesta responsive y breakpoints móviles

## 1. Identificación

| Campo              | Valor                                                       |
|--------------------|-------------------------------------------------------------|
| Estudiante         | Cindy Maytté Ruano Calderón                                 |
| Módulo oficial     | ASII-25 — QA, pruebas E2E, CI y guía de despliegue final    |
| Actividad adaptada | Gestión de ejecuciones automatizadas y calidad de versiones |
| Rol / dominio      | QA / Aseguramiento de Calidad y CI/CD                       |
| Semana             | 10                                                          |
| Tema               | Diseño para movilidad                                       |
| Viewports objetivo | 320–430 px, con adaptación progresiva a tablet y escritorio |

## 2. Estrategia mobile-first

La pantalla móvil prioriza una decisión operativa: saber si el run está activo, si el Quality Gate pasó y qué acción segura sigue. Los datos secundarios se revelan bajo demanda para reducir scroll, carga cognitiva y consumo de red.

### Breakpoints

```css
/* Base: 320–430 px, una columna y acciones de pulgar */
:root {
  --touch-target: 44px;
  --page-gutter: 16px;
  --bottom-nav-height: 64px;
}

@media (min-width: 431px) and (max-width: 768px) {
  /* sm: dos regiones ligeras, cards con más metadatos */
  .run-card__metrics { grid-template-columns: repeat(2, 1fr); }
  .filter-panel { display: grid; grid-template-columns: 1fr 1fr; }
}

@media (min-width: 769px) {
  /* md+: tabla, navegación lateral y paneles paralelos */
  .run-list { display: table; }
  .mobile-bottom-nav { display: none; }
  .desktop-sidebar { display: block; }
}
```

| Rango           | Composición                             | Regla de contenido                                                 |
|-----------------|-----------------------------------------|--------------------------------------------------------------------|
| `xs` 320–430 px | Una columna, cards, navegación inferior | Solo estado, run ID corto, suite, Gate y acción primaria visibles. |
| `sm` 431–768 px | Grid de dos columnas en métricas        | Mostrar commit y timestamp; metadatos HTTP en acordeón.            |
| `md` 769 px+    | Tabla y sidebar                         | Recuperar comparación de columnas y filtros persistentes.          |

## 3. Patrones adaptados

- Las tablas de ejecuciones se convierten en cards verticales con `run_id`, estado, suite, Gate y evidencia como primera línea.
- El modal de planificación se transforma en pantalla completa o bottom sheet; nunca obliga a usar un modal estrecho.
- La navegación pasa a barra inferior con `Runs`, `Planificar`, `Evidencia` y `Más`; el menú secundario usa hamburguesa.
- Los filtros densos se agrupan en un drawer y muestran chips activos arriba.
- Los botones principales ocupan el ancho disponible y tienen mínimo `44x44 px`.
- El hash se muestra truncado con `Copiar hash`; detalles completos bajo acordeón.
- Logs usan scroll interno horizontal y vertical sin desplazar toda la página.

## 4. Pantalla móvil 1 — Dashboard móvil de Executions

```text
+--------------------------------+
| QA     tenant-7f2a••   [menu]  |
| [Runs] [Gate] [Evidencia]      |
|--------------------------------|
| EJECUCIONES       [Filtrar]    |
| [PASS] 8e12••  E2E auth        |
| Gate aprobado | Evidencia lista|
| commit a1b2c3d   hace 2 min    |
|--------------------------------|
| [RUNNING] 7ca9••  E2E all      |
| Gate pendiente | Evidencia cola|
| commit f8e7d6c   actualizando  |
|--------------------------------|
| Runs     Planificar  Evidencia |
+--------------------------------+
```

- Visible: estado, suite, Gate, evidencia, commit abreviado y actualización.
- Colapsable: branch, duración completa, headers HTTP y correlation ID.
- Acción táctil: tocar card abre detalle; `Filtrar` abre drawer.
- Empty state: `No hay ejecuciones para este tenant` + `Planificar ejecución`.

## 5. Pantalla móvil 2 — Formulario móvil de planificación

```text
+--------------------------------+
| < Planificar ejecución         |
| Tenant tenant-7f2a•• (fijo)    |
| Commit *                       |
| [a1b2c3d4e5f6____________]     |
| Suites *                       |
| [x] Auth  [x] Admissions       |
| Umbral p95 [2] s               |
| Cobertura mínima [80] %        |
|                                |
| [Validar y ejecutar]           |
+--------------------------------+
```

- Una columna, labels persistentes y teclado móvil apropiado.
- Cada checkbox y botón mide al menos `44x44 px`.
- El tenant no es editable; se muestra enmascarado.
- Submit deshabilitado hasta completar commit y una suite.
- Confirmación táctil antes de crear el run; retry usa `Idempotency-Key`.

## 6. Pantalla móvil 3 — Visor móvil de evidencias

```text
+--------------------------------+
| < Run 8e12••     [PASS]        |
| 92% cobertura | 0 críticos     |
| p95 1.42 s                     |
| Evidencia: READY               |
| e2e-results.tar.gz             |
| 14.2 MB  SHA-256 9f2a...c81e   |
| [Copiar hash] [Descargar]      |
|--------------------------------|
| LOGS                           |
| +----------------------------+ |
| | runner> suite auth PASS   |  | <- scroll
| | gate> decision approved   |  |
| +----------------------------+ |
| [Ver auditoría]                |
+--------------------------------+
```

- `pending` deshabilita descarga pero mantiene actualización.
- `ready` habilita descarga temporal autorizada.
- `invalid` muestra causa y `Regenerar evidencia`.
- `Copiar hash` da feedback `Hash copiado` sin revelar tokens.

## 7. Pantalla móvil 4 — Quality Gate Status

```text
+--------------------------------+
| < Quality Gate 8e12••          |
|                                |
| p95 evaluación                 |
|       1.42 s / 2 s     PASS    |
| cobertura 92%          PASS    |
| fallos críticos 0      PASS    |
|                                |
| DECISIÓN: PASS                 |
| Evidencia: empaquetando        |
| [Reintentar] [Confirmar]       |
+--------------------------------+
```

- Barras/radiales incluyen valor numérico, unidad y texto; no dependen del color.
- `Confirmar trazabilidad` solo está disponible para QA Lead.
- Developer ve métricas y evidencia; CI Runner consume el mismo estado por API.
- Botones están en la zona inferior segura del pulgar y separados por al menos 8 px.

## 8. Navegación y seguridad móvil

- Barra inferior fija, con `padding-bottom` igual a su altura para no ocultar contenido.
- El menú no contiene JWT; el token vive en almacenamiento seguro del cliente nativo o cookie protegida según plataforma.
- `tenant_id` y `run_id` se muestran parcialmente; la API sigue siendo la autoridad.
- Pull-to-refresh no repite un `POST`; solo consulta estados idempotentes.
- El foco y `aria-live="polite"` se conservan en viewport pequeño.
- Rotación, zoom y teclado virtual no deben ocultar el botón primario.
