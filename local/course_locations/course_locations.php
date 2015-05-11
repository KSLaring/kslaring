<?php
/**
 * Course Locations
 *
 * @package         local
 * @subpackage      course_locations
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      07/05/2015
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../config.php');
require_once('locationslib.php');
require_once('locations_form.php');

require_login();

/* PARAMS   */
$url            = new moodle_url('/local/course_locations/course_locations.php');
$context        = context_system::instance();
$myCompetence   = null;

require_capability('local/course_locations:manage',$context);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname','local_course_locations'));
$PAGE->navbar->add(get_string('courses_locations','local_course_locations'),$url);


/* Get My Competence Locations  */
$myCompetence = CourseLocations::Get_MyCompetence($USER->id);


/* Header   */
echo $OUTPUT->header();

/* Table with locations */
echo $OUTPUT->heading(get_string('title_courses_locations','local_course_locations'));


echo "<strong>Get_CoursesLocations_List(filer,sort,fieldSort)</strong>.Function to get all courses." . '</br>';
echo "<li><u>sort</u>. (ASC/DESC) It has not added to the SQL yet.</li>";
echo "<li><u>fieldSort</u>. It has not added to the SQL yet.</li>";
echo "<li><u>filter</u>. Filter criteria. Array.";
echo "<ul>";
echo "<li>filter['county']. 0 --> means all counties. </li>";
echo "<li>filter['muni']. 0 --> means all municipalities. </li>";
echo "<li>filter['sector']. 0 --> means all sectors. </li>";
echo "<li>filter['course']. null --> means all courses. not Null --> Filter by course name </li>";
echo "<li>filter['fromDate']. 0 --> Don't apply filter. </li>";
echo "<li>filter['toDate']. 0 --> Don't apply filter. </li>";
echo "<li>filter['fromDate']/filter['toDate']. Not 0 --> Filter by Start Date Course. </li>";
echo "</ul>";
echo "</li>";

echo "</br></br>";
echo "<strong>Get_CoursesLocations_List</strong>.Return an Array. " . "</br>";
echo " Each element of the array is an object. And it contains the next information : " . "</br>";
echo "<li><u>course</u>.         Course id</li>";
echo "<li><u>name</u>.           Course name</li>";
echo "<li><u>start</u>.          Course Start Date.</li>";
echo "<li><u>maxSeats</u>.       Max Seats.</li>";
echo "<li><u>length</u>.         Course Length. (Course Format Options)</li>";
echo "<li><u>county</u>.         County name</li>";
echo "<li><u>municipality</u>.   Municipality name</li>";
echo "<li><u>sectors</u>.        All sectors name,connected with the course, separated by coma.</li>";
echo "<li><u>location</u>.       Location name.</li>";


echo "<strong>Get_MyCompetence(userId)</strong>.Function to get the municipalities, ordered by county, that the user is able to see. ." . '</br>';
echo "Return Array. Each element of array is an object. It contains the next information : " . "</br>";
echo "<li><u>id</u>.            Level Zero Id. County Id. </li>";
echo "<li><u>levelOne</u>.      Level One. Municipality List. Municipality Ids separated by comma.
      If the user has permission to see all the municipalities, that belong to the county, then the value is 0.
      If the user has only permission to see some municipalities, then it will be a list of Id municipalities separated by comma. </li>";

echo "<strong>Get_Companies($in=null,$parent=null)</strong>.Function to get the companies." . '</br>';
echo "<li><u>$in</u>.       Companies that belong that list.</li>";
echo "<li><u>$parent</u>.   Companies connected with</li>";

echo $OUTPUT->footer();