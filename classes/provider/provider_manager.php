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
 * Video provider resolution.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\provider;

use dml_exception;

/**
 * Resolves video provider connectors.
 *
 * Design decisions (2026-07-28, INDEX § Hacia la 2.1.0):
 * - The active provider is a site-level setting (mod_videoconnect/provider,
 *   default 'vimeo'); teachers never choose it. New activities are stamped
 *   with the active provider ({videoconnect}.provider) and keep playing
 *   through the connector that created them even after a site switch.
 * - Closed list (interface + implementations, no subplugins): a new
 *   provider = a new connector class registered in CONNECTORS.
 * - One configuration per connector, persistent while inactive: each
 *   connector owns its own config keys.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_manager {
    /** @var string Fallback provider (also the value stamped by upgrade on legacy rows). */
    public const DEFAULT = 'vimeo';

    /** @var string[] Connector class per machine name (closed list). */
    protected const CONNECTORS = [
        'vimeo' => vimeo_provider::class,
    ];

    /** @var provider_interface[] Instantiated connectors, by name. */
    protected static array $instances = [];

    /**
     * Machine name of the site-wide active provider.
     *
     * @return string
     * @throws dml_exception
     */
    public static function get_active_name(): string {
        $name = (string) get_config('mod_videoconnect', 'provider');
        return isset(self::CONNECTORS[$name]) ? $name : self::DEFAULT;
    }

    /**
     * Connector of the site-wide active provider.
     *
     * @return provider_interface
     * @throws dml_exception
     */
    public static function get_active(): provider_interface {
        return self::get_provider(self::get_active_name());
    }

    /**
     * Connector by machine name (e.g. the one stamped on an instance).
     *
     * Unknown names resolve to the default connector: better a wrong link
     * than a broken page for historical rows.
     *
     * @param string $name Machine name of the provider.
     * @return provider_interface
     */
    public static function get_provider(string $name): provider_interface {
        if (!isset(self::CONNECTORS[$name])) {
            debugging("mod_videoconnect: unknown provider '{$name}', falling back to '" . self::DEFAULT . "'",
                DEBUG_DEVELOPER);
            $name = self::DEFAULT;
        }
        if (!isset(self::$instances[$name])) {
            $classname = self::CONNECTORS[$name];
            self::$instances[$name] = new $classname();
        }
        return self::$instances[$name];
    }
}
