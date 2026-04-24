# Changelog — mod_videoconnect

## Versión 1.1.0 — 2026-04-24

### Objetivo
Actualización de compatibilidad con Moodle 5.1 y 5.2. Sin cambios funcionales ni refactor.

---

### Cambios en el código

#### `lib.php`

- **Eliminado `defined('MOODLE_INTERNAL')` duplicado.**  
  Existía dos veces en el mismo fichero. En Moodle 5.x con debug activo genera un warning innecesario.

- **Añadido soporte para `FEATURE_MOD_PURPOSE`.**  
  Introducido en Moodle 4.4 y requerido en Moodle 5.x. Sin él, el selector de actividades no categoriza correctamente el módulo y genera warnings en modo debug. Valor asignado: `MOD_PURPOSE_CONTENT`.

#### `classes/vimeo.php`

- **Corregido acceso a clave de array inexistente.**  
  `if ($result['error'])` → `if (!empty($result['error']))`.  
  En PHP 8.x, acceder a una clave no existente lanza un warning. Cuando la petición a Vimeo es exitosa, la clave `error` no existe en la respuesta, causando un error en modo debug.

#### `version.php`

- **Corregido comentario incorrecto en `$plugin->requires`.**  
  El valor `2023111300` corresponde a Moodle 4.3, no a Moodle 5.1 como indicaba el comentario. El valor numérico era correcto, solo el comentario era erróneo.

---

### Dependencias

| Paquete | Versión anterior | Versión nueva |
|---|---|---|
| `vimeo/vimeo-api` | 3.0.10 | 3.0.12 |

Actualización mediante `composer update vimeo/vimeo-api`. Incluye bugfixes de la librería oficial.

---

### Pruebas realizadas (Moodle 5.2)

| Prueba | Resultado |
|---|---|
| Instalación sin errores | ✅ |
| Crear actividad con ID de Vimeo | ✅ Vídeo embebido correctamente |
| Subir vídeo local + tarea cron | ✅ Subido a Vimeo via API |
| Ver vídeo subido desde Moodle | ✅ |
| Modo debug activo — errores en pantalla | ✅ Ninguno |

### Pendiente

- Repetir todas las pruebas en Moodle 5.1.
