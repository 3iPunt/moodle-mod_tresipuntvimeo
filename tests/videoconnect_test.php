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
 * Plugin helper tests.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect;

use advanced_testcase;

/**
 * Tests for the videoconnect helper.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_videoconnect\videoconnect
 */
final class videoconnect_test extends advanced_testcase {
    /**
     * Domains parse into a clean, lowercased, deduped list.
     *
     * @covers \mod_videoconnect\videoconnect::parse_domains
     */
    public function test_parse_domains(): void {
        $raw = "Campus.Example.com\n\n  campus.example.com  \notro.example.com\r\ntercero.example.com";
        $this->assertSame(
            ['campus.example.com', 'otro.example.com', 'tercero.example.com'],
            videoconnect::parse_domains($raw)
        );
        $this->assertSame([], videoconnect::parse_domains("  \n \n"));
    }

    /**
     * Domain validation: host names without protocol nor path.
     *
     * @covers \mod_videoconnect\videoconnect::is_valid_domain
     * @dataProvider domain_provider
     * @param string $domain Candidate domain.
     * @param bool $valid Expected verdict.
     */
    public function test_is_valid_domain(string $domain, bool $valid): void {
        $this->assertSame($valid, videoconnect::is_valid_domain($domain));
    }

    /**
     * Data provider of candidate domains.
     *
     * @return array[]
     */
    public static function domain_provider(): array {
        return [
            'plain' => ['campus.example.com', true],
            'subdomain dashes' => ['mi-campus.example.com', true],
            'with protocol' => ['https://campus.example.com', false],
            'with path' => ['campus.example.com/moodle', false],
            'single label' => ['localhost', false],
            'empty' => ['', false],
        ];
    }

    /**
     * The whitelist flag keeps the historical behaviour when unset.
     *
     * @covers \mod_videoconnect\videoconnect::is_whitelist_enabled
     */
    public function test_is_whitelist_enabled(): void {
        $this->resetAfterTest();
        // Sin setting guardado: comportamiento histórico (activada).
        unset_config('usewhitelist', 'mod_videoconnect');
        $this->assertTrue(videoconnect::is_whitelist_enabled());

        set_config('usewhitelist', 0, 'mod_videoconnect');
        $this->assertFalse(videoconnect::is_whitelist_enabled());

        set_config('usewhitelist', 1, 'mod_videoconnect');
        $this->assertTrue(videoconnect::is_whitelist_enabled());
    }

    /**
     * Folder extraction delegates to the active provider connector.
     *
     * @covers \mod_videoconnect\videoconnect::extract_folderid
     */
    public function test_extract_folderid(): void {
        $this->assertSame('4206879', videoconnect::extract_folderid('https://vimeo.com/manage/folders/4206879'));
        $this->assertSame('', videoconnect::extract_folderid(''));
        $this->assertNull(videoconnect::extract_folderid('nope'));
    }
}
