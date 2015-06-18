<?php
/**
 * Local Fridadmin  - Language Settings (English)
 *
 * @package         local
 * @subpackage      fridamin/lang
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @author          eFaktor     (Urs Hunkler {@link urs.hunkler@unodo.de})
 *
 * @updateDate      16/06/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Integrate 'Course Locations' plugin into FriAdmin plugin
 */


$string['pluginname'] = 'Friadmin';

$string['courselist_title'] = 'Friadmin available courses';
$string['coursetable_title'] = 'Course table';
$string['course_courseid'] = 'Course ID';
$string['course_name'] = 'Name';
$string['course_summary'] = 'Summary';
$string['course_targetgroup'] = 'Target group';
$string['course_date'] = 'Date';
$string['course_time'] = 'Time from - to';
$string['course_seats'] = 'Avail.&nbsp;seats';
$string['course_deadline'] = 'Deadline';
$string['course_length'] = 'Length';
$string['course_municipality'] = 'Municipality';
$string['course_sector'] = 'Sector';
$string['course_location'] = 'Location';
$string['course_responsible'] = 'In charge';
$string['course_teacher'] = 'Teacher';
$string['course_priceinternal'] = 'Price internal';
$string['course_priceexternal'] = 'Price external';
$string['course_link'] = 'Link';
$string['course_edit'] = '';

$string['coursedetail_title'] = 'Friadmin course details';
$string['coursedetail_back'] = 'Back to courselist';
$string['coursedetail_go'] = 'Open course';
$string['coursedetail_settings'] = 'Course settings';
$string['coursedetail_completion'] = 'Course completions';
$string['coursedetail_statistics'] = 'Overview statistics';
$string['coursedetail_users'] = 'Enrolled users';
$string['coursedetail_confirmed'] = 'Manage confirmed';
$string['coursedetail_waitlist'] = 'Manage queue';
$string['coursedetail_participantlist'] = 'Downlaod participant list';
$string['coursedetail_duplicate'] = 'Duplicate';
$string['coursedetail_email'] = 'Send email';

$string['coursetemplate_title'] = 'Friadmin add from template';
$string['coursetemplate_subtitle'] = 'Create a course from a course template.';
$string['coursetemplate_cat'] = 'Course template category';
$string['coursetemplate_cat_desc'] = 'Please select the course category where all template courses are stored.';
$string['coursetemplate_cat_select'] = 'Select course template category ...';
$string['coursetemplate_go'] = 'Open course';
$string['coursetemplate_another'] = 'Create another course';
$string['coursetemplate_settings'] = 'Course settings';
$string['coursetemplate_overview'] = 'Course overview';
$string['coursetemplate_result'] = 'The course has been created -
id: <strong>{$a->id}</strong>, shortname: "<strong>{$a->shortname}</strong>",
fullname: "<strong>{$a->fullname}</strong>".';
$string['coursetemplate_error'] = 'Course could not be created.';

$string['location'] = 'Location: ';
$string['fromto'] = 'From - to: ';
$string['coursename'] = 'Course name: ';
$string['selmunicipality'] = 'My municipalities';
$string['selsector'] = 'All sectors';
$string['sellocation'] = 'All locations';
$string['selname'] = 'Course name';
$string['selcategory'] = 'Target category';
$string['missingselcategory'] = 'Missing target category';
$string['seltemplate'] = 'Course template';
$string['missingseltemplate'] = 'Missing course template';
$string['selsubmit'] = 'Search';
$string['selsubmitcreate'] = 'Create course';

$string['edit'] = 'Edit course';
$string['show'] = 'Show details';

/* ********************** */
/* Course Location Plugin */
/* ********************** */
$string['plugin_course_locations'] = 'Course Locations';
$string['friadmin:course_locations_manage'] = 'Manage Course Locations';

$string['lst_locations'] = 'Browse list of locations';
$string['new_location'] = 'New Location';
$string['edit_location'] = 'Edit Location';
$string['edit'] = 'Edit';
$string['del_location'] = 'Delete Location';
$string['view_location'] = 'View Location';
$string['courses_locations'] = 'Courses Location List';

$string['title_locations'] = 'Locations';
$string['title_courses_locations'] = 'Available Courses';
$string['title_general'] = 'General';

$string['exist_locations'] = 'Existing Locations';
$string['location'] = 'Location';
$string['filter'] = 'Filter';
$string['municipality'] = 'Municipalities';
$string['counties'] = 'Counties';
$string['sectors'] = 'Sectors';
$string['select_level_list'] = 'Select Item';
$string['activate'] = 'Activate';
$string['deactivate'] = 'Deactivate';

$string['location_county'] = 'County';
$string['location_muni'] = 'Municipality';
$string['location_name'] = 'Name';
$string['location_desc'] = 'Description';
$string['location_url'] = 'Url more information';
$string['location_floor'] = 'Floor';
$string['location_room'] = 'Room';
$string['location_seats'] = 'Max. Seats';
$string['location_detail'] = 'Detail';
$string['location_address'] = 'Address';
$string['location_street'] = 'Street';
$string['location_post_code'] = 'Post Code';
$string['location_city'] = 'City';
$string['location_map'] = 'Url map';
$string['location_post'] = 'Post address';
$string['location_contact'] = 'Contact person';
$string['location_phone'] = 'Contact phone';
$string['location_mail'] = 'Contact eMail';
$string['location_contact_inf'] = 'Contact';
$string['location_comments'] = 'Comments';

$string['sel_location'] = 'Select location ...';
$string['sel_sector'] = 'Select sector ...';

$string['no_data'] = 'No data found for your selection.';
$string['return_to_selection'] = 'Return to locations selection page';

$string['error_deleting_location'] = 'Sorry, the location could not be deleted because there are courses connected with';
$string['deleted_location'] = 'The location has been revomed';
$string['delete_location_are_you_sure'] = '<p>Are you sure you want to delete the next location ? </p>
<li><strong>Municipality: </strong>{$a->muni}</li>
<li><strong>Location: </strong>{$a->name}</li>
<li><strong>Address: </strong>{$a->address}</li>';

$string['btn_save'] = 'Save';
$string['lnk_back'] = 'Back';


