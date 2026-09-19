# ASII-25 — Mapa de navegación y especificación del prototipo

## 1. Identificación

| Campo              | Valor                                                       |
|--------------------|-------------------------------------------------------------|
| Estudiante         | Cindy Maytté Ruano Calderón                                 |
| Módulo oficial     | ASII-25 — QA, pruebas E2E, CI y guía de despliegue final    |
| Actividad adaptada | Gestión de ejecuciones automatizadas y calidad de versiones |
| Rol / dominio      | QA / Aseguramiento de Calidad y CI/CD                       |
| Semana             | 11                                                          |
| Tema               | Mejores prácticas para diseño móvil/web y prototipado       |

## 2. Alcance del prototipo

El prototipo simula el flujo principal del Micro-HIS QA: planificación, ejecución E2E, evaluación de Quality Gate, evidencia SHA-256 y despliegue aprobado. También permite activar el error de hash/fallo E2E y comprobar el bloqueo y retry.

Archivos navegables:

```text
prototype/
├── index.html
├── styles.css
└── app.js
```

## 3. Mapa de navegación

```text
Dashboard #dashboard
  ├─ Planificar ejecución #plan
  │    └─ Validar y crear run -> Ejecución #run
  ├─ Seleccionar run -> Quality Gate #gate
  │    ├─ PASS -> Aprobar despliegue #deploy
  │    └─ FAIL -> Reintentar -> run/gate con Idempotency-Key
  └─ Navegación inferior móvil: Runs / Planificar / Gate
```

| Pantalla            | Ruta/hash    | Rol principal                 | Entrada                 | Salida                       |
|---------------------|--------------|-------------------------------|-------------------------|------------------------------|
| Dashboard           | `#dashboard` | QA Lead, Developer            | tenant, filtros         | seleccionar run o planificar |
| Planificación       | `#plan`      | QA Lead                       | commit, suite, umbrales | run `queued/running`         |
| Ejecución           | `#run`       | QA Lead, CI Runner            | run y suite             | resultados E2E               |
| Quality Gate        | `#gate`      | QA Lead, Developer, CI Runner | métricas y evidencia    | `PASS` o `FAIL`              |
| Despliegue aprobado | `#deploy`    | QA Lead / CI Runner           | Gate aprobado           | PR listo para merge          |

## 4. Modos Desktop y Móvil

- **Desktop >= 1024 px:** header completo, tarjetas métricas en tres columnas, botones en línea y navegación por enlaces/hash.
- **Móvil 320–430 px:** una columna, cards verticales, barra inferior fija y acciones de mínimo `44x44 px`.
- El mismo estado se expresa con texto, badge y mensaje; no depende solo del color.
- En móvil los metadatos secundarios se colapsan conceptualmente y la acción primaria queda al alcance del pulgar.

## 5. Componentes navegables

| Componente                        | Comportamiento                                                       |
|-----------------------------------|----------------------------------------------------------------------|
| Selector de rol                   | Simula QA Lead, Developer y CI Runner sin cambiar autorización real. |
| Selector de escenario             | Alterna camino feliz y error crítico.                                |
| `QARunnerUI`                      | Dashboard, formulario, cards y navegación principal.                 |
| Modal/formulario de planificación | Validación nativa de commit y suites; conserva entrada ante errores. |
| Visor de logs                     | Scroll interno, estado del runner y mensajes anunciados.             |
| Quality Gate                      | Métricas p95, cobertura, fallos, evidencia y acciones.               |
| Toast / live region               | Feedback no bloqueante con `aria-live="polite"`.                     |
| Estados                           | `PASS`, `FAIL`, `RUNNING`, `READY`, `INVALID`, `PENDING`.            |

## 6. Ejecución

Abrir `prototype/index.html` directamente en el navegador. No requiere servidor ni dependencias.

1. Pulsar `Planificar ejecución`.
2. Confirmar el formulario y pulsar `Avanzar simulación`.
3. Revisar el Gate y pulsar `Aprobar despliegue`.
4. Volver al inicio y seleccionar `Error crítico`.
5. Repetir el flujo para observar `FAIL`, `INVALID`, bloqueo y `Reintentar`.
6. Redimensionar la ventana entre desktop y 320–430 px para comprobar el responsive.

Para un servidor local opcional:

```bash
php -S localhost:8080 -t docs/asii-25/week-11/prototype
```

Luego abrir `http://localhost:8080`.

## 7. Reglas de accesibilidad y datos

- HTML semántico, labels explícitos y focus visible.
- Botones y enlaces accionables por teclado; el formulario usa validación nativa.
- `aria-live="polite"` para cambios de estado y `role="status"` para toast.
- Tenant y commit abreviados; no se muestran JWT ni secretos.
- El hash es una evidencia visible, no un token de acceso.
- Las acciones críticas se bloquean cuando el Gate falla y el retry no duplica el run.
