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

<p align="center"><b>Host your course videos on a private Vimeo account, straight from Moodle.</b></p>

<p align="center"><b>🇬🇧 English</b> · <a href="README.es.md">🇪🇸 Español</a></p>

Video Connect is an activity module that centralises the videos of your
campus on a Vimeo account: teachers upload a file (published to Vimeo in the
background) or paste the ID/URL of an existing video, and the video plays
embedded in the course. Moodle only stores the video reference — no video
files consume your campus storage or bandwidth. It does not modify the
Moodle core nor the theme.

---

## ✨ What it does

- **Two modes in one activity** — upload a local video file, or embed an
  existing Vimeo video by pasting its ID or URL.
- **Background publishing** — uploads are queued and published to Vimeo by a
  scheduled task; teachers never wait for the upload to finish.
- **Privacy by default** — uploaded videos are hidden from vimeo.com,
  comments and downloads are disabled, and embedding can be restricted to
  your campus domains (whitelist).
- **Player on the course page or inside the activity** — each activity can
  embed the player directly on the course page (default) or show the
  standard link and play only inside the activity.
- **Site-wide control panel** — every Video Connect activity with its
  publication state (published, pending, incident, error), filters, upload
  history and retry/discard actions.
- **Settings for managers** — a dedicated settings page lets managers
  configure the plugin without being site administrators.
- **Provider-agnostic by design** — the plugin talks to the video platform
  through a connector; Vimeo is the bundled provider, and new providers can
  be added by writing a new connector, without touching the plugin core.

## ⚙️ How it works

1. The site administrator connects the plugin to a Vimeo API app
   (credentials + scopes) in the plugin settings.
2. When a teacher uploads a video, the activity stores it temporarily and a
   scheduled task (every 2 minutes, requires an active cron) publishes it to
   Vimeo, applies the privacy policy and moves it to the configured folder.
3. While the video is being published, students see a friendly "available
   shortly" card; once published, the embedded player.
4. Videos referenced by ID are embedded directly, with no upload involved.
5. The control panel shows the state of every video of the site and lets
   managers retry or discard failed uploads.

## 📋 Requirements

| Requirement | Version |
|---|---|
| Moodle | 4.5+ (including 5.x — tested up to 5.1) |
| PHP | 8.1+ |
| Vimeo account | Paid plan with domain-level embed privacy (Starter or higher) |
| Vimeo API app | Scopes: `public`, `private`, `upload`, `edit`, `interact` |
| Moodle cron | Active (uploads are processed by a scheduled task) |
| Other plugins | Not required |

> The `upload` scope requires a prior request to Vimeo (approval usually
> takes some hours). Without `edit` the domain whitelist cannot be applied
> and uploaded videos will not play embedded; without `interact` videos
> cannot be moved to a folder.

## 🚀 Installation

1. Copy the code into `mod/videoconnect/` (`public/mod/videoconnect/` on
   Moodle 5.x).
2. Complete the installation from **Site administration › Notifications**
   (or CLI: `php admin/cli/upgrade.php --non-interactive`).
3. Purge the caches (**Site administration › Development › Purge caches**
   or `php admin/cli/purge_caches.php`).
4. Configure the Vimeo credentials (see Settings below).

## 🔧 Settings

In **Site administration › Plugins › Activity modules › Video Connect**:

| Setting | Effect |
|---|---|
| **Client ID / Client Secret** | Credentials of your Vimeo API app |
| **Is authenticated + Personal Access Token** | Use a PAT (required to upload videos) instead of the client credentials grant (playback only) |
| **Scopes** | Scopes requested to Vimeo; the full flow needs `public`, `private`, `upload`, `edit` and `interact` |
| **Restrict embedding (whitelist)** | Only whitelisted domains can embed the uploaded videos; disabled, future uploads are publicly embeddable |
| **Whitelist domains** | Domains allowed to embed, one per line, without protocol |
| **Folder ID** | Vimeo folder where uploaded videos are organised (ID or pasted folder URL) |

Two extra pages hang from the same menu:

- **Video Connect settings** (`/mod/videoconnect/manage.php`) — the same
  settings, editable by users with the `mod/videoconnect:configure`
  capability (managers) without requiring site administration access.
- **Control panel** (`/mod/videoconnect/panel.php`) — for users with the
  `mod/videoconnect:managevideos` capability: site-wide video states,
  filters, upload history and retry/discard actions.

## 🗑️ Uninstallation

Uninstalling removes the plugin tables (activities and upload log) and any
pending temporary files. The videos already published on Vimeo are **not**
deleted: they remain in your Vimeo account.

## 🛠️ Development

The Vimeo PHP client is vendored under `.extlib/vendor/` and managed with
Composer (`composer.json` sets `vendor-dir` accordingly). JavaScript modules
live in `amd/src/` (build with `grunt amd`).

**Provider connectors.** The plugin core (upload task, forms, views) works
exclusively against `classes/provider/provider_interface.php`. Vimeo is the
bundled connector (`vimeo_provider`). To support another video platform,
implement the interface in a new connector class and register it in
`provider_manager`; each activity is stamped with the provider that created
it, so existing videos keep playing through their original connector even if
the site switches provider.

```bash
# Unit tests
vendor/bin/phpunit --testsuite mod_videoconnect_testsuite

# Acceptance tests
vendor/bin/behat --tags @mod_videoconnect
```

## 📄 License

[GNU GPL v3 or later](https://www.gnu.org/copyleft/gpl.html) — 2021-2026 [Tresipunt](https://tresipunt.com) (contacte@tresipunt.com)

---

<p align="center">
  <a href="https://tresipunt.com"><img src="pix/tresipunt_logo.png" alt="Tresipunt" width="160"></a>
</p>
