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
        global $DB;
		list($renderedchooser) = $this->_customdata;
        $mform = $this->_form;
		$mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);
		$mform->addElement('hidden', MOD_COMPLETIONRESET_UPDATEFIELD);
        $mform->setType(MOD_COMPLETIONRESET_UPDATEFIELD, PARAM_TEXT);
		$mform->addElement('static', 'activitychooser',null, $renderedchooser);
        $this->add_action_buttons();
    }
}

class mod_completionreset_helper{

	static function perform_reset($course){
		global $DB,$USER,$CFG;

		//course completions table
		$rec=$DB->get_record('course_completions',array('userid'=>$USER->id,'course'=>$course->id));
		if($rec){
			$data = new stdClass();
			$data->id=$rec->id;
			$data->timecompleted=null;
			$data->reaggregate=0;
			$DB->update_record('course_completions',$data);
		}
		
		//fetch activity list
		$allactivities = self::get_all_activities($course);
		
		
		//course modules completion table
		$cmids = array();
		foreach($allactivities->chosencms as $cm){
			$cmids[]=$cm->id;
			$recs=$DB->get_records('course_modules_completion',array('userid'=>$USER->id,'coursemoduleid'=>$cm->id));
			if($recs){
				foreach($recs as $rec){
					$data = new stdClass();
					$data->id=$rec->id;
					$data->viewed=0;
					$data->timemodified=0;
					$data->completionstate=0;
					$DB->update_record('course_modules_completion',$data);
				}
			}
		}
		
		//lets get a csv list of moduleids for bulk operations
		$cmids =implode(',',$cmids);
		
		//course completion crti compl table
		//is this right?
		//$DB->delete_records('course_completion_crit_compl',array('course'=>$course->id,'userid'->$USER->id));
		//or
		$recs=$DB->get_records_select('course_completion_criteria','course=:course AND moduleinstance IN (:cmids)',
						array('course'=>$course->id,'cmids'=>$cmids));
		if($recs){
			foreach($recs as $rec){
				$DB->delete_records('course_completion_crit_compl',
						array('course'=>$course->id,'userid'=>$USER->id,'criteriaid'=>$rec->id));
			}
		}

		//per activity type reset
		foreach($allactivities->chosencms as $cm){
			switch($cm->modname){
				case 'lesson': self::clear_lesson($cm); break;
				case 'quiz': self::clear_quiz($cm); break;
				case 'scorm': self::clear_scorm($cm); break;
				case 'assign': self::clear_assign($cm);  break;
				default: //do nothing
			}
		}
		
		//delete all grades from gradebook
		//this caused a lot of trouble initially, so delegated grade deletion in most cases to the
		//per activity reset above.  But assign would not delete and it left a 0% in teh gradebook
		//so kills the gradebook entry for all the selected activities. The per activity reset still
		//deletes its grades. 
		self::force_gradebook_clear($allactivities);

		//finally clear the completion cache, so that on page refresh, the changes are updated
		self::clear_completion_cache($course->id);
	}
	
	//This will clear the gradebook for a single activity, 
	static function force_gradebook_item_clear($cm){
		global $USER,$CFG,$DB;

		$rec = $DB->get_record('grade_items',
					array('courseid'=>$cm->course,'itemmodule'=>$cm->modname,'iteminstance'=>$cm->instance));
		if(!$rec){return;}
		$itemid = $rec->id;

		$DB->delete_records_select('grade_grades','userid= :userid AND itemid = :itemid',
					array('userid'=>$USER->id,'itemid'=>$itemid));

		
		//delete all history
		$DB->delete_records_select('grade_grades_history','userid= :userid AND itemid = :itemid',
					array('userid'=>$USER->id,'itemid'=>$itemid));

	}
	
