<?php
/**
 * Inconsistencies Course Completions  - Library - Extend Navigation
 *
 * @package         local
 * @subpackage      icp
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    25/05/2015
 * @author          eFaktor     (fbv)
 */
function local_icp_extend_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    if ($PAGE->course->enablecompletion) {
        // Only let users with the appropriate capability see this settings item.
        if (!has_capability('local/icp:manage', context_course::instance($PAGE->course->id))) {
            return;
        }

        if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {

            $str_title  = get_string('title_index', 'local_icp');
            $url        = new moodle_url('/local/icp/index.php',array('id' => $PAGE->course->id,));
            $icp        = navigation_node::create($str_title,
                                                  $url,
                                                  navigation_node::TYPE_SETTING,'course_completions',
                                                  'course_completions',
                                                  new pix_icon('i/edit', $str_title)
            );

            if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                $icp->make_active();
            }
            $settingnode->add_node($icp,'users');
        }
    }

}//local_icp_extend_settings_navigation
