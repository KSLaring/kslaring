<?php
/**
 * Course Template - Library
 *
 * @package         local/
 * @subpackage      course_template/library
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/01/2016
 * @author          eFaktor     (fbv)
 *
 */

define('ENROL_WAITING_SELF',1);
define('ENROL_WAITING_BULK',2);
define('ENROL_FIELD_INVOICE','customint8');
define('ENROL_FIELD_APPROVAL','customint7');
define('ENROL_FIELD_CUTOFFDATE', 'customint1');
define('ENROL_FIELD_MAXENROLMENTS', 'customint2');
define('METHOD_FIELD_MAXENROLMENTS','customint3');
define('ENROL_FIELD_WAITLISTSIZE', 'customint6');
define('ENROL_FIELD_SENDWELCOMEMESSAGE', 'customint4');
define('ENROL_FIELD_SENDWAITLISTMESSAGE', 'customint5');
define('SETTINGS_DEFAULT_SIZE',100);
define('ACTION_ENROLMENT',1);
define('ACTION_SHOW_COURSE',0);

class CourseTemplate {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $categoryId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get category name
     */
    public static function GetCategoryName($categoryId) {
        /* Variables */
        global $DB;
        $rdo = null;

        try {
            /* Get category name */
            $rdo = $DB->get_record('course_categories',array('id' => $categoryId),'name');
            if ($rdo) {
                return $rdo->name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCategoryName

    /**
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user has the correct permissions to create a new course from the
     * template
     */
    public static function HasCorrectPermissions() {
        /* Variables    */
        global $DB, $USER;

        try {
            /* Fist, check if the user has the correct permissions  */
            /* Search Criteria  */
            $params = array();
            $params['user'] = $USER->id;
            $params['context'] = '1';
            $params['archetype'] = 'manager';

            /* SQL Instruction  */
            $sql = " SELECT		ra.id,
                                ra.contextid,
                                ra.userid
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 	r.id 			= ra.roleid
                                                            AND	r.archetype		= :archetype
                                                            AND r.shortname     = r.archetype
                     WHERE		ra.userid     = :user
                        AND     ra.contextid  = :context ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql, $params);
            if ($rdo) {
                return true;
            } else {
                return false;
            }//if_rdo
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//HasCorrectPermissions

    /**
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a fake permission, temporary permission, to the user.
     * So, the user will be able to create a new course from the template
     */
    public static function Add_FakePermission_To_User() {
        /* Variables    */
        global $DB, $USER;
        $fakePermission = null;
        $context = null;
        $role = null;

        try {
            /* Context System   */
            $context = CONTEXT_SYSTEM::instance();
            /* Role Id      */
            $role = $DB->get_record('role', array('archetype' => 'manager', 'shortname' => 'manager'));

            /* New Fake Permission  */
            $fakePermission = new stdClass();
            $fakePermission->userid = $USER->id;
            $fakePermission->roleid = $role->id;
            $fakePermission->contextid = $context->id;
            $fakePermission->timemodified = time();

            /* Insert   */
            $fakePermission->id = $DB->insert_record('role_assignments', $fakePermission);

            /* Reload All Capabilities  */
            reload_all_capabilities();

            return $fakePermission->id;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_FakePermission_To_User

    /**
     * @param           $fakePermissionId
     *
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete the fake permission have been created for the user
     */
    public static function Delete_FakePermission($fakePermissionId) {
        /* Variables    */
        global $DB;

        try {
            /* Delete Fake Permission   */
            $DB->delete_records('role_assignments', array('id' => $fakePermissionId));

            /* Reload All Capabilities  */
            reload_all_capabilities();
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Delete_FakePermission

    /**
     * @param           $courseId
     * @param           $method
     *
     * @return          mixed|stdClass
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get enrol instance connected with the method
     */
    public static function GetEnrolInstance($courseId,$method) {
        /* Variables */
        global $DB;
        $params     = null;
        $rdo        = null;
        $sql        = null;
        $instance   = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['courseid']     = $courseId;
            if ($method == ENROL_WAITING_SELF) {
                $params['method']       = 'self';
            }else {
                $params['method']       = 'unnamedbulk';
            }

            /* SQL Instruction */
            $sql = " SELECT	ewm.id,
                            ewm.waitinglistid,
                            ewm.courseid,
                            IF(e.customint1,e.customint1,0)	as 'date_off',
                            ewm.customint3 	                as 'max_enrolled',
                            e.customint6 	                as 'list_size',
                            e.customint8 	                as 'invoice',
                            e.customint7 	                as 'approval'
                     FROM		{enrol_waitinglist_method}	ewm
                        JOIN	{enrol}						e		ON e.id = 	ewm.waitinglistid
                     WHERE	ewm.courseid 	= :courseid
                        AND	ewm.methodtype 	= :method ";


            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                $instance = new stdClass();
                $instance->id               = null;
                $instance->waitinglistid    = null;
                $instance->courseid         = $courseId;
                $instance->date_off         = 0;
                $instance->max_enrolled     = 0;
                $instance->list_size        = SETTINGS_DEFAULT_SIZE;
                $instance->invoice          = 0;
                $instance->approval         = 0;

                return $instance;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetEnrolInstance

    /**
     * @param           $data
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update Waiting Enrolment
     */
    public static function UpdateWaitingEnrolment($data) {
        /* Variables */
        global $DB;
        $trans          = null;
        $enrolInstance  = null;
        $methodInstance = null;
        $time           = null;

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local time   */
            $time = time();

            /* Enrol Instance   */
            $enrolInstance = new stdClass();
            $enrolInstance->id                          = $data->waitinglistid;
            $enrolInstance->courseid                    = $data->id;
            $enrolInstance->{ENROL_FIELD_CUTOFFDATE}    = $data->date_off;
            $enrolInstance->{ENROL_FIELD_WAITLISTSIZE}  = $data->list_size;
            $enrolInstance->{ENROL_FIELD_INVOICE}       = $data->invoice;
            $enrolInstance->{ENROL_FIELD_APPROVAL}      = $data->approval;
            $enrolInstance->timemodified                = $time;
            /* Execute  */
            $DB->update_record('enrol',$enrolInstance);

            /* Clear Status */
            $rdo = $DB->get_records('enrol_waitinglist_method',array('courseid' => $data->id,'waitinglistid' => $data->waitinglistid),'id,status');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $instance->status = 0;
                    /* Execute  */
                    $DB->update_record('enrol_waitinglist_method',$instance);
                }
            }//if_rdo

            /* Method Enrol Instance */
            $methodInstance = new stdClass();
            $methodInstance->id                             = $data->instanceid;
            $methodInstance->waitinglistid                  = $data->waitinglistid;
            $methodInstance->courseid                       = $data->id;
            $methodInstance->{METHOD_FIELD_MAXENROLMENTS}   = $data->max_enrolled;
            $methodInstance->status                         = 1;
            $methodInstance->timemodified                   = $time;
            if ($data->waitinglist == ENROL_WAITING_SELF) {
                $enrolInstance->password                    = $data->password;
                $methodInstance->methodtype                 = 'self';
            }else {
                $methodInstance->methodtype                 = 'unnamedbulk';
            }
            /* Execute  */
            $DB->update_record('enrol_waitinglist_method',$methodInstance);

            /* Commit */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//UpdateWaitingEnrolment

    /**
     * @param           $data
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create waiting enrolment instance
     */
    public static function CreateWaitingEnrolment($data) {
        /* Variables */
        global $DB;
        $trans          = null;
        $course         = null;
        $method         = null;
        $plugin         = null;
        $method         = array();

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local time   */
            $time = time();

            /* Plugin info  */
            $plugin = enrol_get_plugin('waitinglist');

            $course = get_course($data->id);

            $data->waitinglistid = $plugin->add_default_instance($course);


            if ($data->waitinglist == ENROL_WAITING_SELF) {
                $method = 'self';
            }else {
                $method = 'unnamedbulk';
            }

            /* Insert Default instance*/
            $sql = " SELECT *
                     FROM   {enrol_waitinglist_method}
                     WHERE  waitinglistid = :courseid
                        AND courseid      = :waitinglistid";

            $rdo = $DB->get_records_sql($sql,array('waitinglistid' => $data->waitinglistid,'courseid' => $data->id));
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $instance->waitinglistid    = $data->waitinglistid;
                    $instance->courseid         = $data->id;
                    $instance->timecreated      = $time;

                    /* Execute */
                    $DB->insert_record('enrol_waitinglist_method',$instance);
                }
            }

            /* Get Instance Id  */
            $sql = " SELECT id
                     FROM   {enrol_waitinglist_method}
                     WHERE  waitinglistid = :waitinglistid
                        AND courseid      = :courseid
                        AND methodtype LIKE '" . $method . "'";
            $rdo = $DB->get_record_sql($sql,array('waitinglistid' => $data->waitinglistid,'courseid' => $data->id));
            $data->instanceid = $rdo->id;

            self::UpdateWaitingEnrolment($data);

            /* Commit */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//CreateWaitingEnrolment

    /***********/
    /* PRIVATE */
    /***********/
}//CourseTemplate