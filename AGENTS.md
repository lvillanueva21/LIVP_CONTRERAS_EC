# AGENTS.md — LSISTEMAS

Este repositorio corresponde al sistema PHP/MySQL LSISTEMAS.

Este archivo contiene instrucciones obligatorias para Codex y cualquier agente IA que analice, modifique o genere código dentro del proyecto.

No se está empezando desde cero. El sistema ya tiene una base funcional de seguridad, administración, páginas lógicas, branding, gestión de archivos y perfil de usuario.

---

## 1. Contexto técnico del proyecto

Stack principal:

- PHP
- MySQL
- PDO
- JavaScript
- Bootstrap 4
- AdminLTE 3
- phpMyAdmin
- Servidor compartido Hostinger
- Repositorio GitHub con despliegue mediante webhook hacia Hostinger

El proyecto usa recursos propios de AdminLTE 3, normalmente ubicados en carpetas como:

- `adminltev32/`
- `plugins/`
- `dist/`

El sistema debe mantener alta compatibilidad con entornos comunes de PHP/MySQL en hosting compartido.

Evitar código que dependa de versiones demasiado nuevas, experimentales o poco comunes de PHP, MySQL o JavaScript.

Usar soluciones populares, estables, mantenibles y probadas.

---

## 2. Estado actual del sistema

El sistema ya cuenta con:

- Login robusto.
- Seguridad de login.
- Sesiones endurecidas.
- Control de acceso por roles y permisos.
- Usuarios V1.
- Roles V1.
- Permisos V1.
- Páginas lógicas.
- Sidebar dinámico.
- Estilos/branding.
- Gestión centralizada de archivos.
- Mi perfil.
- Subida real de archivos.
- Vista previa, descarga y eliminación de archivos.
- Vinculación de archivos a usos visuales.
- Logo, favicon y carrusel dinámicos.
- Foto de perfil y portada de usuario.
- Bloque visual de usuario en sidebar.

No reconstruir estas bases salvo que el usuario lo pida expresamente.

---

## 3. Forma obligatoria de trabajo

Trabajar siempre por micro-pasos.

Cada tarea debe ser:

- Pequeña.
- Controlada.
- Comprobable.
- Compatible con lo ya construido.
- Fácil de probar manualmente desde navegador.

Antes de modificar código:

1. Revisar el código existente.
2. Revisar helpers, patrones y estructuras ya creadas.
3. Verificar nombres reales de tablas, columnas, rutas y archivos.
4. Revisar si existe una fuente real de base de datos o dump SQL disponible.
5. Detectar si existe bloqueo real.
6. Implementar solo lo pedido.

No mezclar fases.

No implementar módulos futuros si no fueron solicitados.

No abrir más capas de las necesarias.

---

## 4. Filosofía de implementación

Codex debe actuar como implementador guiado, no como arquitecto libre.

Si hay duda entre hacer más o hacer exactamente lo pedido, hacer exactamente lo pedido.

Si hay duda entre inventar o revisar, revisar.

Si hay duda entre avanzar con riesgo o reportar bloqueo, reportar bloqueo.

Priorizar:

- Seguridad.
- Compatibilidad.
- Mantenibilidad.
- Coherencia visual.
- Coherencia funcional.
- Bajo riesgo.
- Cambios mínimos.

---

## 5. Archivo de políticas del sistema

Antes de modificar UX, formularios, tablas, imágenes, archivos, notificaciones, loading, paginación, modales o flujos visuales, leer y respetar:

`reglas md/01_politicas_base_sistema.md`

Ese archivo debe contener el encabezado:

`# Politicas Base del Sistema (V1)`

Ese archivo contiene políticas largas del sistema.

Este `AGENTS.md` contiene reglas operativas obligatorias para Codex.

Si el archivo de políticas no existe, no es legible o contradice el prompt actual, detenerse y reportar el problema antes de implementar.

---

## 6. Reglas sobre base de datos y SQL

Usar PDO.

No usar `mysqli` en nuevas implementaciones.

No crear archivos `.sql`.

No ejecutar migraciones automáticas.

No asumir que el servidor ejecutará scripts SQL.

No asumir acceso directo a consola SQL del servidor.

El usuario ejecuta manualmente los querys en phpMyAdmin.

Todo query necesario debe entregarse en la respuesta final, separado en:

- QUERYS DEL SISTEMA
- QUERYS DE TESTEO DEL SISTEMA

