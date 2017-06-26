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
 * Set course tags page.
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

if (empty($CFG->usetags)) {
    print_error('tagsaredisabled', 'tag');
}

$id = required_param('id', PARAM_INT);
$tagcollid = required_param('tagcollid', PARAM_INT);
$ctx = required_param('ctx', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_login($course, true);

$urlparams = array();
$urlparams['id'] = $id;
$urlparams['tagcollid'] = $tagcollid;

$PAGE->set_url(new moodle_url('/blocks/course_tags/settags.php', $urlparams));
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_course::instance($id));

$PAGE->set_title(get_string('settags', 'block_course_tags'));
$PAGE->set_heading($course->shortname);

$PAGE->requires->js_call_amd('block_course_tags/singleselect', 'init');
$PAGE->requires->js_call_amd('block_course_tags/filtertags', 'init');

/* @var block_course_tags_renderer $blockrenderer The block renderer. */
$blockrenderer = $PAGE->get_renderer('block_course_tags');
$pagecontents = $blockrenderer->settags_page($course, $tagcollid, $ctx);

echo $OUTPUT->header();
echo $pagecontents;
echo $OUTPUT->footer();
