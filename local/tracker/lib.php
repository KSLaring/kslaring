<?php

function local_tracker_extends_navigation(global_navigation $navigation) {
    global $USER;

    if (isloggedin()) {
        $nodeTracker = $navigation->add(get_string('name','local_tracker'), new moodle_url('/report/manager/tracker/index.php'));

        if (is_siteadmin($USER->id)) {
            $nodBar = $nodeTracker->add(get_string('report_manager','local_tracker'),new moodle_url('/report/manager/index.php'));
            $nodBar = $nodeTracker->add(get_string('company_structure','local_tracker'),new moodle_url('/report/manager/company_structure/company_structure.php'));
            $nodBar = $nodeTracker->add(get_string('outcome_report','local_tracker'),new moodle_url('/report/manager/outcome_report/outcome_report.php'));
            $nodBar = $nodeTracker->add(get_string('course_report','local_tracker'),new moodle_url('/report/manager/course_report/course_report.php'));
            $nodBar = $nodeTracker->add(get_string('job_roles','local_tracker'),new moodle_url('/report/manager/job_role/job_role.php'));
            $nodBar = $nodeTracker->add(get_string('outcomes','local_tracker'),new moodle_url('/report/manager/outcome/outcome.php'));
            $nodBar = $nodeTracker->add(get_string('outcome_area_title','local_tracker'),new moodle_url('/grade/edit/outcome/index.php'));
        }else {
            if (has_capability('report/manager:viewlevel4', CONTEXT_SYSTEM::instance())) {
                $nodBar = $nodeTracker->add(get_string('report_manager','local_tracker'),new moodle_url('/report/manager/index.php'));
            }
        }//if_else
    }
}//tracker_extends_navigation

/**
 * @param           $settingsnav
 * @param           $context
 *
 * @creationDate    30/01/2015
 * @author          eFaktor         (fbv)
 *
 * Description
 * Add to 'My Profile Settings Menu' the link to the competence user info profile
 */
function local_tracker_extends_settings_navigation($settingsnav, $context) {
    /* Variables    */
    global $USER;
    $node_before    = null;

    if ($setting_node = $settingsnav->get('usercurrentsettings')) {
        $str_title = get_string('competence_profile', 'profilefield_competence');
        $url =new moodle_url('/user/profile/field/competence/competence.php',array('id' =>$USER->id));

        /* Create Node  */
        $competence_node = navigation_node::create($str_title,
                                                   $url,
                                                   navigation_node::TYPE_SETTING,'competence_profile',
                                                   'competence_profile',
                                                   null);
        /* Find the position to add the link    */
        foreach ($setting_node->children as $child) {
            if ($child->text == get_string('changepassword')) {
                $node_before = $child->key;
            }
        }//for_childrens

        if ($node_before) {
            $setting_node->add_node($competence_node,$node_before);
        }else {
            $setting_node->add_node($competence_node);
        }//if_node_before
    }//if_usercurrentsettings
}//local_microlearning_extends_settings_navigation