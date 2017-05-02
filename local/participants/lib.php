<?php
/**
 * Participants List - Global library
 *
 * @package         local
 * @subpackage      participants
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    06/07/2016
 * @author          eFaktor     (fbv)
 */
function local_participants_extend_settings_navigation($settingsnav, $context) {
    /* Variables */
    global $PAGE;
    $strTitle           = null;
    $url                = null;
    $participantsNode   = null;
    $listNode           = null;
    $reportNode         = null;

    try {
        // Only add this settings item on non-site course pages.
        if (!$PAGE->course or $PAGE->course->id == 1) {
            return;
        }

        // Only let users with the appropriate capability see this settings item.
        if (!has_capability('local/participants:manage', context_course::instance($PAGE->course->id))) {
            return;
        }

        /* Add link to participants list */
        $course = get_course($PAGE->course->id);
        if (($course->format == 'classroom')
            ||
            ($course->format == 'classroom_frikomport')) {
            if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {

                $strTitle   = get_string('pluginname', 'local_participants');
                $url        = new moodle_url('/local/participants/participants.php',array('id' => $PAGE->course->id));

                $participantsNode = navigation_node::create($strTitle,
                                                             $url,
                                                             navigation_node::TYPE_SETTING,
                                                             'participants',
                                                             'participants',
                                                             new pix_icon('i/edit', $strTitle)
                                                            );
                if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                    $participantsNode->make_active();
                }
                $settingnode->add_node($participantsNode,'users');
            }//if_courseadmin
        }//if_format

    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_participants_extends_settings_navigation
