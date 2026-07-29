<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Upgrade steps of mod_videoconnect.
 *
 * @package     mod_videoconnect
 * @category    upgrade
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute mod_videoconnect upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_videoconnect_upgrade($oldversion): bool {
    global $DB;

    // Automatically generated Moodle v4.1.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.2.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.3.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.4.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2026060401) {
        // Drop videoconnect_uploads.http_status_code: it was never written
        // by any code path (the Vimeo client library does not expose the
        // HTTP status code of the upload response).
        $dbman = $DB->get_manager();
        $table = new xmldb_table('videoconnect_uploads');
        $field = new xmldb_field('http_status_code');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026060401, 'videoconnect');
    }

    if ($oldversion < 2026072900) {
        $dbman = $DB->get_manager();

        // Sello de proveedor por instancia (2.1.0): las actividades se crean
        // con el proveedor activo del sitio y se reproducen siempre con el
        // conector que las creó. Las filas históricas son todas de Vimeo.
        $table = new xmldb_table('videoconnect');
        $field = new xmldb_field('provider', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'vimeo', 'idvideo');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Visualización por instancia: embebido en la página del curso
        // (comportamiento histórico, default) o solo dentro de la actividad.
        $field = new xmldb_field('displayinline', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'provider');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Faltaban índices en videoconnect_uploads: la tarea de cron
        // consulta por status cada 2 minutos y el panel resuelve la última
        // subida por instancia con MAX(id).
        $table = new xmldb_table('videoconnect_uploads');
        $index = new xmldb_index('status', XMLDB_INDEX_NOTUNIQUE, ['status']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('instance-id', XMLDB_INDEX_NOTUNIQUE, ['instance', 'id']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_mod_savepoint(true, 2026072900, 'videoconnect');
    }

    // Everything has succeeded to here. Return true.
    return true;
}
