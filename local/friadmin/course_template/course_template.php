<?php
/**
 * Course Template - Course Template
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/01/2016
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once('lib/coursetemplatelib.php');
require_once('../../../course/lib.php');

/* PARAMS   */
$courseId       = required_param('id',PARAM_INT);
$course         = get_course($courseId);
$contextCourse  = context_course::instance($courseId);
$url            = new moodle_url('/local/friadmin/course_template/course_template.php',array('id' => $courseId));

$strTitle       = get_string('coursetemplate_title', 'local_friadmin');
$strSubTitle    = get_string('course_enrolment', 'local_friadmin');

require_login($course);

/* Check Permissions/Capability */
if (!has_capability('local/friadmin:view',context_system::instance())) {
    if (!local_friadmin_helper::CheckCapabilityFriAdmin()) {
        print_error('nopermissions', 'error', '', 'block/frikomport:view');
    }//if_superuser
}

$PAGE->set_url($url);
$PAGE->set_context($contextCourse);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_friadmin'));

/* Header   */
echo $OUTPUT->header();
$info = array('id'          => $course->id,
              'shortname'   => $course->shortname,
              'fullname'    => $course->fullname
             );

$result = '<p class="result">';
$result .=  get_string('coursetemplate_result', 'local_friadmin', $info);
$result .= '</p>';

echo $OUTPUT->heading($strTitle,2);
echo $OUTPUT->heading($strSubTitle,3);

$linkLst = new local_friadmin_coursetemplate_linklist();

$linkLst->create_linklist($courseId);

echo $result;
echo $linkLst->getContentListLink();


/* Footer   */
echo $OUTPUT->footer();