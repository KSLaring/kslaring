<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External functions and service definitions.
 *
 * @package    local_mobile
 * @copyright  2014 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(
    'local_mobile_get_plugin_settings' => array(
        'classname'   => 'local_mobile_external',
        'methodname'  => 'get_plugin_settings',
        'classpath'   => 'local/mobile/externallib.php',
        'description' => 'Retrieve the plugin settings.',
        'type'        => 'read',
    ),

    'local_mobile_mod_quiz_get_quizzes_by_courses' => array(
        'classname'     => 'local_mobile_external',
        'methodname'    => 'mod_quiz_get_quizzes_by_courses',
        'description'   => 'Returns a list of quizzes in a provided list of courses,
                            if no list is provided all quizzes that the user can view will be returned.',
        'type'          => 'read',
        'capabilities'  => 'mod/quiz:view',
    ),

    'local_mobile_mod_quiz_get_attempt_data' => array(
        'classname'     => 'local_mobile_external',
        'methodname'    => 'mod_quiz_get_attempt_data',
        'description'   => 'Returns information for the given attempt page for a quiz attempt in progress.',
        'type'          => 'read',
        'capabilities'  => 'mod/quiz:attempt',
    ),

    'local_mobile_mod_quiz_start_attempt' => array(
        'classname'     => 'local_mobile_external',
        'methodname'    => 'mod_quiz_start_attempt',
        'description'   => 'Starts a new attempt at a quiz.',
        'type'          => 'write',
        'capabilities'  => 'mod/quiz:attempt',
    ),

    'local_mobile_mod_quiz_save_attempt' => array(
        'classname'     => 'local_mobile_external',
        'methodname'    => 'mod_quiz_save_attempt',
        'description'   => 'Processes save requests during the quiz.
                            This function is intended for the quiz auto-save feature.',
        'type'          => 'write',
        'capabilities'  => 'mod/quiz:attempt',
    ),
    'local_mobile_mod_quiz_process_attempt' => array(
        'classname'     => 'local_mobile_external',
        'methodname'    => 'mod_quiz_process_attempt',
        'description'   => 'Process responses during an attempt at a quiz and also deals with attempts finishing.',
        'type'          => 'write',
        'capabilities'  => 'mod/quiz:attempt',
    ),
      'local_mobile_mod_assign_view_assign' => array(
            'classname'     => 'local_mobile_external',
            'methodname'    => 'mod_assign_view_assign',
            'classpath'     => 'local/mobile/externallib.php',
            'description'   => 'Update the module completion status.',
            'type'          => 'write',
            'capabilities'  => 'mod/assign:view',
      ),
      'local_mobile_core_course_get_user_navigation_options' => array(
            'classname' => 'local_mobile_external',
            'methodname' => 'core_course_get_user_navigation_options',
            'classpath' => 'local/mobile/externallib.php',
            'description' => 'Return a list of navigation options in a set of courses that are avaialable or not for the current user.',
            'type' => 'read',
      ),
      'local_mobile_core_course_get_user_administration_options' => array(
            'classname' => 'local_mobile_external',
            'methodname' => 'core_course_get_user_administration_options',
            'classpath' => 'local/mobile/externallib.php',
            'description' => 'Return a list of administration options in a set of courses that are avaialable or not for the current
                            user.',
            'type' => 'read',
      ),
      'local_mobile_core_user_update_picture' => array(
            'classname' => 'local_mobile_external',
            'methodname' => 'core_user_update_picture',
            'classpath' => 'local/mobile/externallib.php',
            'description' => 'Update or delete the user picture in the site',
            'type' => 'write',
            'capabilities' => 'moodle/user:editownprofile, moodle/user:editprofile',
      ),
      'local_mobile_tool_mobile_get_config' => array(
            'classname'   => 'local_mobile_external',
            'methodname'  => 'tool_mobile_get_config',
            'classpath' => 'local/mobile/externallib.php',
            'description' => 'Returns a list of the site configurations, filtering by section.',
            'type'        => 'read',
      ),
    'local_mobile_core_course_get_courses_by_field' => array(
        'classname' => 'local_mobile_external',
        'methodname' => 'core_course_get_courses_by_field',
        'classpath' => 'local/mobile/externallib.php',
        'description' => 'Get courses matching a specific field (id/s, shortname, idnumber, category)',
        'type' => 'read',
    ),
);

