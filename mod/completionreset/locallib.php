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
 * Private Completion Reset Module utility functions
 *
 * @package mod_completionreset
 * @copyright  2015 Justin Hunt (http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/completionreset/lib.php");
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->libdir.'/formslib.php');


class mod_completionreset_chooseform extends moodleform {

    function definition() {
        global $SESSION;
		list($context,$renderedchooser,$resetusers) = $this->_customdata;
        $mform = $this->_form;
		$mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);
		$mform->addElement('hidden', MOD_COMPLETIONRESET_UPDATEFIELD);
        $mform->setType(MOD_COMPLETIONRESET_UPDATEFIELD, PARAM_TEXT);
		$mform->addElement('static', 'activitychooser',null, $renderedchooser);
        $mform->addElement('hidden', 'resetusers');
        $mform->setType('resetusers', PARAM_INT);
		//$mform->addElement('hidden','u');
		//$mform->setType('u',PARAM_INT);
		$mform->setDefault('resetusers',$resetusers);

        /**
         * Description
         * Add an extra functionality. Add my users to the completion reset
         *
         * @updateDate  29/03/2017
         * @author      eFaktor     (fbv)
         */
        //$this->add_action_buttons();
        // Buttons
        $buttons    = array();
        $strButton  = null;
        if ($resetusers) {
            $strButton = get_string('btn_reset','completionreset');
        }else {
            $strButton = get_string('savechanges');
        }
        $buttons[] = $mform->createElement('submit', 'savechanges', $strButton);
        // Check if has permissions
        //if ((mod_completionreset_helper::allow_choose_users($context->id) && (!$SESSION->addusers))) {
        //    $buttons[] = $mform->createElement('submit', 'users', get_string('add_users', 'completionreset'));
        //}

        $buttons[] = $mform->createElement('cancel');

        $mform->addGroup($buttons, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');
    }
}

class mod_completionreset_helper{

    /**
     * Description
     * Check if the user is instructor or teacher to add user in completion reset.
     *
     * @param           integer $context
     *
     * @return          bool|null
     * @throws          Exception
     *
     * @creationDate    29/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function allow_choose_users($context) {
        /* Variables */
        global $DB,$USER;
        $rdo    = null;
        $params = null;
        $sql    = null;
        $allow  = null;

