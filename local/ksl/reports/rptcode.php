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

require_once( '../../../config.php');
require_once( '../forms/ksl_forms.php');
require_once( '../lib/kslib.php');

// Variables!
$contextsystem  = context_system::instance();
$userarray      = null;
$errormsg       = null;
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 1, PARAM_INT);
$sort           = 'asc';
$url            = new moodle_url('/local/ksl/reports/rptcode.php', array('sort' => $sort, 'page' => $page, 'perpage' => $perpage));

$industrycode     = $SESSION->industrycode;

$url = new moodle_url('/local/ksl/reports/rptcode.php');

// Startpage!
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($contextsystem);

// Breadcrumb!
$navbar = $PAGE->navbar->add(get_string('pluginname', 'local_ksl'), new moodle_url('/local/ksl/index.php'), null);
$navbar->make_active();

// Capabilities!
require_capability('local/ksl:manage', $contextsystem);

$userarray  = ksl::local_ksl_industrysearch($industrycode, $page, $perpage);
$usercount  = ksl::local_ksl_industrysearch_count($industrycode);
$out        = ksl::display_users($userarray, $industrycode);

// Print Header!
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('industrycoderpt', 'local_ksl'));

echo $OUTPUT->paging_bar($usercount, $page, $perpage, $url);

echo $out;

echo $OUTPUT->paging_bar($usercount, $page, $perpage, $url);

// Print Footer!
echo $OUTPUT->footer();