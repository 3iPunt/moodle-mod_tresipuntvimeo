# Changelog — mod_videoconnect

## Versión 2.1.0 — 2026-07-29 (`2026072900`)

### Objetivo
Plugin agnóstico del proveedor de vídeo, rediseño de la interfaz y
saneamiento integral tras auditoría.

### ⚠️ Cambios de requisitos

- **Mínimo soportado: Moodle 4.5** (antes 4.3). El markup es dual
  Bootstrap 4/5.
- El rol **profesor sin permiso de edición** deja de tener
  `mod/videoconnect:addinstance` por defecto (definición heterodoxa;
  ahora `editingteacher` y `manager`, clonando de
  `moodle/course:manageactivities`).

### Novedades

- **Conector de proveedor de vídeo**: el núcleo trabaja contra
  `provider_interface`; Vimeo es el conector incluido (`vimeo_provider`).
  Añadir otro proveedor = escribir un conector y registrarlo en
  `provider_manager`. Nueva columna `videoconnect.provider`: cada actividad
  queda sellada con el proveedor que la creó y se reproduce siempre con su
  conector.
- **Rediseño de la vista, el panel y el detalle** (design system de la
  familia Tresipunt): tarjeta de estado 16:9 responsive en la vista con
  mensajes por rol (el detalle técnico solo para gestores), panel con
  cabecera co-brandada, KPIs-filtro, filtros colapsables con autocompletado
  de curso (corregido: no funcionaba la búsqueda) y tabla renovada; detalle
  con tarjeta de diagnóstico que traduce el estado a acción.
- **«Mostrar el vídeo en la página del curso»** (`displayinline`): cada
  actividad decide si se embebe en el curso (comportamiento histórico,
  default) o solo dentro de la actividad. También se respeta el ajuste
  estándar «Muestra la descripción» (`FEATURE_SHOW_DESCRIPTION`).
- **«Probar conexión»** en los ajustes: valida credenciales contra el
  proveedor y avisa de los scopes que faltan para el flujo completo.
- **Suite PHPUnit** (36 tests) y CI alineada con el mínimo soportado.
- **README nuevo** (inglés + español) con la lista real de scopes
  (`public, private, upload, edit, interact`) y toda la funcionalidad
  actual; catalán completado (145 claves en los 3 idiomas).

### Correcciones

- El mensaje de error HTTP del proveedor ya no se muestra a los alumnos.
- La tarea de subida: un fallo de BD ya no marca la fila como «actividad
  eliminada», los errores inesperados quedan registrados y en estado
  reintentable (nunca filas huérfanas en «subiendo»), y las filas atascadas
  de runs muertos se rescatan automáticamente (umbral 6 h).
- Guarda del token vacío en modo PAT (antes `TypeError` fatal en el cron) y
  excepciones del cliente Vimeo construidas correctamente.
- Al crear una actividad con ID y fichero a la vez, el ID antiguo ya no
  queda embebido hasta que corre el cron.
- Los ficheros temporales se eliminan tras publicar y al borrar la
  actividad; `videoconnect_uploads` gana índices (`status` e
  `instance,id`).
- La página del curso hace 1 consulta por curso (antes 2 por actividad) y
  ya no se reconstruye la caché del curso en cada subida.
- Doble escapado de nombres (`format_string` + Mustache) corregido en
  vista, panel, detalle e índice.
- El backup incluye los campos nuevos (`provider`, `displayinline`); la
  cola de subidas queda fuera a propósito (rutas temporales locales).

### Notas de actualización

- El upgrade `2026072900` añade `videoconnect.provider`,
  `videoconnect.displayinline` y los índices; las filas existentes quedan
  como `vimeo` / visible en curso (comportamiento previo).
- Tras actualizar: purgar cachés. Si se compila JS: `grunt amd`.

---

## Versión 2.0.0 — 2026-06-05 (`2026060502`)

### Objetivo
Actualización mayor de la dependencia de la API de Vimeo.

### Cambios

- **`vimeo/vimeo-api` actualizado de 3.x a 4.0.1**: la 4.x incorpora su
  propio cliente de subida resumible (TUS) y elimina la dependencia
  `ankitpokhrel/tus-php` y todo su árbol (predis, carbon, ramsey/uuid,
  symfony console…): de ~24 paquetes vendorizados a 10.
- `.extlib/vendor/` queda como **único autoload** de terceros, gestionado
  por Composer (`config.vendor-dir`), y `thirdpartylibs.xml` sincronizado
  con el árbol real.
- Sin cambios funcionales para el usuario: los flujos de subida y embebido
  se mantienen; versión mayor por el cambio de la superficie de
  dependencias.

