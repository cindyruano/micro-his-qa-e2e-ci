# 02 - Alcance de QA del Módulo ASII-25

## Objetivo

Definir qué se valida en el módulo de QA: la aplicación Laravel HIS, su API multi-tenant, sus modelos clínicos y el proceso automatizado de pruebas. El alcance separa la autenticación actualmente expuesta de los módulos clínicos modelados que aún requieren controladores y rutas.

## Dentro del alcance

| Área bajo prueba       | Componentes del proyecto                                                      | Estado                                       | Prioridad |
|------------------------|-------------------------------------------------------------------------------|----------------------------------------------|-----------|
| Tenant y autenticación | routes/api.php, TenantMiddleware, AuthController, JWT y User.                 | Rutas implementadas.                         | Alta      |
| RBAC                   | Roles Admin, Médico, Enfermera, TecnicoLab, Recepcionista; Spatie Permission. | Roles sembrados; gestión de roles pendiente. | Alta      |
| Pacientes y admisiones | Patient, Admission, Bed, Ward, Doctor, citas y migraciones.                   | Modelos y BD; API pendiente.                 | Alta      |
| Expediente clínico     | MedicalRecord, SoapNote, Allergy, VitalSign, Prescription y diagnósticos.     | Modelos y BD; API pendiente.                 | Crítica   |
| Laboratorio            | LabOrder, LabResult, muestras, pruebas, estados y alertas críticas.           | Modelos y BD; API pendiente.                 | Crítica   |
| Calidad del pipeline   | Migraciones, seeders, pruebas, reportes y protección de secretos.             | Debe automatizarse en CI.                    | Alta      |

El [diagrama de contexto](./diagramas/imagenes/alcance-contexto.svg) muestra estos límites: la aplicación Laravel y sus pruebas están dentro del alcance; mensajería, observabilidad y la plataforma CI/CD son dependencias externas. No se incluye comercio ni pagos.

## Tipos de prueba

| Tipo         | Qué valida                                                        | Herramienta o comando                                                    | Gate                                           |
|--------------|-------------------------------------------------------------------|--------------------------------------------------------------------------|------------------------------------------------|
| Unitaria     | Reglas, validadores, policies, claims y transiciones de estado    | PHPUnit; php artisan test --testsuite=Unit                               | Sin fallos; cobertura crítica objetivo >= 85 % |
| Integración  | Middleware, controladores, BD, relaciones, transacciones y tenant | Laravel TestCase + RefreshDatabase; php artisan test --testsuite=Feature | Contrato HTTP correcto y sin datos parciales   |
| Contrato API | Payloads, campos requeridos y códigos HTTP                        | PHPUnit y esquemas JSON cuando estén disponibles.                        | Ninguna ruta publicada rompe el contrato       |
| E2E          | Flujo de usuario desde cliente web hasta API                      | Cypress o Playwright.                                                    | Happy path y excepciones del caso habilitado   |
| Estática     | Formato y problemas bloqueantes                                   | Laravel Pint y análisis configurado.                                     | Cero errores bloqueantes                       |

## Entornos

| Entorno      | Uso QA                           | Reglas                                                      |
|--------------|----------------------------------|-------------------------------------------------------------|
| Local        | Desarrollo y pruebas rápidas     | Sin credenciales productivas                                |
| CI           | Pull requests y ramas protegidas | BD efímera, `.env.testing`, fixtures deterministas y dobles |
| Staging / QA | E2E cercano a producción         | Datos sintéticos y servicios sandbox                        |
| Producción   | Promoción controlada             | Solo después de CI y Staging aprobados                      |

## Fuera del alcance

- Pruebas de estrés, carga extrema y capacidad masiva.
- DAST exhaustivo, pentesting manual y certificación legal.
- Aplicaciones móviles nativas.
- Migración masiva de históricos.
- Alta disponibilidad, failover y recuperación ante desastres.
- Integraciones reales de correo, SMS, push o pagos mientras no sean parte de la implementación bajo prueba.

## Criterios de entrada y salida

### Entrada

- Migraciones y seeders disponibles para una base limpia.
- Runtime PHP 8.2+ y Laravel 12, según composer.json.
- .env.testing y secretos configurados fuera del repositorio.
- Fixtures sintéticos, repetibles y aislados por tenant.

### Salida

- Las pruebas de la etapa pasan sin reintentos ocultos.
- Se verifican 400, 401, 403, 404, 422 y respuestas 2xx según cada escenario.
- No hay acceso entre tenants ni exposición de JWT o datos clínicos.
- Se publican reportes JUnit, cobertura y evidencias E2E.

## Referencias

- [Diagrama de alcance y contexto (SVG)](./diagramas/imagenes/alcance-contexto.svg)
- [Fuente PlantUML](./diagramas/alcance-contexto.puml)
- [Actores](./01-actores.md)
- [Matriz de trazabilidad](./04-matriz-trazabilidad.md)
