# Semana 1 - Diseño QA del HIS

## Propósito

Esta semana define la base funcional del módulo ASII-25 para *Quality Assurance*, pruebas de integración, pruebas End-to-End (E2E) y CI/CD del Sistema Hospitalario Integrado (HIS). La documentación vincula actores, alcance, casos de uso, código Laravel, diagramas PlantUML y criterios de aceptación.

## Estado del sistema bajo prueba

- `Aplicación:` Laravel 12, PHP 8.2+.
- `Multi-tenancy:` X-Tenant-ID mediante TenantMiddleware.
- `Autenticación:` JWT en AuthController y guard api.
- `Roles sembrados:` Admin, Médico, Enfermera, TecnicoLab, Recepcionista.
- `API disponible actualmente:` registro, login, consulta de sesión, refresh y logout.
- `Dominio persistido:` pacientes, admisiones, camas, expediente clínico y laboratorio.
- `Módulos clínicos pendientes de rutas y controladores:` CU-02 a CU-05.

## Documentos

|  # | Documento                                                | Contenido                                                      |
|----|----------------------------------------------------------|----------------------------------------------------------------|
| 01 | [Actores del Módulo QA](./01-actores.md)                 | Actores, roles, responsabilidades y participación en pruebas   |
| 02 | [Alcance de QA](./02-alcance.md)                         | Superficies bajo prueba, tipos de prueba, entornos y gates     |
| 03 | [Casos de Uso QA](./03-casos-de-uso.md)                  | CU-01 implementado y CU-02 a CU-05 pendientes, con excepciones |
| 04 | [Matriz de Trazabilidad QA](./04-matriz-trazabilidad.md) | Relación entre casos, código, pruebas, evidencia y diagramas   |

## Diagramas

Los SVG renderizados se almacenan en diagramas/imagenes/ y sus fuentes editables en diagramas/.

| Diagrama           | SVG                                                  | PlantUML                                           |
|--------------------|------------------------------------------------------|----------------------------------------------------|
| Actores            | [Ver SVG](./diagramas/imagenes/actores.svg)          | [Editar fuente](./diagramas/actores.puml)          |
| Alcance y contexto | [Ver SVG](./diagramas/imagenes/alcance-contexto.svg) | [Editar fuente](./diagramas/alcance-contexto.puml) |
| Casos de uso       | [Ver SVG](./diagramas/imagenes/caso-uso.svg)         | [Editar fuente](./diagramas/caso-uso.puml)         |
| Secuencia CU-01    | [Ver SVG](./diagramas/imagenes/secuencia.svg)        | [Editar fuente](./diagramas/secuencia.puml)        |

## Flujo de trabajo QA

1. Preparar una base de datos de testing con migraciones y seeders deterministas.
2. Ejecutar calidad estática y pruebas unitarias.
3. Ejecutar integración sobre middleware tenant, JWT, roles y persistencia.
4. Ejecutar contrato API y E2E para los casos que tengan rutas funcionales.
5. Publicar JUnit, cobertura y evidencias; bloquear el merge ante fallos críticos.

## Convenciones

- `CU-XX:` identificador de caso de uso.
- `A-XX:` identificador de actor.
- `Unit:` prueba unitaria.
- `Integration:` prueba de integración.
- `E2E:` prueba de extremo a extremo.
- `CI:` integración continua y gates automatizados.
- `Pendiente:` existe dominio o requisito, pero no hay ruta funcional verificable.

## Siguiente fase

- Definir estructura de suites, fixtures y datos por tenant.
- Implementar pruebas Feature para el contrato actual de autenticación.
- Preparar el pipeline CI y los artefactos de evidencia.
- Habilitar progresivamente CU-02 a CU-05 cuando se publiquen sus controladores y rutas.
