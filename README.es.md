<p align="center">
  <img src="pix/icon.svg" alt="" width="72">
</p>

<h1 align="center">Video Connect</h1>

<p align="center">
  <img src="https://img.shields.io/badge/version-2.1.0-informational" alt="Version">
  <a href="https://moodle.org"><img src="https://img.shields.io/badge/Moodle-4.5%2B-orange?logo=moodle" alt="Moodle"></a>
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/License-GPL--3.0-green" alt="License">
  <a href="https://tresipunt.com"><img src="https://img.shields.io/badge/made%20by-Tresipunt-F84015" alt="Made by Tresipunt"></a>
</p>

<p align="center"><b>Aloja los vídeos de tus cursos en una cuenta privada de Vimeo, directamente desde Moodle.</b></p>

<p align="center"><a href="README.md">🇬🇧 English</a> · <b>🇪🇸 Español</b></p>

Video Connect es un módulo de actividad que centraliza los vídeos del campus
en una cuenta de Vimeo: el profesorado sube un fichero (que se publica en
Vimeo en segundo plano) o pega el ID/URL de un vídeo existente, y el vídeo se
reproduce embebido en el curso. Moodle solo guarda la referencia del vídeo —
ningún fichero de vídeo consume el almacenamiento ni el ancho de banda del
campus. No modifica el núcleo de Moodle ni el tema.

---

## ✨ Qué hace

- **Dos modos en una actividad** — subir un fichero de vídeo local, o
  embeber un vídeo existente de Vimeo pegando su ID o URL.
- **Publicación en segundo plano** — las subidas se encolan y una tarea
  programada las publica en Vimeo; el profesorado nunca espera a que la
  subida termine.
- **Privacidad por defecto** — los vídeos subidos quedan ocultos de
  vimeo.com, sin comentarios ni descargas, y el embebido puede restringirse
  a los dominios del campus (whitelist).
- **Reproductor en la página del curso o dentro de la actividad** — cada
  actividad puede embeber el reproductor directamente en la página del curso
  (por defecto) o mostrar el enlace estándar y reproducir solo dentro.
- **Panel de control del sitio** — todas las actividades Video Connect con
  su estado de publicación (publicado, pendiente, incidencia, error),
  filtros, historial de subidas y acciones de reintentar/descartar.
- **Configuración para gestores** — una página de ajustes propia permite a
  los gestores configurar el plugin sin ser administradores del sitio.
- **Agnóstico del proveedor por diseño** — el plugin habla con la plataforma
  de vídeo a través de un conector; Vimeo es el proveedor incluido, y pueden
  añadirse otros proveedores escribiendo un conector nuevo, sin tocar el
  núcleo del plugin.

## ⚙️ Cómo funciona

1. El administrador conecta el plugin con una app de la API de Vimeo
   (credenciales + scopes) en los ajustes del plugin.
2. Cuando un profesor sube un vídeo, la actividad lo guarda temporalmente y
   una tarea programada (cada 2 minutos, requiere cron activo) lo publica en
   Vimeo, aplica la política de privacidad y lo mueve a la carpeta
   configurada.
3. Mientras el vídeo se publica, el alumnado ve una tarjeta de «disponible
   en breve»; una vez publicado, el reproductor embebido.
4. Los vídeos referenciados por ID se embeben directamente, sin subida.
5. El panel de control muestra el estado de todos los vídeos del sitio y
   permite reintentar o descartar subidas fallidas.

## 📋 Requisitos

| Requisito | Versión |
|---|---|
| Moodle | 4.5+ (incluida la 5.x — probado hasta 5.1) |
| PHP | 8.1+ |
| Cuenta de Vimeo | Plan de pago con privacidad de embebido por dominio (Starter o superior) |
| App de la API de Vimeo | Scopes: `public`, `private`, `upload`, `edit`, `interact` |
| Cron de Moodle | Activo (las subidas las procesa una tarea programada) |
| Otros plugins | No requiere |

