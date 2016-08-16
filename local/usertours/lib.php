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
 * Tour.
 *
 * @package    local_usertours
 * @copyright  2016 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_usertours\helper;

/**
 * Manage inplace editable saves.
 *
 * @param   string      $itemtype       The type of item.
 * @param   int         $itemid         The ID of the item.
 * @param   mixed       $newvalue       The new value
 * @return  string
 */
function local_usertours_inplace_editable($itemtype, $itemid, $newvalue) {
    $context = \context_system::instance();
    external_api::validate_context($context);
    require_capability('moodle/site:config', $context);

    if ($itemtype === 'tourname') {
        $tour = helper::get_tour($itemid);
        $tour->set_name($newvalue)->persist();

        return helper::render_tourname_inplace_editable($tour);
    } else if ($itemtype === 'tourcomment') {
        $tour = helper::get_tour($itemid);
        $tour->set_comment($newvalue)->persist();

        return helper::render_tourcomment_inplace_editable($tour);
    } else if ($itemtype === 'stepname') {
        $step = helper::get_step($itemid);
        $step->set_title($newvalue)->persist();

        return helper::render_stepname_inplace_editable($step);
    }
}

function local_usertours_extend_navigation(global_navigation $nav) {
    \local_usertours\helper::bootstrap();
}
