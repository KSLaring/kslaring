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

defined('MOODLE_INTERNAL') || die;

/**
 * Class containing data for the mod_registerattendance view page
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_registerattendance_view_page extends mod_registerattendance_widget implements renderable {
    /**
     * Construct the view renderable.
     *
     * @param object $cm The course module
     */
    public function __construct($cm) {
        // Create the data object and set the first values.
        parent::__construct();

        $this->data->url = new moodle_url('/mod/registerattendance/view.php', array('id' => $cm->id));
        $this->data->title = get_string('view_title', 'mod_registerattendance');
    }

    /**
     * Build the HTML for the user feedback shown on the page to participants.
     *
     * @param \mod_registerattendance\registerattendance $registerattendance The module object
     *
     * @return string The user feedback as HTML
     */
    public function construct_user_feedback($registerattendance) {
        $out = '';
        $strattended = get_string('attended', 'mod_registerattendance');
        $strnotattended = get_string('notattended', 'mod_registerattendance');

        // Load criteria to display.
        /* @var \completion_info $completion The completion info object */
        $completion = $registerattendance->completion;
        $this->wipe_session_cache();
        $completiondata = $completion->get_data($registerattendance->cm);

        $attended = $completiondata->completionstate == COMPLETION_COMPLETE ? $strattended : $strnotattended;
        $out .= get_string('youhaveattended', 'mod_registerattendance', $attended);

        return $out;
    }

    /**
     * Wipes information cached in user session.
     */
    protected function wipe_session_cache() {
        global $SESSION;
        unset($SESSION->completioncache);
        unset($SESSION->completioncacheuserid);
    }
}
