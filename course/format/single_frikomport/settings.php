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
 * Single Frikomport Format - Settings
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
require_once($CFG->dirroot. '/course/format/single_frikomport/settingslib.php');

if ($ADMIN->fulltree) {
    $settings->add(new format_single_frikomport_admin_setting_activitytype('format_single_frikomport/activitytype',
            new lang_string('defactivitytype', 'format_single_frikomport'),
            new lang_string('defactivitytypedesc', 'format_single_frikomport'),
            'forum', null));
}