> El scope `upload` requiere una solicitud previa a Vimeo (la aprobación
> suele tardar unas horas). Sin `edit` no se puede aplicar la whitelist de
> dominios y los vídeos subidos no se reproducirán embebidos; sin
> `interact` los vídeos no pueden moverse a carpeta.

## 🚀 Instalación

1. Copiar el código en `mod/videoconnect/` (`public/mod/videoconnect/` en
   Moodle 5.x).
2. Completar la instalación desde **Administración del sitio ›
   Notificaciones** (o por CLI: `php admin/cli/upgrade.php
   --non-interactive`).
3. Purgar las cachés (**Administración del sitio › Desarrollo › Purgar
   cachés** o `php admin/cli/purge_caches.php`).
4. Configurar las credenciales de Vimeo (ver Ajustes).

## 🔧 Ajustes

En **Administración del sitio › Extensiones › Módulos de actividad › Video
Connect**:

| Ajuste | Efecto |
|---|---|
| **Client ID / Client Secret** | Credenciales de tu app de la API de Vimeo |
| **Utiliza autenticación + Personal Access Token** | Usar un PAT (imprescindible para subir vídeos) en lugar del client credentials grant (solo reproducción) |
| **Scopes** | Scopes solicitados a Vimeo; el flujo completo necesita `public`, `private`, `upload`, `edit` e `interact` |
| **Restringir el embebido (whitelist)** | Solo los dominios de la whitelist pueden embeber los vídeos subidos; desactivado, las subidas futuras son embebibles públicamente |
| **Dominios de la whitelist** | Dominios autorizados a embeber, uno por línea, sin protocolo |
| **Folder ID** | Carpeta de Vimeo donde se organizan los vídeos subidos (ID o URL de carpeta pegada) |

Del mismo menú cuelgan dos páginas más:

- **Configuración de Video Connect** (`/mod/videoconnect/manage.php`) — los
  mismos ajustes, editables por usuarios con la capability
  `mod/videoconnect:configure` (gestores) sin acceso de administración.
- **Panel de control** (`/mod/videoconnect/panel.php`) — para usuarios con
  la capability `mod/videoconnect:managevideos`: estados de todos los
  vídeos, filtros, historial de subidas y acciones de reintentar/descartar.

## 🗑️ Desinstalación

Al desinstalar se eliminan las tablas del plugin (actividades y bitácora de
subidas) y los ficheros temporales pendientes. Los vídeos ya publicados en
Vimeo **no** se borran: permanecen en tu cuenta de Vimeo.

## 🛠️ Desarrollo

El cliente PHP de Vimeo va vendorizado en `.extlib/vendor/` y se gestiona
con Composer (`composer.json` fija el `vendor-dir`). Los módulos JavaScript
viven en `amd/src/` (build con `grunt amd`).

**Conectores de proveedor.** El núcleo del plugin (tarea de subida,
formularios, vistas) trabaja exclusivamente contra
`classes/provider/provider_interface.php`. Vimeo es el conector incluido
(`vimeo_provider`). Para soportar otra plataforma de vídeo, implementa la
interfaz en una clase conectora nueva y regístrala en `provider_manager`;
cada actividad queda sellada con el proveedor que la creó, de modo que los
vídeos existentes siguen reproduciéndose con su conector original aunque el
sitio cambie de proveedor.

```bash
# Tests unitarios
vendor/bin/phpunit --testsuite mod_videoconnect_testsuite

# Tests de aceptación
vendor/bin/behat --tags @mod_videoconnect
```

## 📄 Licencia

[GNU GPL v3 or later](https://www.gnu.org/copyleft/gpl.html) — 2021-2026 [Tresipunt](https://tresipunt.com) (contacte@tresipunt.com)

---

<p align="center">
  <a href="https://tresipunt.com"><img src="pix/tresipunt_logo.png" alt="Tresipunt" width="160"></a>
</p>
