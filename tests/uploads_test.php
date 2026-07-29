<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Uploads model tests.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect;

use advanced_testcase;
use mod_videoconnect\event\upload_discarded;
use mod_videoconnect\event\upload_retried;
use moodle_exception;
use stdClass;

/**
 * Tests for the uploads model: derived states, retry/discard guards and
 * temp file housekeeping.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_videoconnect\uploads
 */
final class uploads_test extends advanced_testcase {
    /**
     * Creates a course, an activity and an optional upload row.
     *
     * @param string $idvideo Video ID of the instance ('' = none).
     * @param int|null $status Upload row status, null for no row.
     * @param string $filepath Upload row temp file path.
     * @return stdClass instance record (with cmid) and uploadid when created.
     */
    protected function create_activity(string $idvideo = '', ?int $status = null, string $filepath = ''): stdClass {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        $module = $this->getDataGenerator()->create_module('videoconnect', [
            'course' => $course->id,
            'idvideo' => $idvideo,
        ]);
        $instance = $DB->get_record('videoconnect', ['id' => $module->id], '*', MUST_EXIST);
        $instance->cmid = $module->cmid;
        if ($status !== null) {
            $upload = new stdClass();
            $upload->instance = $instance->id;
            $upload->filepath = $filepath;
            $upload->status = $status;
            $upload->timecreated = time();
            $instance->uploadid = $DB->insert_record('videoconnect_uploads', $upload);
        }
        return $instance;
    }

    /**
     * Creates a real temp file, as save_temp_file() would.
     *
     * @return string Absolute path.
     */
    protected function create_temp_file(): string {
        $path = make_request_directory() . '/video.mp4';
        file_put_contents($path, 'x');
        return $path;
    }

    /**
     * New instances are stamped with the active provider.
     *
     * @covers ::videoconnect_add_instance
     */
    public function test_instances_are_stamped_with_provider(): void {
        $this->resetAfterTest();
        $instance = $this->create_activity();
        $this->assertSame('vimeo', $instance->provider);
    }

    /**
     * Derived states of the panel match each scenario.
     *
     * @covers \mod_videoconnect\uploads::get_summary
     * @covers \mod_videoconnect\uploads::get_state_case_sql
     */
    public function test_get_summary_derived_states(): void {
        $this->resetAfterTest();

        $this->create_activity('111111');
        $this->create_activity('', uploads::STATUS_NOT_EXECUTED);
        $this->create_activity('', uploads::STATUS_ERROR_UPLOADING);
        $this->create_activity('222222', uploads::STATUS_UPLOADING_ERROR_WHITELIST);
        $this->create_activity();

        $summary = uploads::get_summary();
        $this->assertSame(1, $summary[uploads::STATE_PUBLISHED]);
        $this->assertSame(1, $summary[uploads::STATE_PENDING]);
        $this->assertSame(1, $summary[uploads::STATE_ERROR]);
        $this->assertSame(1, $summary[uploads::STATE_INCIDENT]);
        $this->assertSame(1, $summary[uploads::STATE_NOVIDEO]);

        // El ámbito de curso: uno nuevo sin actividades no cuenta nada.
        $course = $this->getDataGenerator()->create_course();
        $scoped = uploads::get_summary((int) $course->id);
        $this->assertSame(0, array_sum($scoped));
    }

    /**
     * Retry guards: only failed uploads, with file, without published video.
     *
     * @covers \mod_videoconnect\uploads::is_retryable
     */
    public function test_is_retryable(): void {
        $this->resetAfterTest();
        $file = $this->create_temp_file();

        $ok = $this->create_activity('', uploads::STATUS_ERROR_UPLOADING, $file);
        $upload = (object) ['status' => uploads::STATUS_ERROR_UPLOADING, 'filepath' => $file];
        $this->assertTrue(uploads::is_retryable($upload, $ok));

        // Con vídeo publicado: nunca (duplicaría el vídeo en el proveedor).
        $published = (object) ['idvideo' => '123'];
        $this->assertFalse(uploads::is_retryable($upload, $published));

        // Sin fichero temporal: no hay nada que reintentar.
        $nofile = (object) ['status' => uploads::STATUS_ERROR_UPLOADING, 'filepath' => $file . '.gone'];
        $this->assertFalse(uploads::is_retryable($nofile, $ok));

        // Estado no reintentable (en curso).
        $uploading = (object) ['status' => uploads::STATUS_UPLOADING, 'filepath' => $file];
        $this->assertFalse(uploads::is_retryable($uploading, $ok));
    }

