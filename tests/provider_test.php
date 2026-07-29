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
 * Provider connector tests.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect;

use advanced_testcase;
use mod_videoconnect\provider\provider_manager;
use mod_videoconnect\provider\vimeo_provider;

/**
 * Tests for the video provider connectors and their resolution.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_videoconnect\provider\vimeo_provider
 * @covers     \mod_videoconnect\provider\provider_manager
 */
final class provider_test extends advanced_testcase {
    /**
     * Video references accepted by the Vimeo connector.
     *
     * @covers \mod_videoconnect\provider\vimeo_provider::parse_video_reference
     * @dataProvider video_reference_provider
     * @param string $input Raw user input.
     * @param string|null $expected Parsed ID, '' or null.
     */
    public function test_parse_video_reference(string $input, ?string $expected): void {
        $provider = new vimeo_provider();
        $this->assertSame($expected, $provider->parse_video_reference($input));
    }

    /**
     * Data provider of raw video references.
     *
     * @return array[]
     */
    public static function video_reference_provider(): array {
        return [
            'empty' => ['', ''],
            'spaces only' => ['   ', ''],
            'numeric id' => ['536287845', '536287845'],
            'plain url' => ['https://vimeo.com/536287845', '536287845'],
            'url without protocol' => ['vimeo.com/536287845', '536287845'],
            'www url' => ['https://www.vimeo.com/536287845', '536287845'],
            'player url' => ['https://player.vimeo.com/video/536287845', '536287845'],
            'manage url' => ['https://vimeo.com/manage/videos/536287845', '536287845'],
            'url with query' => ['https://vimeo.com/536287845?share=copy', '536287845'],
            'invalid text' => ['not a video', null],
            'other host' => ['https://example.com/536287845', null],
            'negative-ish input' => ['video 1234', null],
        ];
    }

    /**
     * Folder references accepted by the Vimeo connector.
     *
     * @covers \mod_videoconnect\provider\vimeo_provider::parse_folder_reference
     * @dataProvider folder_reference_provider
     * @param string $input Raw user input.
     * @param string|null $expected Parsed ID, '' or null.
     */
    public function test_parse_folder_reference(string $input, ?string $expected): void {
        $provider = new vimeo_provider();
        $this->assertSame($expected, $provider->parse_folder_reference($input));
    }

    /**
     * Data provider of raw folder references.
     *
     * @return array[]
     */
    public static function folder_reference_provider(): array {
        return [
            'empty' => ['', ''],
            'numeric id' => ['4206879', '4206879'],
            'manage url' => ['https://vimeo.com/manage/folders/4206879', '4206879'],
            'user folder url' => ['https://vimeo.com/user/123/folder/4206879?isPrivate=true', '4206879'],
            'invalid' => ['carpetas', null],
        ];
    }

    /**
     * The upload result URI resolves to the numeric video ID.
     *
     * @covers \mod_videoconnect\provider\vimeo_provider::extract_videoid
     */
    public function test_extract_videoid(): void {
        $provider = new vimeo_provider();
        $this->assertSame(123456789, $provider->extract_videoid('/videos/123456789'));
        $this->assertSame(123456789, $provider->extract_videoid('123456789'));
        $this->assertSame(0, $provider->extract_videoid('/videos/none'));
    }

    /**
     * Embed and public URLs point to the video.
     *
     * @covers \mod_videoconnect\provider\vimeo_provider::get_embed_url
     * @covers \mod_videoconnect\provider\vimeo_provider::get_video_url
     */
    public function test_urls(): void {
        $provider = new vimeo_provider();
        $this->assertStringStartsWith('https://player.vimeo.com/video/42', $provider->get_embed_url('42'));
        $this->assertSame('https://vimeo.com/42', $provider->get_video_url('42'));
    }

    /**
     * The manager resolves the active provider and falls back on unknowns.
     *
     * @covers \mod_videoconnect\provider\provider_manager
     */
    public function test_manager_resolution(): void {
        $this->resetAfterTest();

        // Sin configurar: el proveedor por defecto.
        $this->assertSame('vimeo', provider_manager::get_active_name());
        $this->assertInstanceOf(vimeo_provider::class, provider_manager::get_active());

        // Configurado a un valor desconocido: degrada al por defecto.
        set_config('provider', 'youtube', 'mod_videoconnect');
        $this->assertSame('vimeo', provider_manager::get_active_name());

        $provider = provider_manager::get_provider('doesnotexist');
        $this->assertDebuggingCalled();
        $this->assertInstanceOf(vimeo_provider::class, $provider);

        // La instancia se cachea por nombre.
        $this->assertSame(
            provider_manager::get_provider('vimeo'),
            provider_manager::get_provider('vimeo')
        );
    }
}