$services = array(
   'Moodle Mobile additional features service'  => array(
        'functions' => array (
            'core_badges_get_user_badges',
            'core_calendar_get_calendar_events',
            'core_comment_get_comments',
            'core_competency_list_course_competencies',
            'core_competency_grade_competency_in_course',
            'core_competency_get_scale_values',
            'core_competency_delete_evidence',
            'core_competency_competency_viewed',
            'core_competency_user_competency_viewed',
            'core_competency_user_competency_viewed_in_plan',
            'core_competency_user_competency_viewed_in_course',
            'core_competency_user_competency_plan_viewed',
            'core_completion_get_activities_completion_status',
            'core_completion_get_course_completion_status',
            'core_completion_mark_course_self_completed',
            'core_completion_update_activity_completion_status_manually',
            'core_course_get_contents',
            'core_course_get_course_module',
            'core_course_get_course_module_by_instance',
            'core_course_get_courses',
            'core_course_search_courses',
            'core_course_view_course',
            'core_enrol_get_enrolled_users',
            'core_enrol_get_users_courses',
            'core_enrol_get_course_enrolment_methods',
            'core_get_component_strings',   // Don't remove this, the app relies on this to check the min version.
            'core_group_get_activity_allowed_groups',
            'core_group_get_activity_groupmode',
            'core_group_get_course_user_groups',
            'core_files_get_files',
            'core_message_block_contacts',
            'core_message_create_contacts',
            'core_message_delete_contacts',
            'core_message_get_blocked_users',
            'core_message_get_contacts',
            'core_message_get_messages',
            'core_message_mark_message_read',
            'core_message_search_contacts',
            'core_notes_delete_notes',
            'core_message_send_instant_messages',
            'core_message_unblock_contacts',
            'core_message_delete_message',
            'core_notes_create_notes',
            'core_notes_get_course_notes',
            'core_notes_view_notes',
            'core_rating_get_item_ratings',
            'core_user_add_user_device',
            'core_user_add_user_private_files',
            'core_user_get_course_user_profiles',
            'core_user_get_users_by_field',
            'core_user_get_users_by_id',
            'core_user_remove_user_device',
            'core_user_view_user_list',
            'core_user_view_user_profile',
            'core_webservice_get_site_info',
            'enrol_self_enrol_user',
            'enrol_self_get_instance_info',
            'enrol_guest_get_instance_info',
            'get_plugin_settings',
            'gradereport_user_get_grades_table',
            'gradereport_user_view_grade_report',
            'message_airnotifier_are_notification_preferences_configured',
            'message_airnotifier_is_system_configured',
            'mod_assign_get_assignments',
            'mod_assign_get_submission_status',
            'mod_assign_get_submissions',
            'mod_assign_save_submission',
            'mod_assign_submit_for_grading',
            'mod_assign_view_grading_table',
            'mod_assign_list_participants',
            'mod_assign_view_submission_status',
            'mod_assign_get_user_mappings',
            'mod_assign_submit_grading_form',
            'mod_assign_get_participant',
            'mod_assign_get_grades',
            'mod_assign_save_grade',
            'mod_assign_save_grades',
            'mod_book_view_book',
            'mod_chat_get_chat_latest_messages',
            'mod_chat_get_chat_users',
            'mod_chat_get_chats_by_courses',
            'mod_chat_login_user',
            'mod_chat_send_chat_message',
            'mod_chat_view_chat',
            'mod_choice_delete_choice_responses',
            'mod_choice_get_choice_options',
            'mod_choice_get_choice_results',
            'mod_choice_get_choices_by_courses',
            'mod_choice_submit_choice_response',
            'mod_choice_view_choice',
            'mod_data_get_databases_by_courses',
            'mod_folder_view_folder',
            'mod_forum_add_discussion',
            'mod_forum_add_discussion_post',
            'mod_forum_can_add_discussion',
            'mod_forum_get_forums_by_courses',
            'mod_forum_get_forum_discussions_paginated',
            'mod_forum_get_forum_discussion_posts',
            'mod_forum_view_forum',
            'mod_forum_view_forum_discussion',
            'mod_glossary_get_glossaries_by_courses',
            'mod_glossary_view_glossary',
            'mod_glossary_view_entry',
            'mod_glossary_get_entries_by_letter',
            'mod_glossary_get_entries_by_date',
            'mod_glossary_get_categories',
            'mod_glossary_get_entries_by_category',
            'mod_glossary_get_authors',
            'mod_glossary_get_entries_by_author',
            'mod_glossary_get_entries_by_author_id',
            'mod_glossary_get_entries_by_search',
            'mod_glossary_get_entries_by_term',
            'mod_glossary_get_entries_to_approve',
            'mod_glossary_get_entry_by_id',
            'mod_imscp_view_imscp',
            'mod_lti_get_ltis_by_courses',
            'mod_lti_get_tool_launch_data',
            'mod_lti_view_lti',
            'mod_page_view_page',
            'local_mobile_core_course_get_user_navigation_options',
            'local_mobile_core_course_get_user_administration_options',
            'local_mobile_core_user_update_picture',
            'local_mobile_mod_assign_view_assign',
            'local_mobile_mod_quiz_get_quizzes_by_courses',
            'mod_quiz_view_quiz',
            'mod_quiz_get_user_attempts',
            'mod_quiz_get_user_best_grade',
            'mod_quiz_get_combined_review_options',
            'local_mobile_mod_quiz_start_attempt',
            'local_mobile_mod_quiz_get_attempt_data',
            'mod_quiz_get_attempt_summary',
            'local_mobile_mod_quiz_save_attempt',
            'local_mobile_mod_quiz_process_attempt',
            'mod_quiz_get_attempt_review',
            'mod_quiz_view_attempt',
            'mod_quiz_view_attempt_summary',
            'mod_quiz_view_attempt_review',
            'mod_quiz_get_quiz_feedback_for_grade',
            'mod_quiz_get_quiz_access_information',
            'mod_quiz_get_attempt_access_information',
            'mod_quiz_get_quiz_required_qtypes',
            'mod_resource_view_resource',
            'mod_scorm_get_scorm_attempt_count',
            'mod_scorm_get_scorm_sco_tracks',
            'mod_scorm_get_scorm_scoes',
            'mod_scorm_get_scorm_user_data',
            'mod_scorm_get_scorms_by_courses',
            'mod_scorm_insert_scorm_tracks',
            'mod_scorm_launch_sco',
            'mod_scorm_view_scorm',
            'mod_survey_get_questions',
            'mod_survey_get_surveys_by_courses',
            'mod_survey_submit_answers',
            'mod_survey_view_survey',
            'mod_url_view_url',
            'mod_wiki_get_wikis_by_courses',
            'mod_wiki_view_wiki',
            'mod_wiki_view_page',
            'mod_wiki_get_subwikis',
            'mod_wiki_get_subwiki_pages',
            'mod_wiki_get_page_contents',
            'mod_wiki_get_subwiki_files',
            'mod_wiki_get_page_for_editing',
            'mod_wiki_new_page',
            'mod_wiki_edit_page',
            'local_mobile_get_plugin_settings',
            'local_mobile_tool_mobile_get_config',
            'local_mobile_core_course_get_courses_by_field',
            'core_course_get_categories',
            'tool_lp_data_for_course_competencies_page',
            'tool_lp_data_for_plans_page',
            'tool_lp_data_for_plan_page',
            'tool_lp_data_for_user_evidence_list_page',
            'tool_lp_data_for_user_evidence_page',
            'tool_lp_data_for_user_competency_summary',
            'tool_lp_data_for_user_competency_summary_in_plan',
            'tool_lp_data_for_user_competency_summary_in_course',
            'tool_lp_data_for_course_competencies_page',
            'tool_mobile_get_plugins_supporting_mobile',
        ),
        'enabled' => 0,
        'restrictedusers' => 0,
        'shortname' => 'local_mobile',
        'downloadfiles' => 1,
        'uploadfiles' => 1
    ),
);