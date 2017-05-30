<?php
/**
 * Completion reset - Selection users
 *
 * @package         mod
 * @subpackage      completionreset
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    29/03/2017
 * @author          eFaktor     (fbv)
 */
require('../../config.php');
require_once('locallib.php');

// Params
$courseid   = required_param('co',PARAM_INT);
$course     = null;
$resetid    = required_param('re',PARAM_INT);
$url        = new moodle_url('/mod/completionreset/choose_users.php', array('co' => $courseid,'re' =>$resetid));
$context    = context_course::instance($courseid);
$config     = get_config('completionreset');
// Get renderer
$renderer = $PAGE->get_renderer('mod_completionreset');
// Get course
$course = get_course($courseid);


require_login($course);
require_capability('mod/completionreset:manage', $context);

// Set page
$PAGE->set_url($url);
$PAGE->set_pagelayout('course');
$PAGE->set_context($context);
$PAGE->set_title($course->shortname.': '. get_string('title','completionreset'));
$PAGE->set_heading($course->fullname);

echo $renderer->header_choose();
$mform->display();
echo $renderer->footer();