No inventar:

- Tablas.
- Columnas.
- Índices.
- Relaciones.
- Datos base.
- Estados.
- Permisos.

Si existe una copia real de la base de datos o dump SQL, debe usarse como fuente de verdad antes de proponer cambios.

---

## 7. Reglas de rutas y despliegue

No usar URLs relativas a la raíz como solución por defecto.

Evitar rutas tipo:

```php
/assets/...
/sistema/...
/modulos/...
```

Usar rutas relativas al archivo o helpers existentes del sistema.

Motivo: el sistema debe poder migrarse entre estructuras como:

- `ventas.ejemplo.pe/`
- `ejemplo.pe/`
- `ejemplo.pe/ventas/`

Tener presente que muchas interfaces y módulos pueden estar cargados mediante `include`.

No asumir que el archivo actual se ejecuta siempre desde la raíz del proyecto.

---

## 8. Reglas sobre textos, tildes, Ñ y emojis

No borrar textos existentes si no forman parte directa del cambio solicitado.

No eliminar tildes.

No reemplazar `Ñ`, `ñ`, tildes, emojis ni caracteres especiales por mojibake.

No “limpiar” textos que parezcan raros.

No cambiar nombres visibles, etiquetas, mensajes, emojis o textos del sistema salvo necesidad estricta del alcance.

Si un archivo tiene problemas de codificación, reportarlo antes de hacer cambios masivos.

---

## 9. Reglas de alcance

No tocar más archivos de los necesarios.

No hacer refactors grandes si no fueron pedidos.

No cambiar arquitectura existente sin aprobación.

No romper ni modificar sin necesidad:

- Login.
- Sesiones.
- CSRF.
- Roles.
- Permisos.
- Usuarios.
- Seguridad.
- Sidebar.
- Branding.
- Gestión de archivos.
- Helpers centrales.

Si una tarea exige tocar muchas capas, detenerse y reportar bloqueo.

---

## 10. Consistencia visual y funcional

El sistema debe mantener una línea visual coherente basada en AdminLTE 3 y Bootstrap 4.

No hacer cambios cosméticos no solicitados.

No cambiar colores, textos, iconos, emojis, posiciones, tamaños o estilos si no forman parte directa del alcance.

No rediseñar pantallas ya aceptadas.

Si una página similar usa notificación flotante, otra página similar no debe resolver el mismo caso con un bloque visual distinto sin autorización.

Si una funcionalidad ya tiene una forma definida de almacenar imágenes, archivos, estados o configuraciones, las nuevas partes del sistema deben seguir ese mismo patrón salvo que el usuario indique un cambio.

El usuario decide cuándo se personaliza un comportamiento visual o funcional.

---

## 11. Prohibiciones técnicas y UX

Está prohibido usar diálogos nativos de JavaScript:

- `alert()`
- `confirm()`
- `prompt()`

Usar modales, notificaciones o componentes personalizados del sistema.

No crear lógica duplicada si ya existe helper reutilizable.

No depender de eventos frágiles para acciones críticas.

No usar callbacks implícitos o cierres de modal como disparador principal de una acción crítica.

En acciones críticas debe existir flujo explícito:

- Botón claro.
- Handler claro.
- Request claro.
- Respuesta clara.
- Estado visual claro.

---

## 12. Políticas UX base

Cuando aplique, respetar estos patrones:

- Notificación flotante con botón X.
- Countdown visible cuando corresponda.
- Notificación incrustada solo si no duplica innecesariamente la flotante.
- Loading visible en procesos que demoren.
- Barra de progreso real en subidas.
- Paginación por defecto de 10 registros por página.
- Paginación con ventana corta de páginas cercanas.
- Modales personalizados para confirmaciones.
- Mensajes claros en errores de servidor, permisos o validaciones.
- Log técnico copiable en errores críticos de administración cuando corresponda.

Las reglas completas están en:

`reglas md/01_politicas_base_sistema.md`

---

## 13. Gestión de archivos e imágenes

El sistema usa gestión centralizada de archivos.

Antes de modificar archivos, imágenes, logos, favicon, carrusel, foto de perfil o portada, revisar la lógica existente relacionada con:

- `lsis_archivos`
- `lsis_archivos_vinculos`
- Helpers de archivos existentes.
- Carpeta física de almacenamiento.
- Vínculos de uso visual.

No crear una lógica paralela de archivos si ya existe una estructura central.