    /**
     * Retry requeues the row and triggers its event; guards throw.
     *
     * @covers \mod_videoconnect\uploads::retry
     */
    public function test_retry(): void {
        global $DB;
        $this->resetAfterTest();
        $file = $this->create_temp_file();
        $instance = $this->create_activity('', uploads::STATUS_ERROR_UPLOADING, $file);

        $sink = $this->redirectEvents();
        uploads::retry($instance->uploadid);
        $events = $sink->get_events();
        $sink->close();

        $this->assertSame(
            uploads::STATUS_NOT_EXECUTED,
            (int) $DB->get_field('videoconnect_uploads', 'status', ['id' => $instance->uploadid])
        );
        $this->assertInstanceOf(upload_retried::class, end($events));

        // Reintentar una fila ya encolada no está permitido.
        $this->expectException(moodle_exception::class);
        uploads::retry($instance->uploadid);
    }

    /**
     * Discard marks the row and triggers its event; mid-upload rows throw.
     *
     * @covers \mod_videoconnect\uploads::discard
     */
    public function test_discard(): void {
        global $DB;
        $this->resetAfterTest();
        $instance = $this->create_activity('', uploads::STATUS_NOT_EXECUTED);

        $sink = $this->redirectEvents();
        uploads::discard($instance->uploadid);
        $events = $sink->get_events();
        $sink->close();

        $this->assertSame(
            uploads::STATUS_DISCARDED,
            (int) $DB->get_field('videoconnect_uploads', 'status', ['id' => $instance->uploadid])
        );
        $this->assertInstanceOf(upload_discarded::class, end($events));

        // Una subida en curso nunca se descarta (carrera con el cron).
        $running = $this->create_activity('', uploads::STATUS_UPLOADING);
        $this->expectException(moodle_exception::class);
        uploads::discard($running->uploadid);
    }

    /**
     * get_latest breaks same-second ties by id (discard+insert race).
     *
     * @covers \mod_videoconnect\uploads::get_latest
     */
    public function test_get_latest_tiebreak(): void {
        global $DB;
        $this->resetAfterTest();
        $instance = $this->create_activity();

        $now = time();
        $old = (object) ['instance' => $instance->id, 'status' => uploads::STATUS_DISCARDED, 'timecreated' => $now];
        $DB->insert_record('videoconnect_uploads', $old);
        $new = (object) ['instance' => $instance->id, 'status' => uploads::STATUS_NOT_EXECUTED, 'timecreated' => $now];
        $newid = $DB->insert_record('videoconnect_uploads', $new);

        $latest = uploads::get_latest((int) $instance->id);
        $this->assertSame((int) $newid, (int) $latest->id);
        $this->assertSame(uploads::STATUS_NOT_EXECUTED, (int) $latest->status);
    }

    /**
     * Deleting the instance removes upload rows and their temp files.
     *
     * @covers ::videoconnect_delete_instance
     * @covers \mod_videoconnect\uploads::delete_temp_file
     */
    public function test_delete_instance_cleans_temp_files(): void {
        global $DB;
        $this->resetAfterTest();
        $file = $this->create_temp_file();
        $instance = $this->create_activity('', uploads::STATUS_ERROR_UPLOADING, $file);

        $this->assertFileExists($file);
        videoconnect_delete_instance((int) $instance->id);

        $this->assertFileDoesNotExist($file);
        $this->assertSame(0, $DB->count_records('videoconnect_uploads', ['instance' => $instance->id]));
        $this->assertSame(0, $DB->count_records('videoconnect', ['id' => $instance->id]));
    }
}