---

## Versión 1.3.0 — 2026-06-05 (`2026060501`)

### Objetivo
Whitelist de embebido opcional y multi-dominio + privacidad completa de los
vídeos subidos (TIPVIDEOC-9).

### Novedades

#### Whitelist opcional y multi-dominio

- **Nuevo setting `usewhitelist`** (activado por defecto): permite elegir si
  los vídeos subidos restringen su embebido a una whitelist de dominios o
  son embebibles en cualquier web. La descripción **avisa explícitamente**
  de la implicación al desactivarlo. Solo afecta a subidas futuras.
- **`whitelist` pasa a multi-dominio**: textarea con un dominio por línea
  (sin protocolo), validado y normalizado al guardar (minúsculas, sin
  duplicados) tanto en la página de settings como en `manage.php`. La tarea
  da de alta **todos** los dominios tras cada subida. Retrocompatible: el
  valor antiguo de un solo dominio sigue siendo válido tal cual.
- Con whitelist activa y sin dominios configurados, la subida queda en
  error de whitelist explícito (antes podía quedar un vídeo irreproducible
  sin rastro).

#### Privacidad completa de los vídeos subidos

- **`privacy.view = 'disable'`**: los vídeos nunca son visibles navegando
  vimeo.com, solo embebidos. Automatiza el paso manual "Hide from Vimeo"
  que el README pedía hacer a mano tras cada subida (gap histórico: los
  vídeos quedaban públicos en vimeo.com).
- **Sin descargas (`download = false`) y sin comentarios
  (`comments = 'nobody'`)** en todos los vídeos subidos.

#### Otros

- El listado del panel se ordena por defecto por **fecha descendente** (lo
  más nuevo primero).
- **Codechecker de Moodle limpio** en todo el plugin (excluido `.extlib/`):
  normalización de finales de línea y newlines finales en 22 archivos
  legacy, líneas largas y `MOODLE_INTERNAL` innecesario en `lib.php`.

---

## Versión 1.2.0 — 2026-06-05 (`2026060500`)

### Objetivo
Panel de control de vídeos para administradores y gestores (TIPVIDEOC-8).

### Novedades

#### Panel de control (`panel.php`)

- **Listado de todas las actividades Video Connect del sitio** con estado
  derivado del vídeo en 5 valores: *Publicado*, *Publicado con incidencia*
  (vídeo subido pero whitelist/carpeta fallida — antes invisible), *Pendiente
  de subir*, *Error de subida* y *Sin vídeo*. Curso y actividad enlazados,
  ID con enlace a Vimeo, mensaje de error truncado visible en la propia fila.
- **Contadores por estado como filtros rápidos clicables** (toggle), con el
  selector de **curso como ámbito de página** (autocompletado AJAX del core,
  auto-aplica al elegir y acota también los contadores).
- **Filtros**: estado, búsqueda por nombre, rango de fechas (desde/hasta) y
  botón de restablecer (conserva el ámbito de curso). Paginación y orden
  server-side (una sola consulta con JOIN a la última subida; verificado
  con cientos de actividades).
- **Detalle de intentos de subida por actividad**: historial completo con
  estados legibles nuevos (redacción de diagnóstico, no las strings de
  alumno), detalle del error HTTP y disponibilidad del fichero temporal.
- **Acciones de gestión**: **Reintentar** (re-encola para el cron; imposible
  duplicar un vídeo publicado — salvaguarda a nivel de modelo) y
  **Descartar** (nunca sobre subidas en curso). Confirmación + `sesskey`;
  si la acción no es posible, notificación con la **causa exacta** (carrera,
  vídeo ya publicado, fichero purgado), nunca página de error.
- **Aviso de cron parado** en la cabecera cuando hay subidas pendientes y la
  tarea no corre desde hace >10 minutos.
- **Auditoría**: eventos `upload_retried` y `upload_discarded` en el log
  estándar de Moodle (quién, cuándo, sobre qué).
- **Acceso**: capability nueva **`mod/videoconnect:managevideos`**
  (`RISK_DATALOSS`, contexto sistema, permitida al arquetipo `manager`),
  separada de `:configure`. Entrada "Panel de control de Video Connect" en
  la carpeta de administración; los gestores sin `moodle/site:config`
  acceden por URL directa.
- Strings de todas las novedades en `en`, `es` y **`ca`** (carpeta nueva,
  solo con las strings del panel).

---

## Versión 1.1.1 — 2026-06-04 (`2026060401`)

