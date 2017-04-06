<?php
// This file is part of ksl
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

require_once( '../../config.php');
require_once( 'forms/ksl_forms.php');
require_once('lib/kslib.php');

// Params!
require_login();

// Variables!
$contextsystem = context_system::instance();
$CFG->wwwroot;

// Startpage!
$url = new moodle_url('/local/ksl/index.php');
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($contextsystem);
$PAGE->requires->js('/local/ksl/js/ksljs.js');  // Enable / Disable.
$PAGE->requires->js('/local/ksl/js/levels.js'); // Activate / Clean.
$page       = optional_param('page', 0, PARAM_INT);
$perpage    = optional_param('page', 15, PARAM_INT);
$sort       = 'asc';
$out        = null;

// Capabilities!
require_capability('local/ksl:manage', $contextsystem);

$contextpp = context_course::instance(2);
$mform = new main_form(null);

ksl::get_javascript_values('level_0', 'level_1', 'level_2', 'level_3');

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot);
} else if ($fromform = $mform->get_data()) {
    if ($fromform->type == 1) {  // Industrycode.
        $SESSION->type = 1;
        $SESSION->industrycode  = $fromform->industrycode;
        $url = new moodle_url('/local/ksl/reports/rptcode.php', array('sort' => $sort, 'page' => $page, 'perpage' => $perpage));
        redirect($url);
    } else if ($fromform->type == 0) { // Organization.
        $SESSION->type = 0;
        $SESSION->organization0 = $fromform->level_0;
        $SESSION->organization1 = $fromform->level_1;
        $SESSION->organization2 = $fromform->level_2;
        $SESSION->organization3 = $fromform->level_3;
        $url = new moodle_url('/local/ksl/reports/rptorg.php', array('sort' => $sort, 'page' => $page));
        redirect($url);
    }
}

// Print Header!
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('indexheading', 'local_ksl'));
$mform->display();

// Print Footer!
echo $OUTPUT->footer();