# Politicas Base del Sistema (V1)

## 1) Proposito
Este documento define las politicas funcionales y de UX que deben repetirse en todas las configuraciones y modulos del sistema.

Objetivo: mantener una base consistente, profesional, predecible y facil de mantener.

## 2) Politicas Globales de UX

### 2.1 Notificaciones
- Canal principal para eventos temporales: notificacion flotante.
- Aplica a eventos temporales de exito, error, advertencia e informacion.
- Toda notificacion flotante temporal debe incluir:
  - boton `X` para cierre manual,
  - countdown visible,
  - autocierre al finalizar el countdown,
  - estilo visual segun tipo: `success`, `error`, `warning`, `info`.
- No duplicar el mismo evento en notificacion flotante y alerta incrustada al mismo tiempo.
- Las alertas o mensajes incrustados solo se permiten si hay justificacion clara de contexto, por ejemplo:
  - validaciones contextuales dentro de formularios o modales,
  - estados persistentes de una seccion,
  - mensajes de "sin permisos",
  - logs tecnicos,
  - resultados detallados dentro de un modal,
  - informacion fija que no sea notificacion temporal.
- En nuevas implementaciones no crear bloques incrustados de exito/error como canal principal.
- Si se requiere mensaje incrustado, debe tratarse como excepcion justificada por contexto.
- Se mantiene prohibido usar `alert()`, `confirm()` y `prompt()`.
- No usar callbacks fragiles (por ejemplo, cierre de modal) como disparador de acciones criticas.

### 2.2 Carga de procesos
- Si una accion tarda, mostrar indicador de carga (spinner, barra o animacion).
- El indicador debe activarse al iniciar y ocultarse al finalizar (exito o error).

### 2.3 Subida de archivos
- Toda subida debe mostrar barra de progreso en tiempo real.
- Al terminar, mostrar mensaje de exito con datos utiles (nombre, tamano, ruta, etc.).

### 2.4 Tablas y paginacion
- Por defecto, todas las tablas deben paginar en 10 elementos por pagina.
- La paginacion debe mostrar solo paginas cercanas (ventana corta), no todas las paginas disponibles.

### 2.5 Resolucion de conflictos de orden
- Si se asigna un orden ya ocupado, debe mostrarse advertencia estricta.
- Debe existir opcion explicita para "tomar el orden".
- El elemento que pierde ese orden queda sin orden asignado (valor de "sin orden").
- El sistema debe informar claramente que el elemento perdio su orden por reasignacion.

### 2.6 Dialogos nativos prohibidos
- No usar `alert`, `confirm` ni `prompt` nativos de JavaScript para interacciones de usuario.
- Usar modales personalizados y/o sistema de notificaciones del proyecto.

### 2.7 Textos tecnicos largos en tablas administrativas
- Las tablas administrativas no deben mostrar en bruto valores tecnicos largos cuando deforman la tabla.
- Aplica a campos como rutas, URLs, paths, logs, tokens, nombres fisicos de archivo y textos tecnicos extensos.
- Patron recomendado:
  - mostrar texto corto visible en celda,
  - mantener el valor completo disponible mediante `title`, `data-*`, modal de detalle o vista expandida,
  - incluir boton de copiar cuando el valor sea util para tareas administrativas,
  - al copiar, mostrar notificacion del sistema,
  - implementar fallback cuando falle Clipboard API.
- Se mantiene prohibido usar `alert`, `confirm` y `prompt`.

### 2.8 Botones de accion en tablas administrativas
- Esta politica aplica solo a botones dentro de tablas administrativas.
- No aplica a botones de formularios, modales, filtros, cards, login ni otras zonas del sistema.
- En tablas administrativas, los botones de accion no deben mostrar texto visible por defecto.
- Deben usar solo icono.
- Deben estar apilados verticalmente (uno encima de otro).
- No deben colocarse en fila horizontal.
- Deben mantener color semantico segun la accion.
- Deben incluir `title` y `aria-label` obligatorios.
- El texto visible solo se permite si el usuario lo pide expresamente o si la accion es excepcional y se aprueba.
- Si no existe icono claro para una accion, Codex debe detenerse y pedir decision antes de inventar texto.

Mapa inicial de acciones en tablas:
- Copiar: `fas fa-copy` + `btn-outline-secondary` + sin texto visible.
- Descargar: `fas fa-download` + `btn-success` + sin texto visible.
- Eliminar: `fas fa-trash` + `btn-danger` + sin texto visible.
- Editar: `fas fa-edit` + `btn-primary` + sin texto visible.
- Activar: `fas fa-toggle-on` + `btn-success` + sin texto visible.
- Desactivar: `fas fa-toggle-off` + `btn-danger` + sin texto visible.
- Reset clave: `fas fa-key` + `btn-dark` + sin texto visible.
- Quitar: `fas fa-times` + `btn-outline-danger` + sin texto visible.

Regla de vocabulario UI:
- En interfaz visible (botones, titulos, labels y mensajes de usuario), usar "Desactivar" y "Desactivado".
- Evitar "Inactivar" e "Inactivo" en UI visible.
- Mantener nombres tecnicos internos existentes si cambiarlos puede romper logica, compatibilidad, claves, codigos, base de datos o trazabilidad.
- No cambiar columnas, valores internos, codigos, permisos ni keys tecnicas solo por vocabulario.

## 3) Estandar de Eliminacion de Archivos

## 3.1 Como SI se hace (correcto)
1. El usuario solicita eliminar desde la tabla.
2. Se abre modal de confirmacion personalizado.
3. Al confirmar:
   - se bloquea boton de confirmar para evitar doble click,
   - se muestra estado de carga,
   - se ejecuta request al endpoint de eliminacion.
4. Backend elimina de forma coherente:
   - valida ID,
   - elimina vinculos relacionados,
   - elimina registro principal,
   - elimina archivo fisico en disco,
   - si falla, devuelve error tecnico (sin exito falso).
5. Frontend muestra resultado:
   - exito: notificacion y refresco parcial de tabla,
   - error: notificacion + log tecnico copiable.

## 3.2 Como NO se hace (incorrecto)
- No depender de callbacks fragiles del cierre del modal para recien disparar la accion critica.
- No dejar multiples listeners globales que compitan entre si para el mismo boton.
- No mostrar "exito" parcial si no se elimino fisicamente el archivo.
- No ocultar detalles tecnicos cuando una operacion falla en entorno de admin.

## 4) Checklist Minimo Antes de Cerrar un Cambio
- [ ] La accion deja evidencia visible para el usuario (notificacion/estado).
- [ ] No hay recarga completa innecesaria de la misma pagina.
- [ ] El flujo funciona en exito y error.
- [ ] No se usan dialogos nativos JS.
- [ ] El modulo mantiene paginacion 10 + paginas cercanas.
- [ ] Errores criticos muestran log tecnico copiable (en admin).

## 5) Leccion Operativa Importante
En acciones criticas (eliminar, guardar, actualizar estado), evitar suposiciones de "evento implicito" o "flujo automatico". Siempre preferir flujo explicito y determinista:
- un boton,
- un handler claro,
- una llamada clara,
- un resultado claro.

---
Este archivo es la base inicial y se debe ampliar con nuevas politicas en futuras iteraciones.