### Objetivo
Correcciones de la revisión técnica de TIPVIDEOC-6 y acceso a la configuración para gestores.

### Cambios en el código

#### Formulario de actividad y settings

- **El setting `folderid` acepta ID numérico o URL de carpeta de Vimeo
  pegada** (`vimeo.com/manage/folders/<id>`,
  `vimeo.com/user/<uid>/folder/<id>?...`) y guarda siempre el ID numérico
  normalizado. Aplica en ambas vías: página de settings
  (`mod_videoconnect\admin\setting_folderid`) y `manage.php`
  (validación en `settings_form` + normalización al guardar). Helper común
  `mod_videoconnect\videoconnect::extract_folderid()`. Valores no
  reconocidos se rechazan con error de validación.
- `manage.php`: el token almacenado **se conserva** al guardar con
  `is_authenticated` desmarcado (el campo va deshabilitado y no se envía;
  antes provocaba warning de propiedad indefinida).

- **El campo "ID del vídeo" acepta ID numérico o URL de Vimeo pegada**
  (`vimeo.com/<id>`, `player.vimeo.com/video/<id>`, con o sin
  protocolo/query). Se normaliza al ID numérico al guardar y, si el valor no
  se reconoce, muestra un error de validación claro. Antes el campo era
  `PARAM_INT`: una URL pegada se truncaba en silencio y el vídeo "no
  aparecía" sin explicación. Los vídeos *unlisted* con hash de privacidad
  siguen sin soportarse (el modelo del plugin es whitelist de dominio).
- **Guardar la actividad sin adjuntar fichero ya no inserta filas de "error"**
  (`status = 0`, `STATUS_NOT_FILEPATH`) en `videoconnect_uploads`. Editar
  nombre/intro o usar el modo "ID existente" es un caso normal, no un fallo;
  la tabla dejaba de ser útil como bitácora por el ruido. Las filas antiguas
  con `status = 0` se conservan (siguen renderizando su mensaje).
- `uploads::update` acepta `$mform = null` (creación programática de
  instancias): antes `videoconnect_add_instance` sin formulario provocaba
  `TypeError` en PHP 8.

#### Máquina de estados de subidas

- **Los estados 7 y 9 ahora se persisten de verdad** en
  `videoconnect_uploads.status`:
    - Respuesta de Vimeo sin ID de vídeo → `status = 7`
      (`STATUS_UPLOADING_VIDEOID_MISSING`); antes se guardaba el genérico
      `4` y el detalle solo quedaba en `error_message`. El profesor ve ahora
      el mensaje específico.
    - Error al mover a carpeta → `status = 9`
      (`STATUS_UPLOADING_ERROR_FOLDER`); antes quedaba `5` (completed).
      Sigue siendo **no bloqueante**: el vídeo está subido y se reproduce
      (queda en la raíz de Vimeo).

#### Base de datos

- **Eliminada la columna `videoconnect_uploads.http_status_code`** (paso de
  upgrade `2026060401`): ningún código la escribía nunca — la librería de
  Vimeo no expone el status code HTTP de la subida.
- Eliminada la variable muerta `is_completed` del renderable `view_page`
  (la plantilla no la consumía).

#### Arquitectura de render (MVC) — `2026060402`

- **El contenido embebido en la página del curso se renderiza ahora en el
  hook `videoconnect_cm_info_view`** (al pintar el curso, con `$PAGE` real)
  en lugar de en `videoconnect_get_coursemodule_info` (que corre durante el
  rebuild de la caché del curso — cron, CLI, restore — y renderizaba una
  plantilla + 3 consultas por instancia en cada rebuild).
- **`view_page` es vista pura**: recibe el registro `videoconnect` y la
  última subida por constructor; sin acceso a BD en `classes/output/`
  (regla MVC Tresipunt). Nueva `uploads::get_latest()` como capa de datos.
  ⚠️ Cambio de firma: `new view_page(stdClass $instance, bool $hasname,
  ?stdClass $lastupload)` (antes `int $cmid, bool $hasname`).
- Eliminado `renderer::render_maincontent_form()` (código muerto: `render()`
  resuelve por convención `templatable`).

#### ⚠️ Breaking — tarea renombrada a la convención core

