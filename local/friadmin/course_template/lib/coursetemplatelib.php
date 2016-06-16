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
define('ENROL_FIELD_PRICE','customtext3');
define('SETTINGS_DEFAULT_SIZE',100);
define('ACTION_ENROLMENT',1);
define('ACTION_SHOW_COURSE',0);

define('CT_APPROVAL_NONE',0);
define('CT_APPROVAL_REQUIRED',1);
define('CT_APPROVAL_MESSAGE',2);

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
            if (is_siteadmin($USER->id)) {
                return true;
            }

            /* Fist, check if the user has the correct permissions  */
            /* Search Criteria  */
            $params = array();
            $params['user'] = $USER->id;
            //$params['context'] = '1';
            $params['archetype'] = 'manager';
            $params['level']     = CONTEXT_COURSECAT;

            /* SQL Instruction  */
            $sql = " SELECT		ra.id,
                                ra.contextid,
                                ra.userid
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 	r.id 			= ra.roleid
                                                            AND	r.archetype		= :archetype
                                                            AND r.shortname     = r.archetype
                        JOIN    {context}           ct      ON  ct.id			= ra.contextid
                                                            AND ct.contextlevel = :level
                     WHERE		ra.userid     = :user
                         ";

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
     * @param           $courseTemplate
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
    public static function GetEnrolInstance($courseId,$courseTemplate) {
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
            $params['enrol']        = 'waitinglist';
            $params['self']         = 'self';
            $params['bulk']         = 'unnamedbulk';

            /* SQL Instruction */
            $sql = " SELECT	e.id,
                            e.courseid,
                            IF(e.customint1,e.customint1,0)	as 'date_off',
                            e.customint2 	                as 'max_enrolled',
                            e.customint6 	                as 'list_size',
                            e.customint8 	                as 'invoice',
                            e.customint7 	                as 'approval',
                            e.customtext3                   as 'price',
                            es.id							as 'selfid',
                            un.id							as 'bulkid'
                     FROM		{enrol}						e
                        -- SELF METHOD
                        JOIN 	{enrol_waitinglist_method}	es	ON 	es.waitinglistid 	= e.id
                                                                AND es.courseid 		= e.courseid
                                                                AND	es.methodtype		= :self
                        -- UNNAMED METHOD
                        JOIN 	{enrol_waitinglist_method}	un	ON 	un.waitinglistid 	= e.id
                                                                AND un.courseid 		= e.courseid
                                                                AND	un.methodtype		= :bulk
                     WHERE	e.enrol 	= :enrol
                        AND	e.courseid 	= :courseid ";


            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                /* Check Course Template */
                $params['courseid']     = $courseTemplate;

                /* Execute */
                $rdo = $DB->get_record_sql($sql,$params);
                if ($rdo) {

                    $rdo->id        = null;
                    $rdo->selfid    = null;
                    $rdo->bulkid    = null;
                    $rdo->courseid  = $courseId;

                    return $rdo;
                }else {
                    /* Instance */
                    $instance = new stdClass();
                    $instance->id               = null;
                    $instance->selfid           = null;
                    $instance->bulkid           = null;
                    $instance->courseid         = $courseId;
                    $instance->date_off         = 0;
                    $instance->max_enrolled     = 0;
                    $instance->list_size        = SETTINGS_DEFAULT_SIZE;
                    $instance->invoice          = 0;
                    $instance->approval         = 0;
                    $instance->price            = 0;

                    return $instance;
                }//if_rdo_courseTemplate
            }//if_rdo_course
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
        $methodSelf     = null;
        $methodBulk     = null;
        $time           = null;

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local time   */
            $time = time();

            /* Enrol Instance   */
            $enrolInstance = new stdClass();
            $enrolInstance->id                          = $data->instanceid;
            $enrolInstance->courseid                    = $data->id;
            $enrolInstance->{ENROL_FIELD_CUTOFFDATE}    = $data->date_off;
            $enrolInstance->{ENROL_FIELD_WAITLISTSIZE}  = $data->list_size;
            $enrolInstance->{ENROL_FIELD_MAXENROLMENTS} = $data->max_enrolled;
            $enrolInstance->{ENROL_FIELD_INVOICE}       = $data->invoice;
            $enrolInstance->{ENROL_FIELD_APPROVAL}      = $data->approval;
            $enrolInstance->{ENROL_FIELD_PRICE}         = $data->price;

            $enrolInstance->timemodified                = $time;
            /* Execute  */
            $DB->update_record('enrol',$enrolInstance);

            /* Method Enrol Instance    */
            switch ($data->waitinglist) {
                case ENROL_WAITING_SELF:
                    /* Self Method  */
                    $methodSelf = new stdClass();
                    $methodSelf->id             = $data->selfid;
                    $methodSelf->status         = 1;
                    $methodSelf->timemodified   = $time;
                    $methodSelf->password       = $data->password;
                    /* Update  */
                    $DB->update_record('enrol_waitinglist_method',$methodSelf);

                    /* Bulk Method  */
                    $methodBulk = new stdClass();
                    $methodBulk->id             = $data->bulkid;
                    $methodBulk->status         = 0;
                    $methodBulk->timemodified   = $time;
                    /* Update  */
                    $DB->update_record('enrol_waitinglist_method',$methodBulk);

                    break;
                case ENROL_WAITING_BULK:
                    /* Self Method  */
                    $methodSelf = new stdClass();
                    $methodSelf->id             = $data->selfid;
                    $methodSelf->status         = 0;
                    $methodSelf->timemodified   = $time;
                    /* Update  */
                    $DB->update_record('enrol_waitinglist_method',$methodSelf);

                    /* Bulk Method  */
                    $methodBulk = new stdClass();
                    $methodBulk->id             = $data->bulkid;
                    $methodBulk->status         = 1;
                    $methodBulk->timemodified   = $time;
                    /* Update  */
                    $DB->update_record('enrol_waitinglist_method',$methodBulk);

                    break;
            }//switch

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
        $methodId       = null;

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local time   */
            $time = time();

            /* Plugin info  */
            $plugin = enrol_get_plugin('waitinglist');

            $course = get_course($data->id);

            $data->instanceid = $plugin->add_default_instance($course);

            /* Insert Default instance*/
            $sql = " SELECT *
                     FROM   {enrol_waitinglist_method}
                     WHERE  waitinglistid = :courseid
                        AND courseid      = :waitinglistid";

            $rdo = $DB->get_records_sql($sql,array('waitinglistid' => $data->instanceid,'courseid' => $data->id));
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $instance->waitinglistid    = $data->instanceid;
                    $instance->courseid         = $data->id;
                    $instance->timecreated      = $time;

                    /* Execute */
                    $methodId = $DB->insert_record('enrol_waitinglist_method',$instance);
                    if ($instance->methodtype == 'self') {
                        $data->selfid = $methodId;
                    }else {
                        $data->bulkid = $methodId;
                    }
                }
            }

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