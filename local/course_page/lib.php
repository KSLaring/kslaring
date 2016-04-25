<?php
/**
 * Course Home Page - Library
 *
 * Description
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      13/09/2014
 * @author          eFaktor     (fbv)
 *
 */
function local_course_page_extend_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('local/course_page:manage', context_course::instance($PAGE->course->id))) {
        return;
    }

    $format_options = course_get_format($PAGE->course)->get_format_options();
    if (array_key_exists('homepage',$format_options) && ($format_options['homepage'])) {
        if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
            $home_url = new moodle_url('/local/course_page/home_page.php',array('id' => $PAGE->course->id,'start'=>0));
            $home_url->param('sesskey', sesskey());
            if ($PAGE->user_is_editing()) {
                $home_url->param('edit', 'on');
                $home_url->param('show', '1');
            } else {
                $home_url->param('edit', 'off');
                $home_url->param('show', '0');
            }
            $str_edit = get_string('edit_home_page','local_course_page');
            $home_node = navigation_node::create($str_edit,
                                                 $home_url,
                                                 navigation_node::TYPE_SETTING,'homepage',
                                                 'homepage',
                                                 new pix_icon('i/settings', '')
            );
            if ($PAGE->url->compare($home_url, URL_MATCH_BASE)) {
                $home_node->make_active();
            }
            $settingnode->add_node($home_node,'editsettings');
        }//if_settingnode
    }
}//local_course_page_extends_setting_navigation
