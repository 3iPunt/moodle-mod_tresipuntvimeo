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
 * Control panel enhancements.
 *
 * Progressive enhancement only: the filter form works without JavaScript
 * (plain selects + submit button). This module upgrades the course select
 * to the core AJAX course selector and applies the course scope on change.
 *
 * @module     mod_videoconnect/panel
 * @copyright  2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Autocomplete from 'core/form-autocomplete';

/**
 * Initialises the course scope selector of the panel.
 *
 * @param {string} selectid DOM id of the course select element.
 * @param {string} placeholder Input placeholder: the active course name, or
 *        the "all courses" string when no scope is applied (the selection
 *        chips are hidden by CSS to keep the field a single line tall).
 */
export const init = async(selectid, placeholder) => {
    const select = document.getElementById(selectid);
    if (!select) {
        return;
    }
    try {
        await Autocomplete.enhance('#' + selectid, false, 'core/form-course-selector', placeholder);
        // El ámbito de curso se aplica al elegirlo (o al quitarlo): el resto
        // de filtros viajan en el mismo formulario, así que basta enviarlo.
        select.addEventListener('change', () => select.form.submit());
    } catch {
        // Sin mejora AJAX el formulario sigue siendo operativo (submit manual).
    }
};
