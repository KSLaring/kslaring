<?php

function local_tracker_manager_extends_navigation(global_navigation $navigation) {
    global $USER;

    if (isloggedin()) {
        $nodeTracker = $navigation->add(get_string('name','local_tracker_manager'), new moodle_url('/report/manager/tracker/index.php'));

        if (is_siteadmin($USER->id)) {
            $nodBar = $nodeTracker->add(get_string('report_manager','local_tracker_manager'),new moodle_url('/report/manager/index.php'));
            $nodBar = $nodeTracker->add(get_string('outcome_report','local_tracker_manager'),new moodle_url('/report/manager/outcome_report/outcome_report.php'));
            $nodBar = $nodeTracker->add(get_string('course_report','local_tracker_manager'),new moodle_url('/report/manager/course_report/course_report.php'));
            $nodBar = $nodeTracker->add(get_string('outcome_area_title','local_tracker_manager'),new moodle_url('/grade/edit/outcome/index.php'));
			$nodBar = $nodeTracker->add(get_string('company_structure','local_tracker_manager'),new moodle_url('/report/manager/company_structure/company_structure.php'));
            $nodBar = $nodeTracker->add(get_string('job_roles','local_tracker_manager'),new moodle_url('/report/manager/job_role/job_role.php'));
            $nodBar = $nodeTracker->add(get_string('outcomes','local_tracker_manager'),new moodle_url('/report/manager/outcome/outcome.php'));
        }else {
            if (has_capability('report/manager:viewlevel4', CONTEXT_SYSTEM::instance())) {
                $nodBar = $nodeTracker->add(get_string('report_manager','local_tracker_manager'),new moodle_url('/report/manager/index.php'));
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
function local_tracker_manager_extends_settings_navigation($settingsnav, $context) {
    /* Variables    */
    global $USER,$ADMIN;
    $url                = null;
    $strTitle           = null;
    $nodeBefore         = null;
    $settingNode        = null;
    $competenceNode     = null;
    $competenceImpNode  = null;

    if ($settingNode = $settingsnav->get('usercurrentsettings')) {
        $strTitle   = get_string('competence_profile', 'profilefield_competence');
        $url        = new moodle_url('/user/profile/field/competence/competence.php',array('id' =>$USER->id));

        /* Create Node  */
        $competenceNode = navigation_node::create($strTitle,
                                                   $url,
                                                   navigation_node::TYPE_SETTING,'competence_profile',
                                                   'competence_profile',
                                                   null);
        /* Find the position to add the link    */
        foreach ($settingNode->children as $child) {
            if ($child->text == get_string('changepassword')) {
                $nodeBefore = $child->key;
            }
        }//for_childrens

        if ($nodeBefore) {
            $settingNode->add_node($competenceNode,$nodeBefore);
        }else {
            $settingNode->add_node($competenceNode);
        }//if_node_before
    }//if_usercurrentsettings
}//local_microlearning_extends_settings_navigation