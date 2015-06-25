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
 * Single Frikomport Format - Settings Library
 *
 * Description
 *
 * @package             course
 * @subpackage          format/single_frikomport
 * @copyright           2010 eFaktor
 *
 * @creationDate        20/04/2015
 * @author              eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die;


class format_single_frikomport_admin_setting_activitytype extends admin_setting_configselect {
    /**
     * This function may be used in ancestors for lazy loading of choices
     *
     * Override this method if loading of choices is expensive, such
     * as when it requires multiple db requests.
     *
     * @return bool true if loaded, false if error
     */
    public function load_choices() {
        global $CFG;
        require_once($CFG->dirroot. '/course/format/single_frikomport/lib.php');
        if (is_array($this->choices)) {
            return true;
        }
        $this->choices = format_single_frikomport::get_supported_activities();
        return true;
    }
}
