# Declaración de uso de IA

Se utilizó GitHub Copilot dentro de VS Code como asistente de desarrollo.

## Propósito

Auditar el Laravel existente e implementar de forma incremental el quality gate transversal de ASII-25, sus pruebas, CI, evidencia y guía de despliegue.

## Prompt relevante resumido

Se solicitó auditar primero el repositorio real, respetar la arquitectura por capas, no duplicar entidades clínicas, entregar un comando/pipeline reproducible, persistir evidencia con commit y ambiente, demostrar éxito y fallo inducido, documentar UML y completar validaciones reales.

## Decisiones revisadas

Se aceptó la propuesta de usar un repositorio orientado al caso de uso, fake para aplicación y Eloquent para infraestructura. Se corrigió el registro de comandos al detectar que Laravel 12 exige un arreglo en `withCommands`. Se documentó que el repositorio no tiene conexiones CENTRAL/HOSPITAL PostgreSQL separadas y que la prueba PostgreSQL queda opt-in local.

## Validación

La solución se validó con sintaxis PHP, Pint sobre los archivos del módulo, migración limpia, pruebas focales, ejecución exitosa del gate y fallo inducido con código 1. La deuda de formato preexistente de otros módulos no fue reformateada.
