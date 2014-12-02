<?php

function local_tracker_extends_navigation(global_navigation $navigation) {
    global $USER;

    if (isloggedin()) {
        $nodeTracker = $navigation->add(get_string('name','local_tracker'), new moodle_url('/report/generator/tracker/index.php'));

        if (is_siteadmin($USER->id)) {
            $nodBar = $nodeTracker->add(get_string('report_generator','local_tracker'),new moodle_url('/report/generator/index.php'));
            $nodBar = $nodeTracker->add(get_string('company_structure','local_tracker'),new moodle_url('/report/generator/company_structure/company_structure.php'));
            $nodBar = $nodeTracker->add(get_string('outcome_report','local_tracker'),new moodle_url('/report/generator/outcome_report/outcome_report.php'));
            $nodBar = $nodeTracker->add(get_string('course_report','local_tracker'),new moodle_url('/report/generator/course_report/course_report.php'));
            $nodBar = $nodeTracker->add(get_string('job_roles','local_tracker'),new moodle_url('/report/generator/job_role/job_role.php'));
            $nodBar = $nodeTracker->add(get_string('outcomes','local_tracker'),new moodle_url('/report/generator/outcome/outcome.php'));
            $nodBar = $nodeTracker->add(get_string('outcome_area_title','local_tracker'),new moodle_url('/grade/edit/outcome/index.php'));
        }else {
            if (has_capability('report/generator:viewlevel4', CONTEXT_SYSTEM::instance())) {
                $nodBar = $nodeTracker->add(get_string('report_generator','local_tracker'),new moodle_url('/report/generator/index.php'));
            }
        }//if_else
    }
}//tracker_extends_navigation
