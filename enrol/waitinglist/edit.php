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
 * Adds new instance of enrol_waitinglist to specified course
 * or edits current instance.
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt   {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('edit_form.php');
require_once('lib.php');

$courseid = required_param('courseid', PARAM_INT);

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/waitinglist:manage', $context);

$PAGE->set_url('/enrol/waitinglist/edit.php', array('courseid'=>$course->id));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
if (!enrol_is_enabled('waitinglist')) {
    redirect($return);
}

$plugin = enrol_get_plugin('waitinglist');

if ($instances = $DB->get_records('enrol', array('courseid'=>$course->id, 'enrol'=>'waitinglist'), 'id ASC')) {
    $instance = array_shift($instances);
    if ($instances) {
        // Oh - we allow only one instance per course!!
        foreach ($instances as $del) {
            $plugin->delete_instance($del);
        }
    }
    // Merge these two settings to one value for the single selection element.
    if ($instance->notifyall and $instance->expirynotify) {
        $instance->expirynotify = 2;
    }
    unset($instance->notifyall);

} else {
    require_capability('moodle/course:enrolconfig', $context);
    // No instance yet, we have to add new instance.
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
    $instance = new stdClass();
    $instance->id              = null;
    $instance->courseid        = $course->id;
    $instance->expirynotify    = $plugin->get_config('expirynotify');
    $instance->expirythreshold = $plugin->get_config('expirythreshold');
    /**
     * @updateDate      28/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add invoice option
     */
    $instance->{ENROL_WAITINGLIST_FIELD_INVOICE}          = 0;
    $instance->{ENROL_WAITINGLIST_FIELD_APPROVAL}         = 0;
    /**
     * @updateDate  21/06/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Internal && External Price
     */
    $instance->{ENROL_WAITINGLIST_FIELD_INTERNAL_PRICE} = 0;
    $instance->{ENROL_WAITINGLIST_FIELD_EXTERNAL_PRICE} = 0;

}

$mform = new enrol_waitinglist_edit_form(null, array($instance, $plugin, $context));

if ($mform->is_cancelled()) {
    redirect($return);

} else if ($data = $mform->get_data()) {
    if ($data->expirynotify == 2) {
        $data->expirynotify = 1;
        $data->notifyall = 1;
    } else {
        $data->notifyall = 0;
    }
    if (!$data->expirynotify) {
        // Keep previous/default value of disabled expirythreshold option.
        $data->expirythreshold = $instance->expirythreshold;
    }
    if ($instance->id) {
        $instance->roleid                                           = $data->roleid;
        $instance->enrolperiod                                      = $data->enrolperiod;
        $instance->expirynotify                                     = $data->expirynotify;
        $instance->notifyall                                        = $data->notifyall;
        $instance->expirythreshold                                  = $data->expirythreshold;
		$instance->{ENROL_WAITINGLIST_FIELD_CUTOFFDATE}             = $data->{ENROL_WAITINGLIST_FIELD_CUTOFFDATE};
		$instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS}          = $data->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS};
		$instance->{ENROL_WAITINGLIST_FIELD_WAITLISTSIZE}           = $data->{ENROL_WAITINGLIST_FIELD_WAITLISTSIZE};
		$instance->{ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE}    = $data->{ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE};
		$instance->{ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE}     = $data->{ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE};
		$instance->{ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE}         = $data->{ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE};
        $instance->timemodified    = time();
        /**
         * @updateDate      28/10/2015
         * @author          eFaktor     (fbv)
         *
         * Description
         * Add the invoice information option
         */
        $instance->{ENROL_WAITINGLIST_FIELD_INVOICE} = $data->{ENROL_WAITINGLIST_FIELD_INVOICE};
        /**
         * @updateDate      24/12/2015
         * @author          eFaktor     (fbv)
         *
         * Description
         * Add the approval request option
         */
        $instance->{ENROL_WAITINGLIST_FIELD_APPROVAL} = $data->{ENROL_WAITINGLIST_FIELD_APPROVAL};

        /**
         * @updateDate  21/06/2016
         * @author      eFaktor     (fbv)
         *
         * Description
         * Internal && External price
         */
        $instance->{ENROL_WAITINGLIST_FIELD_INTERNAL_PRICE} = $data->{ENROL_WAITINGLIST_FIELD_INTERNAL_PRICE};
        $instance->{ENROL_WAITINGLIST_FIELD_EXTERNAL_PRICE} = $data->{ENROL_WAITINGLIST_FIELD_EXTERNAL_PRICE};

        $DB->update_record('enrol', $instance);

        // Update seats

        /**
         * Instance have been update
         * So, check all users waiting for a seat
         * Users with entry no confirmed yet
         *
         * @updateDate  06/07/2017
         * @author      eFaktor     (fbv)
         */
        $queueman = \enrol_waitinglist\queuemanager::get_by_course_workspace($course->id);
        if ($queueman->qentries) {
            \core\event\enrol_instance_updated::create_from_record($instance)->trigger();
        }//if_Entries

        // Use standard API to update instance status.
        if ($instance->status != $data->status) {
            $instance = $DB->get_record('enrol', array('id'=>$instance->id));
            $plugin->update_status($instance, $data->status);
            $context->mark_dirty();
        }

    } else {
        $fields = array(
                        'status'                                    => $data->status,
                        'roleid'                                    => $data->roleid,
                        'enrolperiod'                               => $data->enrolperiod,
                        'expirynotify'                              => $data->expirynotify,
                        'notifyall'                                 => $data->notifyall,
                        'expirythreshold'                           => $data->expirythreshold,
			            ENROL_WAITINGLIST_FIELD_CUTOFFDATE          =>$data->{ENROL_WAITINGLIST_FIELD_CUTOFFDATE},
			            ENROL_WAITINGLIST_FIELD_MAXENROLMENTS       =>$data->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS},
			            ENROL_WAITINGLIST_FIELD_WAITLISTSIZE        =>$data->{ENROL_WAITINGLIST_FIELD_WAITLISTSIZE},
			            ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE =>$data->{ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE},
			            ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE  =>$data->{ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE},
			            ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE      =>$data->{ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE},
                        /**
                         * @updateDate  28/10/2015
                         * @author      eFaktor     (fbv)
                         *
                         * Description
                         * Add the invoice information option
                         */
                        ENROL_WAITINGLIST_FIELD_INVOICE => $data->{ENROL_WAITINGLIST_FIELD_INVOICE},
                        /**
                         * @updateDate      24/12/2015
                         * @author          eFaktor     (fbv)
                         *
                         * Description
                         * Add the approval request option
                         */
                        ENROL_WAITINGLIST_FIELD_APPROVAL => $data->{ENROL_WAITINGLIST_FIELD_APPROVAL},
                        /**
                         * @updateDate  21/06/2016
                         * @author      eFaktor     (fbv)
                         *
                         * Description
                         * Internal && External price
                         */
                        ENROL_WAITINGLIST_FIELD_INTERNAL_PRICE => $data->{ENROL_WAITINGLIST_FIELD_INTERNAL_PRICE},
                        ENROL_WAITINGLIST_FIELD_EXTERNAL_PRICE => $data->{ENROL_WAITINGLIST_FIELD_EXTERNAL_PRICE}
		               );
        $waitinglistid =  $plugin->add_instance($course, $fields);

       //add default methods
        //add an instance of each of the methods, if the waitinglist instance was created ok
        if($waitinglistid){
			$methods=array();
			foreach(enrol_waitinglist_plugin::get_method_names() as $methodtype){
			 $class = '\enrol_waitinglist\method\\' . $methodtype. '\enrolmethod' .$methodtype ;
			   if (class_exists($class)){
					$class::add_default_instance($course->id,$waitinglistid); 
			   }
			}
		}
    }

    redirect($return);
}

$PAGE->set_title(get_string('pluginname', 'enrol_waitinglist'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_waitinglist'));
$mform->display();
echo $OUTPUT->footer();