Las imágenes deben aceptar formatos web razonables y seguros.

No imponer límites arbitrarios desde la aplicación si el servidor ya tiene sus propios límites.

Si el servidor rechaza un archivo por tamaño o configuración, mostrar mensaje claro y útil.

Cuando una imagen de usuario se reemplaza por otra, la anterior no debe quedar acumulada inútilmente si el flujo definido exige reemplazo real.

---

## 14. Servidor, GitHub y caché

El proyecto puede desplegarse en Hostinger mediante webhook desde GitHub.

Tener presente que puede existir caché del servidor o del navegador.

No asumir que un cambio no funciona solo porque no se ve de inmediato.

Cuando aplique, indicar pruebas manuales considerando posible caché.

No depender de configuraciones avanzadas de servidor que normalmente no están disponibles en hosting compartido.

---

## 15. Regla de bloqueo real

Si una tarea requiere:

- Tocar demasiados archivos.
- Cambiar arquitectura.
- Crear muchas tablas.
- Mezclar módulos.
- Romper el alcance pedido.
- Modificar seguridad central.
- Hacer supuestos sobre la base de datos.
- Cambiar pantallas ya aceptadas.
- Usar rutas globales frágiles.
- Reescribir flujos ya funcionales.

Detenerse y responder con este formato:

```text
BLOQUEO DETECTADO

POR QUÉ OCURRE:
...

ARCHIVOS QUE OBLIGARÍA A TOCAR:
...

RIESGO:
...

PROPUESTA DE RECORTE:
...

APROBACIÓN REQUERIDA:
...
```

No implementar hasta que el usuario apruebe.

---

## 16. Respuesta final obligatoria

Al terminar cualquier implementación, responder siempre con:

```text
RESUMEN DE IMPLEMENTACIÓN

ARCHIVOS MODIFICADOS

ARCHIVOS CREADOS

ARCHIVOS ELIMINADOS

QUERYS DEL SISTEMA

QUERYS DE TESTEO DEL SISTEMA

CÓMO PROBAR MANUALMENTE

CASOS POSITIVOS

CASOS NEGATIVOS

RIESGOS O PENDIENTES

SUGERENCIA DE COMMIT

AGENTS.md
```

En `ARCHIVOS MODIFICADOS`, `ARCHIVOS CREADOS` y `ARCHIVOS ELIMINADOS`, listar rutas en texto plano.

Si no hubo querys, indicar:

```text
No se requieren querys.
```

Si no se crearon archivos, indicar:

```text
No se crearon archivos.
```

Si no se eliminaron archivos, indicar:

```text
No se eliminaron archivos.
```

En `SUGERENCIA DE COMMIT`, proponer un nombre corto y claro para el commit.

Ejemplos:

```text
feat: agregar empresa v1
fix: corregir eliminacion de archivos
refactor: reutilizar helper de imagenes
```

---

## 17. Revisión del AGENTS.md al final de cada iteración

Al final de cada iteración, Codex debe evaluar si alguna regla nueva definida por el usuario debe agregarse, modificarse o mantenerse fuera de `AGENTS.md`.

En la sección final `AGENTS.md`, responder una de estas opciones:

```text
AGENTS.md:
No requiere cambios.
```

o:

```text
AGENTS.md:
Se recomienda actualizarlo con esta regla:
...
```

o:

```text
AGENTS.md:
Fue actualizado en esta iteración.
```

No inflar el archivo con historia, detalles temporales o reglas que solo aplican a una tarea puntual.

Agregar al `AGENTS.md` únicamente reglas generales que deban respetarse en futuras iteraciones.

---

## 18. Estilo de explicación esperado

Responder claro y directo.

No vender como correcto algo que falló.

Si algo salió mal, explicar:

- Qué salió mal.
- Por qué pasó.
- Qué se corrigió.
- Cómo probarlo.

Evitar explicaciones largas innecesarias.

Priorizar instrucciones prácticas y pruebas manuales visibles desde navegador.

---

## 19. Regla principal de decisión

Si el prompt del usuario contradice una regla general de este archivo, seguir el prompt del usuario solo si no rompe seguridad, datos o arquitectura crítica.

Si hay riesgo, reportar bloqueo.

Si una regla aplica solo a una fase puntual, no convertirla automáticamente en regla global.

Si una implementación puede hacerse con menos cambios, hacerla con menos cambios.
