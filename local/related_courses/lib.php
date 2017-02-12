<?php
/**
 * Related Courses (local) - Library
 *
 * Description
 *
 * @package         local
 * @subpackage      related_courses
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      16/09/2014
 * @author          eFaktor     (fbv)
 *
 */
function local_related_courses_extend_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('local/related_courses:manage', context_course::instance($PAGE->course->id))) {
        return;
    }

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $str_title = get_string('title', 'local_related_courses');
        $url = new moodle_url('/local/related_courses/related_courses.php',array('id' => $PAGE->course->id));
        $related_courses = navigation_node::create($str_title,
            $url,
            navigation_node::TYPE_SETTING,'related_courses',
            'related_courses',
            new pix_icon('i/edit', $str_title)
        );
        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $related_courses->make_active();
        }
        $settingnode->add_node($related_courses,'users');
    }
}//local_microlearning_extends_settings_navigation