	//This will clear the gradebook for all activities. 
	static function force_gradebook_clear($allactivities){
		global $USER,$CFG,$DB;

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
						array('userid'=>$USER->id));
		}
		
		//delete all history
		if(!empty($itemids)){
			$DB->delete_records_select('grade_grades_history','userid = :userid AND itemid IN ('.$itemids_string .')',
						array('userid'=>$USER->id));
		}
	}
	
	//clear the completion cache,
	static function clear_completion_cache($courseid){
		global $SESSION,$USER;
		// Make sure cache is present and is for current user (loginas
            // changes this), then clear it
            if (isset($SESSION->completioncache) && $SESSION->completioncacheuserid==$USER->id) {
                unset($SESSION->completioncache[$courseid]);
            }
	}
	
	//Reset a lesson
	static function clear_lesson($cm){
		global $DB,$USER;
		//echo 'clearing lesson: ' . $cm->name;
        $DB->delete_records('lesson_timer', array('lessonid'=>$cm->instance,'userid'=>$USER->id));
        $DB->delete_records('lesson_high_scores', array('lessonid'=>$cm->instance,'userid'=>$USER->id));
        $DB->delete_records('lesson_grades', array('lessonid'=>$cm->instance,'userid'=>$USER->id));
        $DB->delete_records('lesson_attempts', array('lessonid'=>$cm->instance,'userid'=>$USER->id));
        $DB->delete_records('lesson_branch', array('lessonid'=>$cm->instance,'userid'=>$USER->id));
        //update gradebook ---- this doesn't work
        //the assignment dont make this easy
        $lesson = $DB->get_record('lesson',array('id'=>$cm->instance));
        lesson_update_grades($lesson, $USER->id);
	}
	
	//Reset a quiz
	static function clear_quiz($cm){
		global $CFG, $DB,$USER;
		//echo 'clearing quiz: ' . $cm->name;
		require_once($CFG->libdir . '/questionlib.php');
		require_once($CFG->dirroot . '/question/engine/datalib.php');
		// Delete attempts.
		question_engine::delete_questions_usage_by_activities(new qubaid_join(
				'{quiz_attempts} quiza JOIN {quiz} quiz ON quiza.quiz = quiz.id',
				'quiza.uniqueid', 'quiz.course = :quizcourseid AND quiza.userid = :userid',
				array('quizcourseid' => $cm->course, 'userid'=>$USER->id)));

		$DB->delete_records_select('quiz_attempts',
				'quiz = :quizid AND userid = :userid', array('quizid'=>$cm->instance,'userid'=>$USER->id));


		// Remove all grades from gradebook.
		$DB->delete_records_select('quiz_grades',
				'quiz = :quizid AND userid = :userid', array('quizid'=>$cm->instance,'userid'=>$USER->id));
				
		//update gradebook
        $quiz = $DB->get_record('quiz',array('id'=>$cm->instance));
        quiz_update_grades($quiz, $USER->id);
	}
	
	//Reset a scorm
	static function clear_scorm($cm){
		global $DB,$CFG,$USER;
		//echo 'clearing scorm: ' . $cm->name;
		$scorm = $DB->get_record('scorm',array('id'=>$cm->instance));
		$attempts = $DB->get_records('scorm_scoes_track', array('userid' => $USER->id, 'scormid' => $scorm->id));
		if($attempts){
			require_once("$CFG->dirroot/mod/scorm/locallib.php");
			foreach($attempts as $attempt){
				scorm_delete_attempt($USER->id, $scorm, $attempt->attempt);
			}
		}

		//This was the older SQL based method. Did not seem to delete eveything. Safer to lean on Scorm mod
		/*
        $DB->delete_records_select('scorm_scoes_track', "scormid=:scormid AND userid=:userid", 
        		array('scormid'=>$cm->instance,'userid'=>$USER->id));
        */
	
	}
	
	//Reset an assignmnet
	static function clear_assign($cm){
		   global $CFG, $DB, $USER;
		   // echo 'clearing assign: ' . $cm->name;
			$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
            $context = context_module::instance($cm->id);
            $assignment = new resettable_assign($context, $cm, $course);
			$assignment->reset_single_user($USER->id);
	}

	static function get_all_activities($course){
		global $DB;
		
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
	}
	
	//Thought we needed this, but we do not
	static function set_completionreset_availability($courseid,$available){
		global $DB;
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
	}
	
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
 */
class resettable_assign extends assign {

	public function __construct($coursemodulecontext, $coursemodule, $course)
    {
        // call parent constructor
        parent::__construct($coursemodulecontext, $coursemodule, $course);
        $this->context = $coursemodulecontext;
        $this->course = $course;

        // Ensure that $this->coursemodule is a cm_info object (or null).
        //--this for Moodle 2.8 but not for Moodle 2.7
        // $this->coursemodule = cm_info::create($coursemodule);
        if($coursemodule instanceof cm_info){ 
        	$this->coursemodule = $coursemodule;
        }else{
       		$modinfo = get_fast_modinfo($course);
	   		$this->coursemodule = $modinfo->get_cm($coursemodule->id);
	    }
       

        // Temporary cache only lives for a single request - used to reduce db lookups.
        $this->cache = array();

        $this->submissionplugins = $this->load_plugins('assignsubmission');
        $this->feedbackplugins = $this->load_plugins('assignfeedback');
    }

 	/**
     * Actual implementation of the reset course functionality, delete all the
     * assignment submissions for course $data->courseid.
     *
     * @param stdClass $data the data submitted from the reset course.
     * @return array status array
     */
    public function reset_single_user($userid) {
        global $CFG, $DB;

		//get instance
		$instance = $this->get_instance();

		//first delete all submission related files
        $fs = get_file_storage();
        $submissions = $DB->get_records('assign_submission',array('assignment'=>$instance->id,'userid'=>$userid));
        if($submissions){
			foreach($submissions as $submission){
				// Delete files associated with this assignment.
				foreach ($this->submissionplugins as $plugin) {
					$fileareas = array();
					$plugincomponent = $plugin->get_subtype() . '_' . $plugin->get_type();
					$fileareas = $plugin->get_file_areas();
					foreach ($fileareas as $filearea => $notused) {
						$fs->delete_area_files($this->context->id, $plugincomponent, $filearea,$submission->id);
					}
				}

				foreach ($this->feedbackplugins as $plugin) {
					$fileareas = array();
					$plugincomponent = $plugin->get_subtype() . '_' . $plugin->get_type();
					$fileareas = $plugin->get_file_areas();
					foreach ($fileareas as $filearea => $notused) {
						$fs->delete_area_files($this->context->id, $plugincomponent, $filearea,$submission->id);
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
		$instance->cmidnumber =  $this->coursemodule->id; 
        assign_update_grades($instance, $userid);
    }

}