        try {
            if (is_siteadmin($USER)) {
                $allow = true;
            }else {
                // Search criteria
                $params = array();
                $params['user']             = $USER->id;
                $params['context_course']   = CONTEXT_COURSE;
                $params['context_system']   = CONTEXT_SYSTEM;
                $params['context_cat']      = CONTEXT_COURSECAT;
                $params['context']          = $context;

                // SQL Instruction
                $sql = " SELECT	  ra.id
                         FROM	  mdl_role_assignments	ra
                            JOIN  mdl_role				r	ON 	r.id			= ra.roleid
                                                            AND	r.archetype		IN ('editingteacher','teacher')
                            JOIN  mdl_context		   	ct	ON	ct.id			= ra.contextid
                                                            AND	ct.contextlevel	IN (:context_course,:context_cat,:context_system)
                         WHERE	  ra.userid 	= :user
                            AND	  ra.contextid	= :context ";

                // Execute
                $rdo = $DB->get_records_sql($sql,$params);
                if ($rdo) {
                    $allow = true;
                }else {
                    $allow = false;
                }
            }//if_Admin

            return $allow;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//allow_choose_users

    /**
     * Description
     * Get users to add or remove from completion reset
     *
     * @param           object  $context
     * @param           integer $reset
     *
     * @return                  null|stdClass
     * @throws                  Exception
     *
     * @creationDate    29/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function choose_users_selectors($context,$reset) {
        /* Variables */
        $chooseusers    = null;

        try {
            // Choose users information
            $chooseusers = new stdClass();
            $chooseusers->course        = $context->instanceid;
            $chooseusers->reset         = $reset;
            // Get users available
            $chooseusers->availables    = self::get_potential_users_selector($context,$reset);
            // Get users already added
            $chooseusers->selected      = self::get_existing_users_selector($context->instanceid,$reset);
            // Sort
            $sort = array();
            if ($chooseusers->availables) {
                foreach ($chooseusers->availables as $key => $user) {
                    $sort[] = $key;
                }
            }//if_Availables
            if ($chooseusers->selected) {
                foreach ($chooseusers->selected as $key => $user) {
                    $sort[] = $key;
                }
            }//if_Availables

            $chooseusers->sort          = $sort;

            return $chooseusers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//choose_users_selectors

    /**
     * Description
     * Add users to completion reset module
     *
     * @param       integer $course
     * @param       integer $reset
     * @param       array   $selected
     *
     * @throws              Exception
     *
     * @creationDate        29/03/2017
     * @author              eFaktor     (fbv)
     */
    public static function add_users_completion_reset($course,$reset,$selected) {
        /* Variables */
        global $DB;
        $params     = null;
        $rdo        = null;
        $instance   = null;
        $trans      = null;
        $toadd      = null;

        // Begin transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // First deleted all existing
            $params = array();
            $params['course']  = $course;
            $params['resetid'] = $reset;
            $DB->delete_records('completionreset_users',$params);

            // Add new content
            if ($selected) {
                $toadd = explode(',',$selected);

                // Instance to add
                $instance = new stdClass();
                $instance->course = $course;
                $instance->resetid = $reset;

                foreach ($toadd as $user) {
                    $instance->userid = $user;

                    // Insert
                    $DB->insert_record('completionreset_users',$instance);
                }//for_selected
            }//if_selected

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//add_users_completion_reset


    public static function perform_reset($course,$resetusers=0) {
        /* Variables */
        global $USER;
        global $DB;
        $trans   = null;
        $toreset = array();
        $params  = null;
        $info    = null;
        $rdo     = null;


        try {
            if (!$resetusers) {
                // Current user
                $info = new stdClass();
                $info->userid = $USER->id;
                // Add current user
                $toreset[$USER->id] = $info;
            }else {
                // Get users
                $rdo = $DB->get_records('completionreset_users',array('course' => $course->id),'userid');
                if ($rdo) {
                    foreach ($rdo as $instance) {
                        $toreset[$instance->userid] = $instance;
                    }
                }
            }//if_resetusers

            if ($toreset) {
                //fetch activity list
                $allactivities = self::get_all_activities($course);

                // Completions
                $params = array();
                $params['course'] = $course->id;

                foreach ($toreset as $info) {
                    $params['userid'] = $info->userid;

                    //course completions table
                    $rec=$DB->get_record('course_completions',$params);
                    if($rec){
                        $data = new stdClass();
                        $data->id               = $rec->id;
                        $data->timecompleted    = null;
                        $data->reaggregate      = 0;
                        $DB->update_record('course_completions',$data);
                    }//if_course_completions

                    // reset_activities
                    self::perform_reset_activities($allactivities,$course->id,$info->userid);

                    $DB->delete_records('completionreset_users',$params);
                }//for_toreset


            }//if_toreset
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//perform_reset

	private static function perform_reset_activities($allactivities,$courseid,$userid){
        /* Variables */
		global $DB,$USER;
        $params = null;

        try {
            // Criteria
            $params = array();
            $params['userid'] = $userid;

            //course modules completion table
            $cmids = array();
            /**
             * @updateDate  13/06/2017
             * @author      eFaktor     (fbv)
             */
            // IT DOESN WORK FOR RESET USER SELECTED
            // WHEN IS THE SAME USER WHO RESET COMPLETION NEVER IS EXECUTED
            /**
            foreach($allactivities->chosencms as $cm){
                // Add module
                $cmids[]=$cm->id;

                // add criteria
                $params['coursemoduleid'] = $cm->id;
                $recs=$DB->get_records('course_modules_completion',$params);
                if($recs){
                    foreach($recs as $rec){
                        $data = new stdClass();
                        $data->id               = $rec->id;
                        $data->viewed           = 0;
                        $data->timemodified     = 0;
                        $data->completionstate  = 0;
                        $DB->update_record('course_modules_completion',$data);
                    }
                }

            }//if_all_activities
             * */


            //lets get a csv list of moduleids for bulk operations
            $cmids =implode(',',$cmids);

            //course completion crti compl table
            $recs=$DB->get_records_select('course_completion_criteria','course=:course AND moduleinstance IN (:cmids)',
                array('course'=>$courseid,'cmids'=>$cmids));
            if($recs){
                $params = array();
                $params['course'] = $courseid;
                foreach($recs as $rec){
                    $DB->delete_records('course_completion_crit_compl', array('course'=>$courseid,'userid'=>$userid,'criteriaid'=>$rec->id));
                }
            }

            //per activity type reset
            foreach($allactivities->chosencms as $cm){
                switch($cm->modname){
                    case 'lesson':  self::clear_lesson($cm,$userid); break;
                    case 'quiz':    self::clear_quiz($cm,$userid); break;
                    case 'scorm':   self::clear_scorm($cm,$userid); break;
                    case 'assign':  self::clear_assign($cm,$userid);  break;
                    default: //do nothing
                }
            }

            //delete all grades from gradebook
            //this caused a lot of trouble initially, so delegated grade deletion in most cases to the
            //per activity reset above.  But assign would not delete and it left a 0% in teh gradebook
            //so kills the gradebook entry for all the selected activities. The per activity reset still
            //deletes its grades.
            self::force_gradebook_clear($allactivities,$userid);

            //finally clear the completion cache, so that on page refresh, the changes are updated
            self::clear_completion_cache($courseid,$userid);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
	}//perform_reset_activities
	
	//This will clear the gradebook for a single activity, 
	static function force_gradebook_item_clear($cm,$userid){
		global $DB;

		try {
            $rec = $DB->get_record('grade_items',
                array('courseid'=>$cm->course,'itemmodule'=>$cm->modname,'iteminstance'=>$cm->instance));
            if(!$rec){return;}
            $itemid = $rec->id;

            $DB->delete_records_select('grade_grades','userid= :userid AND itemid = :itemid',
                array('userid'=>$userid,'itemid'=>$itemid));

            //delete all history
            $DB->delete_records_select('grade_grades_history','userid= :userid AND itemid = :itemid',
                array('userid'=>$userid,'itemid'=>$itemid));
        }catch (Exception $ex) {
		    throw $ex;
        }//try_catch
	}
	
	//This will clear the gradebook for all activities. 
	static function force_gradebook_clear($allactivities,$userid){
		global $DB;

		try {
            //delete from gradebook
            //this was the older logic, replaced in favor of a moodle function call
            //per activity to be reset
            $itemids = array();
            foreach($allactivities->chosencms as $cm){
                $rec = $DB->get_record('grade_items',
                    array('courseid'=>$cm->course,'itemmodule'=>$cm->modname,'iteminstance'=>$cm->instance));
                if($rec){
                    $itemids[]=$rec->id;
                }
            }

            $itemids_string = implode(',',$itemids);
            if(!empty($itemids)){
                $DB->delete_records_select('grade_grades','userid = :userid AND itemid IN ('.$itemids_string .')',
                    array('userid'=>$userid));
            }

            //delete all history
            if(!empty($itemids)){
                $DB->delete_records_select('grade_grades_history','userid = :userid AND itemid IN ('.$itemids_string .')',
                    array('userid'=>$userid));
            }
        }catch (Exception $ex) {
		    throw $ex;
        }//try_catch
	}
	
	//clear the completion cache,
	static function clear_completion_cache($courseid,$userid){
		global $SESSION;

		try {
            // Make sure cache is present and is for current user (loginas
            // changes this), then clear it
            if (isset($SESSION->completioncache) && $SESSION->completioncacheuserid==$userid) {
                unset($SESSION->completioncache[$courseid]);
            }
        }catch (Exception $ex) {
		    throw $ex;
        }//try_catch
	}
	
	//Reset a lesson
	static function clear_lesson($cm,$userid){
		global $DB;

		try {
            //echo 'clearing lesson: ' . $cm->name;
            $DB->delete_records('lesson_timer', array('lessonid'=>$cm->instance,'userid'=>$userid));
            $DB->delete_records('lesson_high_scores', array('lessonid'=>$cm->instance,'userid'=>$userid));
            $DB->delete_records('lesson_grades', array('lessonid'=>$cm->instance,'userid'=>$userid));
            $DB->delete_records('lesson_attempts', array('lessonid'=>$cm->instance,'userid'=>$userid));
            $DB->delete_records('lesson_branch', array('lessonid'=>$cm->instance,'userid'=>$userid));
            //update gradebook ---- this doesn't work
            //the assignment dont make this easy
            $lesson = $DB->get_record('lesson',array('id'=>$cm->instance));
            lesson_update_grades($lesson, $userid);
        }catch (Exception $ex) {
		    throw $ex;
        }//try_catch
	}
	
	//Reset a quiz
	/**
	 * @param 	$cm
	 *
	 * @updateDate	21/09/2016
	 * @author		eFaktor		(fbv)
	 *
	 * Description
	 * Add quiz id as search criteria
	 */
	static function clear_quiz($cm,$userid){
		global $CFG, $DB;

		try {
            require_once($CFG->libdir . '/questionlib.php');
            require_once($CFG->dirroot . '/question/engine/datalib.php');

            // Delete attempts.
            question_engine::delete_questions_usage_by_activities(new qubaid_join(
                '{quiz_attempts} quiza JOIN {quiz} quiz ON quiza.quiz = quiz.id AND quiz.id = :quizid',
                'quiza.uniqueid', 'quiz.course = :quizcourseid AND quiza.userid = :userid',
                array('quizcourseid' => $cm->course, 'userid'=>$userid,'quizid' =>$cm->instance)));

            $DB->delete_records_select('quiz_attempts',
                'quiz = :quizid AND userid = :userid', array('quizid'=>$cm->instance,'userid'=>$userid));


            // Remove all grades from gradebook.
            $DB->delete_records_select('quiz_grades',
                'quiz = :quizid AND userid = :userid', array('quizid'=>$cm->instance,'userid'=>$userid));

            //update gradebook
            $quiz = $DB->get_record('quiz',array('id'=>$cm->instance));
            quiz_update_grades($quiz, $userid);
        }catch (Exception $ex) {
		    throw $ex;
        }//try_Catch
	}
	
	//Reset a scorm
	static function clear_scorm($cm,$userid){
		global $DB,$CFG;

		try {
            $scorm = $DB->get_record('scorm',array('id'=>$cm->instance));
            $attempts = $DB->get_records('scorm_scoes_track', array('userid' => $userid, 'scormid' => $scorm->id));
            if($attempts){
                require_once("$CFG->dirroot/mod/scorm/locallib.php");
                foreach($attempts as $attempt){
                    scorm_delete_attempt($userid, $scorm, $attempt->attempt);
                }
            }
        }catch (Exception $ex) {
		    throw $ex;
        }//try_catch
	}
	
	//Reset an assignmnet
	static function clear_assign($cm,$userid){
		   global $DB;

		   try {
               $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
               $context = context_module::instance($cm->id);
               $assignment = new resettable_assign($context, $cm, $course);
               $assignment->reset_single_user($userid);
           }catch (Exception $ex) {
		       throw $ex;
           }//try_catch
	}

	static function get_all_activities($course){
		global $DB;

		try {
            $include = array('quiz','scorm','assign','page','book','url','lesson','resource');
            $exclude = array('completionreset','label');

            $rec = $DB->get_record(MOD_COMPLETIONRESET_ACTIVITIESTABLE,array('course'=>$course->id));
            if($rec){
                $activities = explode(',',$rec->activities);
            }else{
                $activities=array();
            }
            $modinfo = get_fast_modinfo($course);
            $cms = $modinfo->get_cms();
            $unchosendata = array();
            $chosendata = array();
            $chosencms=array();
            $sortorderarray = array();

            foreach($cms as $cm){
                //excluded mod types
                if(in_array($cm->modname,$exclude)){
                    continue;
                }
                if(!in_array($cm->modname,$include)){
                    continue;
                }
                //old logic / not good enough for things like choice
                /*
                $supportfunction = $cm->modname. '_supports';
                if($supportfunction(FEATURE_COMPLETION_HAS_RULES)===true ||
                    $supportfunction(FEATURE_GRADE_HAS_GRADE)===true){
                    if(!in_array($cm->modname,$include)){
                        $exclude[] = $cm->modname;
                        continue;
                    }
                }
                */
                if(in_array($cm->id,$activities)){
                    $chosendata[$cm->id]=$cm->name;
                    $chosencms[]=$cm;
                }else{
                    $unchosendata[$cm->id]=$cm->name;
                }
                $sortorderarray[]=$cm->id;
            }

            $ret = new stdClass();
            $ret->sortorderarray=$sortorderarray;
            $ret->chosendata=$chosendata;
            $ret->unchosendata=$unchosendata;
            $ret->chosencms=$chosencms;
            return $ret;
        }catch (Exception $ex) {
		    throw $ex;
        }//try_catch
	}
	
	//Thought we needed this, but we do not
	static function set_completionreset_availability($courseid,$available){
		global $DB;

		try {
            $modinfo = get_fast_modinfo($courseid);
            $cms = $modinfo->get_cms();
            foreach($cms as $cm){
                if($cm->modname=='completionreset'){
                    $cm->set_available($available,1,'');
                }
            }
            /*
            $rec = $DB->get_record('modules',array('name'=>'completionreset'));
            if($rec){
                $DB->set_field('course_modules', 'visible', $visible, array('course'=>$courseid,'module'=>$rec->id));
            }
            */
        }catch (Exception $ex) {
		    throw $ex;
        }//try_catch
	}

	/***********/
	/* PRIVATE */
	/***********/

    /**
     * Description
     * Get all users that have already added
     *
     * @param       integer $course
     * @param       integer $reset
     *
     * @return              null
     * @throws              Exception
     *
     * @creationDate    29/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_existing_users_selector($course,$reset) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        $lstusers   = array();

        try {
            // Search criteria
            $params = array();
            $params['reset']    = $reset;
            $params['course']   = $course;

            // SQL Instruction
            $sql = " SELECT  DISTINCT
                                u.id,
                                u.firstname,
                                u.lastname,
                                u.email
                     FROM	  {user}				  u
                        JOIN  {user_enrolments}		  ue	ON  ue.userid 		= u.id
                        JOIN  {completionreset_users} cru	ON 	cru.userid		= ue.userid
                                                            AND	cru.resetid		= :reset
                                                            AND cru.course		= :course
                     WHERE 	  u.deleted   = 0
                        AND   u.username != 'guest'
                     ORDER BY u.firstname,u.lastname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lstusers[$instance->id] = $instance->firstname . " " . $instance->lastname . " (" . $instance->email . ")";
                }//for_rdo
            }//if_Rdo

            return $lstusers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_existing_users_selector

    /**
     * Description
     * Get all potential users to add in the completion reset
     *
     * @param       object  $context
     * @param       integer $reset
     *
     * @return              null
     * @throws              Exception
     *
     * @creationDate    29/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_potential_users_selector($context,$reset) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        $lstusers   = array();

        try {
            // Search criteria
            $params = array();
            $params['reset']        = $reset;
            $params['context']      = $context->id;
            $params['course']       = $context->instanceid;

            // SQL Instruction
            $sql = " SELECT  	DISTINCT
                                  u.id,
                                  u.firstname,
                                  u.lastname,
                                  u.email
                     FROM		  {user}				  u
                        JOIN	  {user_enrolments}		  ue  ON  ue.userid     = u.id
                        JOIN	  {enrol}				  e	  ON  e.id 	  	    = ue.enrolid
                                                              AND e.courseid    = :course
                        JOIN	  {role_assignments}	  ra  ON  ra.userid     = ue.userid
                                                              AND ra.contextid	= :context
                        JOIN	  {role}				  r	  ON  r.id			= ra.roleid
                                                              AND r.archetype	= 'student'
                        LEFT JOIN {completionreset_users} cru ON  cru.course	= e.courseid
                                                              AND cru.userid	= ra.userid
                                                              AND cru.resetid	= :reset
                     WHERE 	u.deleted   = 0
                        AND u.username != 'guest'
                        AND cru.id IS NULL 
                     ORDER BY u.firstname,u.lastname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lstusers[$instance->id] = $instance->firstname . " " . $instance->lastname . " (" . $instance->email . ")";
                }//for_rdo
            }//if_rdo

            return $lstusers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_potential_users_selector
	
}
/**
 * File browsing support class
 */
class completionreset_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}

/**
 * Adds a function to the assignment to delete by user
 *
 * @updateDate  29/03/2017
 * @author      eFaktor (fbv)
 */
class resettable_assign extends assign {
    private $course_module;

	public function __construct($coursemodulecontext, $coursemodule, $course)
    {
        $this->course_module = $coursemodule;

        // call parent constructor
        parent::__construct($coursemodulecontext, $coursemodule, $course);
        // Set context
        $this->set_context($coursemodulecontext);
        // Set course
        $this->set_course($course);


        // Ensure that $this->coursemodule is a cm_info object (or null).
        //--this for Moodle 2.8 but not for Moodle 2.7
        // $this->coursemodule = cm_info::create($coursemodule);
        /**
        if($coursemodule instanceof cm_info){
        	$this->coursemodule = $coursemodule;
        }else{
       		$modinfo = get_fast_modinfo($course);
	   		$this->coursemodule = $modinfo->get_cm($coursemodule->id);
	    }**/
       

        // Temporary cache only lives for a single request - used to reduce db lookups.
        //$this->set_cache = array();

        //$this->submissionplugins = $this->load_plugins('assignsubmission');
        //$this->feedbackplugins = $this->load_plugins('assignfeedback');
    }

 	/**
     * Actual implementation of the reset course functionality, delete all the
     * assignment submissions for course $data->courseid.
     *
     * @param stdClass $data the data submitted from the reset course.
     */
    public function reset_single_user($userid) {
        global $DB;

        try {
            //get instance
            $instance = $this->get_instance();

            //first delete all submission related files
            $fs = get_file_storage();
            $submissions = $DB->get_records('assign_submission',array('assignment'=>$instance->id,'userid'=>$userid));
            if($submissions){
                foreach($submissions as $submission){
                    // Delete files associated with this assignment.
                    foreach ($this->get_submission_plugins as $plugin) {
                        $fileareas = array();
                        $plugincomponent = $plugin->get_subtype() . '_' . $plugin->get_type();
                        $fileareas = $plugin->get_file_areas();
                        foreach ($fileareas as $filearea => $notused) {
                            $fs->delete_area_files($this->get_context()->id, $plugincomponent, $filearea,$submission->id);
                        }
                    }

                    foreach ($this->get_feedback_plugins as $plugin) {
                        $fileareas = array();
                        $plugincomponent = $plugin->get_subtype() . '_' . $plugin->get_type();
                        $fileareas = $plugin->get_file_areas();
                        foreach ($fileareas as $filearea => $notused) {
                            $fs->delete_area_files($this->get_context()->id, $plugincomponent, $filearea,$submission->id);
                        }
                    }
                }
            }
            //then delete all DB entries related to this user for this assignment
            $DB->delete_records('assign_submission',array('assignment'=>$instance->id,'userid'=>$userid));
            $DB->delete_records('assign_user_flags', array('assignment'=>$instance->id,'userid'=>$userid));
            $DB->delete_records('assign_grades', array('assignment'=>$instance->id,'userid'=>$userid));
            //this is not done by assign module on course reset, so don't do it here. But it might be necessary
            //$DB->delete_records('assign_user_mapping', array('assignment'=>$this->id,'userid'=>$userid));

            //update gradebook
            $instance->cmidnumber =  $this->course_module->id;
            assign_update_grades($instance, $userid);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }

}