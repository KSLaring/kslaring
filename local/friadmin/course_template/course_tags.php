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
 * Course Template - Course Template
 *
 * @package         local
 * @subpackage      friadmin/course_tags
 * @copyright       2017 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
//require_once('lib/coursetemplatelib.php');
require_once('classes/ct_course_tags_linklist.php');
//require_once('../../../course/lib.php');

require_login();

/* PARAMS   */
$courseid = required_param('id', PARAM_INT);

$course = get_course($courseid);
$contextcourse = context_course::instance($courseid);

$strtitle = get_string('coursetemplate_title', 'local_friadmin');
$strsubtitle = get_string('coursetemplate_tags', 'local_friadmin');
$pageurl = new moodle_url('/local/friadmin/course_template/course_tags.php',
    array('id' => $courseid));

$PAGE->requires->js_call_amd('block_course_tags/singleselect', 'init');
$PAGE->requires->js_call_amd('block_course_tags/filtertags', 'init');

$PAGE->set_url($pageurl);
$PAGE->set_context($contextcourse);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($strtitle . '- ' . $strsubtitle);
$PAGE->navbar->add(get_string('pluginname', 'local_friadmin'));

/* Check Permissions/Capability */
if (!has_capability('local/friadmin:view', context_system::instance())) {
    if (!local_friadmin_helper::CheckCapabilityFriAdmin()) {
        print_error('nopermissions', 'error', '', 'block/frikomport:view');
    }//if_superuser
}

// Use the course tag settings area from the course tags block.
// Get the defined course tag collection.
$tagcollid = core_tag_area::get_collection('core', 'course');

/* @var block_course_tags_renderer $blockrenderer The block renderer. */
$blockrenderer = $PAGE->get_renderer('block_course_tags');
$pagecontents = $blockrenderer->settags_page($course, $tagcollid, $contextcourse->id, true);

// Get the wizard buttons.
$linklist = new ct_course_tags_linklist($courseid);

/* Header   */
echo $OUTPUT->header();

echo $OUTPUT->heading($strtitle, 2);
echo $OUTPUT->heading($strsubtitle, 3);

echo $pagecontents;
echo $linklist->getlinklistcontent();

/* Footer   */
echo $OUTPUT->footer();