- `classes/tasks/upload_videos_task.php` → **`classes/task/`** (namespace
  `mod_videoconnect\task`). En el upgrade, Moodle re-registra la tarea
  automáticamente; si un admin había personalizado su horario, se
  restablece el default, y cualquier crontab/script con
  `--execute='\mod_videoconnect\tasks\...'` debe actualizarse a `\task\`.

#### Configuración accesible por capability (`mod/videoconnect:configure`)

- **Nueva capability `mod/videoconnect:configure`** (`RISK_CONFIG`, contexto sistema,
  permitida por defecto al arquetipo `manager`).
- **Nueva página `manage.php`** con formulario propio
  (`classes/form/settings_form.php`) que replica los settings del plugin.
  Permite a un gestor configurar las credenciales de Vimeo sin
  `moodle/site:config` (el `settings.php` de un módulo solo se carga para
   administradores del sitio). Acceso de gestores por URL directa.
- **Carpeta "Video Connect" en Administración del sitio** (patrón
  `mod_assign`): `admin_category` bajo Extensiones → Módulos de actividad
  con la settingpage clásica ("Configuración", sección
  `modsettingvideoconnect` intacta) y la página externa "Configuración de
  Video Connect" (→ `manage.php`) dentro. Visible para administradores.

#### Dependencias — corrección importante

- **La actualización de `vimeo/vimeo-api` a 3.0.12 declarada en 1.1.0 nunca
  llegó al árbol distribuido**: el `composer update` se ejecutó con el
  `vendor-dir` por defecto y dejó la versión nueva en `vendor/` mientras el
  código carga `.extlib/vendor/` (que seguía en 3.0.10).
- Fijado `config.vendor-dir = .extlib/vendor` en `composer.json` y
  reinstaladas las dependencias: `.extlib/vendor/` ahora contiene
  **vimeo-api 3.0.12** real.
- Eliminado el árbol `vendor/` duplicado y añadido `/vendor/` a `.gitignore`.
- `thirdpartylibs.xml` sincronizado con lo distribuido: vimeo-api `3.0.12`,
  tus-php `2.3.0` (antes declaraba 3.0.8 y 2.4.0).
- `composer.json`: corregido `type` (`moodle-pluging` → `moodle-plugin`) y
  licencia a formato SPDX (`GPL-3.0-or-later`).

#### Settings

- `client_secret` y `access_token` pasan de `admin_setting_configtext` a
  **`admin_setting_configpasswordunmask`**: dejan de mostrarse en claro en la
  UI de administración. Sin cambio de almacenamiento.
- Corregida la clave del heading de settings (`tresipuntcsvexport/csvsettings`
  → `mod_videoconnect/vimeosettings`, copy-paste de otro plugin).

#### Integración con Vimeo

- **`vimeo/vimeo-api` actualizado a la última estable: 3.0.12 → 4.0.1**
  (el ticket pedía no mantener `^3.0`; `composer.json` pasa a `^4.0`).
  API pública sin cambios (constructor, `clientCredentials`, `setToken`,
  `upload`, excepciones) — cero adaptación de código. La 4.x sustituye
  `ankitpokhrel/tus-php` por un cliente TUS propio ligero: **se eliminan 14
  paquetes** del árbol vendorizado (tus-php, predis, nesbot/carbon,
  ramsey/uuid, symfony console/http-foundation/mime/event-dispatcher,
  brick/math…), quedando 10 (vimeo-api + guzzle 7.11 + PSR). Menos peso y
  menos superficie de seguridad. `thirdpartylibs.xml` sincronizado con el
  árbol completo. Verificado contra la API real (auth PAT + whitelist).

- **Corregido falso error en whitelist/carpeta** (`vimeo::curl_request`):
  Vimeo responde **204 No Content** (cuerpo vacío) en esos `PUT` y el código
  intentaba decodificarlo como JSON → "Syntax error" y `status = 8` aunque
  la operación hubiera funcionado. Ahora el éxito se decide por el código
  HTTP. Verificado con subidas reales.
- **Mensajes de error de la API más claros**: se prefiere el
  `developer_message` de Vimeo, después su `error`, y como último recurso
  `HTTP <código> + extracto del body`.
- **Scopes documentados del flujo completo**: `public`, `private`, `upload`
  (subida), `edit` (whitelist) e `interact` (carpeta). El README solo
  citaba los tres primeros.

#### Tarea programada

- `upload_videos_task` ya **no construye el cliente Vimeo cuando no hay
  subidas pendientes**: se elimina la petición de token a Vimeo cada 2
  minutos en vacío (y el fallo constante de la tarea en entornos sin
  credenciales configuradas).
- Si la inicialización del cliente falla con subidas pendientes, se registra
  un mensaje claro y accionable en el log de la tarea antes de propagar el
  error. Las filas quedan en `STATUS_NOT_EXECUTED` y se reintentan al
  corregir la configuración.

#### Limpieza

- Corregido docblock copy-paste en `db/access.php` ("Tresipunt CSV Export").

---

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
