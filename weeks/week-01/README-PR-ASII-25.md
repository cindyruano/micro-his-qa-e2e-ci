# PR de módulo ASII

## Resumen

<!-- Explica qué capacidad del módulo entrega este PR y por qué es necesaria para el HIS. -->

Este PR corresponde al módulo ASII-25: QA, pruebas E2E, CI y guía de despliegue.

## Módulo asignado

- Módulo ASII: `ASII-25 — QA, pruebas E2E, CI y guía de despliegue`
- Estudiante: Cindy Maytte Ruano Calderón
- GitHub: `cindyruano`
- Rama: `feature/asii-25-qa-pruebas-e2e-ci-y-guia-de-despliegue-fin-cindyruano`
- Destino del PR: `develop`
- Issue relacionado: Closes #

## Alcance

### Incluido

- Actores, roles y responsabilidades del proceso de QA.
- Alcance, tipos de prueba, entornos y criterios de aceptación.
- Casos de uso y matriz de trazabilidad.
- Pruebas de integración, contrato API, E2E y CI/CD.
- Guía de despliegue y controles para tenant, permisos y datos clínicos sensibles.

### Fuera de alcance

- Implementación de funcionalidades clínicas que todavía no tienen rutas ni controladores.
- Pruebas de carga extrema, pentesting exhaustivo y certificación legal.
- Aplicaciones móviles nativas, migración masiva de históricos y alta disponibilidad.
- Integraciones reales de correo, SMS, push o pagos no incluidas en la implementación bajo prueba.

## Evidencia obligatoria

- [ ] El PR viene desde un worktree creado desde `origin/develop`.
- [ ] No trabajé sobre `main`.
- [ ] No mezclé cambios de otro módulo.
- [ ] El issue del módulo está vinculado.
- [ ] Incluí RF/RNF y criterios de aceptación.
- [ ] Incluí evidencia de diseño: UML, C4, componentes, capas o flujo UX según corresponda.
- [ ] Documenté endpoints, payloads, respuestas, errores y permisos si el módulo expone API.
- [ ] Adjunté capturas o demo si el módulo tiene UI.
- [ ] Ejecuté validaciones locales y pegué los resultados abajo.
- [ ] Revisé roles, permisos, tenant y datos clínicos sensibles cuando aplica.
- [ ] Dejé notas de integración con otros módulos o riesgos pendientes.

## Validación ejecutada

Ejecutar desde la raíz del proyecto, con PHP 8.2+, Composer, Node.js y las dependencias instaladas.
Pegar la salida real de cada comando en el resultado correspondiente.

### Preparación

```powershell
composer install
npm install
Copy-Item .env.example .env -ErrorAction SilentlyContinue
php artisan key:generate
```

Resultado esperado:

```text
Dependencias instaladas correctamente, archivo .env disponible y APP_KEY generado.
```

### Backend y pruebas automatizadas

```powershell
composer validate --strict
php artisan migrate:fresh --seed --force
php artisan test
```

Resultado esperado:

```text
composer.json is valid.
Las migraciones y seeders finalizan sin errores.
Todas las pruebas PHPUnit pasan: PASS.
```

### Calidad de código PHP

```powershell
vendor/bin/pint --test
```

Resultado esperado:

```text
El código cumple el formato configurado y Pint finaliza con código 0.
```

### Contrato y rutas API

```powershell
php artisan route:list --path=api
```

Resultado esperado:

```text
Se muestran únicamente las rutas API publicadas, con método, URI, middleware y controlador.
Verificar especialmente TenantMiddleware, autenticación JWT y permisos por rol.
```

### Frontend

```powershell
npm run build
```

Resultado esperado:

```text
vite build finaliza correctamente y genera los artefactos en public/build.
```

### Evidencia registrada

| Comando | Resultado observado | Estado |
|---|---|---|
| `composer validate --strict` | `./composer.json is valid` | Aprobado |
| `php artisan migrate:fresh --seed --force` | Bloqueado: falta `vendor/autoload.php` | Pendiente tras `composer install` |
| `php artisan test` | Bloqueado: falta `vendor/autoload.php` | Pendiente tras `composer install` |
| `vendor/bin/pint --test` | Bloqueado: no existe `vendor/bin/pint` | Pendiente tras `composer install` |
| `php artisan route:list --path=api` | Bloqueado: falta `vendor/autoload.php` | Pendiente tras `composer install` |
| `npm run build` | Bloqueado: `vite` no se reconoce; falta `node_modules` | Pendiente tras `npm install` |

En esta ejecución, las pruebas de Laravel y el build frontend no pudieron ejecutarse
porque las dependencias locales aún no están instaladas. Después de completar la
preparación, reemplazar los estados pendientes por la salida real de cada comando.

### Pruebas manuales mínimas de API

Con el servidor iniciado mediante `php artisan serve`, validar con datos sintéticos:

```powershell
php artisan serve
```

- Login con `POST /api/v1/auth/login` y `X-Tenant-ID`: debe devolver `2xx` y un JWT.
- Consulta autenticada con `GET /api/v1/auth/me`: debe devolver `2xx` con el usuario del tenant.
- Petición sin JWT: debe devolver `401`.
- Petición sin `X-Tenant-ID`: debe ser rechazada por el middleware de tenant.
- Petición con rol insuficiente: debe devolver `403`.
- Intento de consultar datos de otro tenant: no debe devolver datos cross-tenant.

Registrar aquí los resultados manuales:

```text
# endpoint / escenario:
# resultado observado:
# código HTTP:
# evidencia:
```

## Contrato API si aplica

| Método | Ruta | Permiso/Rol | Descripción |
|---|---|---|---|
|  |  |  |  |

## Evidencia visual si aplica

<!-- Adjunta capturas, enlaces a mockups o demo. -->

## Riesgos y pendientes

- Los casos de uso clínicos CU-02 a CU-05 requieren rutas y controladores para contar con cobertura funcional completa.
- La ejecución E2E en CI depende de una base de datos efímera, fixtures deterministas y datos sintéticos.

## Checklist final del estudiante

- [ ] El PR es pequeño y revisable.
- [ ] El código compila o las limitaciones están explicadas.
- [ ] La documentación del módulo está actualizada.
- [ ] No hay credenciales, tokens ni datos sensibles reales en el PR.
- [ ] Solicité revisión solo después de completar la evidencia mínima.
