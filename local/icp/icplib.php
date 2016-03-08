<?php
/**
 * Inconsistencies Course Completions  - Library
 *
 * @package         local
 * @subpackage      icp
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    25/05/2015
 * @author          eFaktor     (fbv)
 */

define ('ACT_COMPLETED',1);
define ('ACT_FAILED',2);
define ('ACT_NOT_DONE',3);
define ('ACT_NOT_DONE_GRADE',4);
define ('ACT_NOT_VIEWED',5);
define ('ACT_VIEWED',6);
define ('REQ_NOT_COMPLETED',7);
define ('REQ_COMPLETED',8);
define ('COURSE_COMPLETED',9);

class InconsistenciesCompletions {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    /**
     * @param           $courseID
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    25/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if there are users with inconsistencies
     */
    public static function ExistUsers_ToClean($courseID) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseID;
            $params['fixed']    = 0;

            /* SQL Instruction  */
            $sql = " SELECT	count(DISTINCT	userid) as 'total'
                     FROM	{course_inconsistencies}
                     WHERE	courseid 	= :course
                        AND	fixed 		= :fixed ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->total) {
                    return true;
                }else {
                    return false;
                }
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ExistUsers_ToClean

    /**
     * @param           $courseID
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    18/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Course Completion Detail
     *
     * Course Info. Object
     *      --> id.         Course id
     *      --> activities.     Array.  Activities to complete
     *      --> preReqCourses.  Array.  Courses to completed before
     *      --> basedOnMe.      Array.  Courses that completion depends on me
     *      --> gradeCriteria.  Grade Course Criteria.
     */
    public static function Get_InfoCourseCompletion($courseID) {
        /* Variables    */
        $courseInfo         = null;

        try {
            /* Course Info Completion           */
            $courseInfo = new stdClass();
            $courseInfo->id         = $courseID;
            /* Info Activities Criterias        */
            $courseInfo->activities     = self::Get_InfoActivitiesCompletion($courseID);
            /* Info Courses Connected Criteria  */
            $courseInfo->preReqCourses  = self::Get_PreRequisitesCourses($courseID);
            /* Course Base On Me                */
            $courseInfo->basedOnMe      = self::Get_CoursesBasedOnMe($courseID);
            /* Info Grade Course                */
            $courseInfo->gradeCriteria  = self::Get_GradeCourseCriteria($courseID);
            /* All Completions Criteria */
            $courseInfo->allCriteria    = self::GetAll_CompletionsCriteria($courseID);

            return $courseInfo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_IfoCourseCompletion


    /**
     * @param           $courseID
     * @return          array
     * @throws          Exception
     *
     * @creationDate    25/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all users connected to the course
     *
     * Users. Array
     *      [userid]
     *              --> id.             User id
     *              --> completionId.   Completion Id.
     *              --> timecompleted.
     */
    public static function Get_Users($courseID) {
        /* Variables    */
        global $DB;
        $users  = array();
        $info   = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseID;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT 	cc.userid,
                                            cc.id as 'completion_id',
                                            cc.timecompleted
                     FROM		{course_completions}	cc
                        JOIN	{user}				    u		ON 		u.id 		= cc.userid
                                                                AND		u.deleted 	= 0
                        JOIN	{user_enrolments}		ue		ON		ue.userid	= u.id
                        JOIN	{enrol}				    e		ON		e.id		= ue.enrolid
                                                                AND		e.courseid	= cc.course
                                                                AND		e.status	= 0
                     WHERE		cc.course = :course
                     ORDER BY	cc.userid ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info User    */
                    $info = new stdClass();
                    $info->id               = $instance->userid;
                    $info->completionId     = $instance->completion_id;
                    $info->timecompleted    = $instance->timecompleted;

                    /* Add User */
                    $users[$instance->userid] = $info;
                }//for_Each_user
            }//if_rdo

            return $users;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_Users

    /**
     * @param           $users
     * @param           $courseInfo
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    21/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the users that have some inconsistencies on their course completion
     */
    public static function Users_WithInconsistencies($users,$courseInfo) {
        /* Variables    */
        global $DB;
        $usersWith              = array();
        $userToCheckCompleted   = array();
        $infoAllCompletion      = null;

        $usersInconsistencies   = false;
        $completionsCriteria    = null;
        $allCriteria            = null;
        $courseID               = null;
        $typeInconsistency      = null;
        $courseInconsistency    = null;

        try {
            /* Get Info Course  */
            $completionsCriteria    = $courseInfo->activities;
            $allCriteria            = implode(',',$courseInfo->allCriteria);
            $courseID               = $courseInfo->id;

            foreach ($users as $userID => $infoUser) {
                /* Clean */
                $typeInconsistency  = null;

                foreach ($completionsCriteria as $criteria) {
                    /* Clean    */
                    $typeInconsistency = null;

                    /* Get Inconsistency Type   */
                    if (!is_null($criteria->tracking_grade)) {
                        $typeInconsistency = self::Get_TypeInconsistency_GradeActivity($infoUser,$courseID,$criteria);
                    }else {
                        $typeInconsistency = self::Get_TypeInconsistency_NoGradeActivity($infoUser,$courseID,$criteria);
                    }//if_act_withGrade

                    if ($typeInconsistency) {
                        /* Add Record - course_inconsistencies */
                        $courseInconsistency = new stdClass();
                        $courseInconsistency->courseid          = $courseID;
                        $courseInconsistency->userid            = $userID;
                        $courseInconsistency->coursemoduleid    = $criteria->module_instance;
                        $courseInconsistency->criteriaid        = $criteria->ccc_criteria;
                        $courseInconsistency->gradeitemid       = $criteria->grade_id;
                        $courseInconsistency->completionid      = $infoUser->completionId;
                        $courseInconsistency->timecompleted     = $infoUser->timecompleted;
                        $courseInconsistency->inconsistency     = $typeInconsistency;
                        $courseInconsistency->fixed             = 0;
                        $courseInconsistency->newcompletion     = null;

                        /* Insert Record    */
                        $DB->insert_record('course_inconsistencies',$courseInconsistency);

                        $usersInconsistencies = true;
                        $usersWith[$userID] = $userID;
                    }//if_typeinconsistency
                }//forEach_Criteria

                /* Check Inconsistency Pre Req Course   */
                if ($courseInfo->preReqCourses) {
                    $preReqCourses = $courseInfo->preReqCourses;
                    $typeInconsistency = null;
                    foreach ($preReqCourses as $courseReq=>$criteria) {
                        $typeInconsistency = self::Get_TypeInconsistency_PreReqCourse($infoUser,$courseID,$courseReq,$criteria);

                        /* Add Inconsistency    */
                        if ($typeInconsistency) {
                            /* Add Record - course_inconsistencies */
                            $courseInconsistency = new stdClass();
                            $courseInconsistency->courseid          = $courseID;
                            $courseInconsistency->userid            = $userID;
                            $courseInconsistency->coursemoduleid    = null;
                            $courseInconsistency->criteriaid        = $criteria;
                            $courseInconsistency->gradeitemid       = null;
                            $courseInconsistency->completionid      = $infoUser->completionId;
                            $courseInconsistency->timecompleted     = $infoUser->timecompleted;
                            $courseInconsistency->inconsistency     = $typeInconsistency;
                            $courseInconsistency->fixed             = 0;
                            $courseInconsistency->newcompletion     = null;

                            /* Insert Record    */
                            $DB->insert_record('course_inconsistencies',$courseInconsistency);

                            $usersInconsistencies = true;
                            $usersWith[$userID]       = $userID;
                        }//if_Inconsistency

                        $typeInconsistency = null;
                    }//for_each_reqCourse
                }//if_preReqCourses

                if (!array_key_exists($userID,$usersWith)) {
                    $userToCheckCompleted[$userID] = $userID;
                }
            }//for_EachUser

            if ($userToCheckCompleted) {
                foreach ($userToCheckCompleted as $user) {
                    $infoAllCompletion = self::Get_InfoCompletionUser($user,$courseID,$allCriteria);
                    if ($infoAllCompletion) {
                        if (($infoAllCompletion->completions == $allCriteria) && (!$infoAllCompletion->timecompleted)) {
                            /* Add Record - course_inconsistencies */
                            $courseInconsistency = new stdClass();
                            $courseInconsistency->courseid          = $courseID;
                            $courseInconsistency->userid            = $user;
                            $courseInconsistency->coursemoduleid    = null;
                            $courseInconsistency->criteriaid        = null;
                            $courseInconsistency->gradeitemid       = null;
                            $courseInconsistency->completionid      = $infoAllCompletion->id;
                            $courseInconsistency->timecompleted     = null;
                            $courseInconsistency->inconsistency     = COURSE_COMPLETED;
                            $courseInconsistency->fixed             = 0;
                            $courseInconsistency->newcompletion     = null;

                            /* Insert Record    */
                            $DB->insert_record('course_inconsistencies',$courseInconsistency);

                            $usersInconsistencies = true;
                        }//if_courseCompleted
                    }//if_infoAllCompletion
                }
            }
            /* Check Course Completed   */
            return $usersInconsistencies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Users_WithInconsistencies

    /**
     * @param           $courseID
     * @return          int
     * @throws          Exception
     *
     * @creationDate    25/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get total users completed but marked with inconsistencies
     */
    public static function GetTotalUsers_CompletedWithInconsistencies($courseID) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseID;
            $params['fixed']    = 0;

            /* SQL Instruction  */
            $sql = " SELECT	count(DISTINCT	userid) as 'total'
                     FROM	{course_inconsistencies}
                     WHERE	courseid 	= :course
                        AND	fixed 		= :fixed
                        AND timecompleted IS NOT NULL
                        AND timecompleted != 0 ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return 0;
            }//if_rdo
        }catch (Exception $ex){
            throw $ex;
        }//ty_catch
    }//GetTotalUsers_CompletedWithInconsistencies

    /**
     * @param           $courseID
     * @return          int
     * @throws          Exception
     *
     * @creationDate    25/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get total users not completed but marked with inconsistencies
     */
    public static function GetTotalUsers_NotCompletedWithInconsistencies($courseID) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseID;
            $params['fixed']    = 0;

            /* SQL Instruction  */
            $sql = " SELECT	count(DISTINCT	userid) as 'total'
                     FROM	{course_inconsistencies}
                     WHERE	courseid 	= :course
                        AND	fixed 		= :fixed
                        AND (timecompleted IS NULL
                             OR
                             timecompleted = 0) ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return 0;
            }//if_rdo
        }catch (Exception $ex){
            throw $ex;
        }//ty_catch
    }//GetTotalUsers_NotCompletedWithInconsistencies

    /**
     * @param           $userID
     * @param           $courseID
     * @param           $allCriteria
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    25/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Info Completion User
     */
    private static function Get_InfoCompletionUser($userID,$courseID,$allCriteria) {
        /* Variables    */
        global $DB;
        $infoCompletions = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseID;
            $params['user']     = $userID;

            /* SQL Instruction  */
            $sql = " SELECT		cc.userid,
                                cc.id,
                                cc.timecompleted,
                                GROUP_CONCAT(DISTINCT cc_cl.criteriaid ORDER BY cc_cl.criteriaid SEPARATOR ',') as 'completions'
                     FROM		{course_completions}				cc
                        JOIN	{course_completion_crit_compl} 	    cc_cl	ON	cc_cl.course		= cc.course
                                                                            AND	cc_cl.criteriaid	IN ($allCriteria)
                                                                            AND	cc_cl.userid		= cc.userid
                     WHERE		cc.course  = :course
                        AND		cc.userid  = :user ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_InfoCompletionUser

    /**
     * @param           $totalCompleted
     * @param           $totalNotCompleted
     * @return          html_table
     * @throws          Exception
     *
     * @creationDate    25/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the table with the total of users with inconsistencies
     */
    public static function Get_TableInfo($totalCompleted,$totalNotCompleted) {
        /* Variables    */
        $table              = null;
        $row                = null;

        try {
            /* Table */
            $table = new html_table();
            $table->id                  = "uupreview";
            $table->attributes['class'] = 'generaltable';
            $table->attributes['align'] = 'center';

            /* Header */
            $table->head  = array(get_string('total_users','local_icp'),
                                  get_string('description','local_icp'));

            /* Total Users - Completed Inconsistencies  */
            /* Add Row  */
            $row = array();
            $row[] = $totalCompleted;
            $row[] = get_string('completed_with','local_icp');
            $table->data[] = $row;

            /* Total Users - Not Completed Inconsistencies  */
            /* Add Row  */
            $row = array();
            $row[] = $totalNotCompleted;
            $row[] = get_string('not_completed_with','local_icp');
            $table->data[] = $row;

            return $table;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TableNotImported

    /**
     * @param           $courseID
     * @param           $courseInfo
     * @return          bool
     *
     * @creationDate    25/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean all the inconsistencies for the course
     */
    public static function CleanInconsistencies($courseID,$courseInfo) {
        /* Variables    */
        global $DB;
        $users          = array();
        $basedOnMe      = null;
        $gradeCriteria  = null;
        $infoCron       = null;

        /* Start Transaction    */
        $transaction = $DB->start_delegated_transaction();

        try {
            /* Get Course Inconsistencies - by User */
            /* Search Criteria  */
            $params = array();
            $params['courseid']     = $courseID;
            $params['fixed']        = 0;

            /* Execute  */
            $rdo = $DB->get_records('course_inconsistencies',$params);
            if ($rdo) {
                /* Get Info     */
                $basedOnMe              = $courseInfo->basedOnMe;
                $gradeCriteria          = $courseInfo->gradeCriteria;

                foreach ($rdo as $instance) {
                    self::CleanInconsistency($instance,$gradeCriteria,$basedOnMe);

                    /* User Fixed   */
                    $users[$instance->userid] = $instance->userid;
                }//for_rdo
            }//if_users

            /* Commit   */
            $transaction->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback     */
            $transaction->rollback($ex);

            return false;
        }//CleanInconsistencies
    }//CleanInconsistencies

    /**********************/
    /* PRIVATE FUNCTIONS  */
    /**********************/

    /**
     * @param           $courseID
     * @return          array
     * @throws          Exception
     *
     * @creationDate    18/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Info Activities Completion
     *
     * Act Compeltion. Array.
     *      []
     *          --> module_instance.    course_compeltion_criteria->moduleinstance
     *          --> ccc_criteria.       course_completion_criteria->id; course_completion_crit_compl-->criteriaid
     *          --> module.             module name.
     *          --> grade_id.           grade_items->id; grade_grades->itmeid;
     *          --> grade_pass.         compare grade_grades->finalgrade
     *          --> tracking.           COMPLETION_TRACKING_MANUAL OR COMPLETION_TRACKING_AUTOMATIC
     *          --> tracking_grade.     completion grade item number ??
     *          --> completion_view.    Null or Not
     */
    private static function Get_InfoActivitiesCompletion ($courseID) {
        /* Variables    */
        global $DB;
        $actCompletion  = array();
        $info           = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseID;

            /* SQL Instruction  */
            $sql = " SELECT			cm.id       as 'cm_id',
 				                    cm.completion,
				                    cm.completiongradeitemnumber,
                                    cm.completionview,
                                    cm.completionexpected,
                                    ccc.id      as 'ccc_id',
                                    cm.instance as 'act_id',
                                    ccc.module,
                                    gi.id	    as 'gi_id',
                                    gi.gradepass
                     FROM			{course_modules}				cm
                        JOIN		{course_completion_criteria}	ccc		ON 	ccc.course 			= cm.course
                                                                            AND ccc.moduleinstance 	= cm.id
                        LEFT JOIN	{grade_items}					gi		ON	gi.courseid			= ccc.course
                                                                            AND	gi.iteminstance		= cm.instance
                                                                            AND gi.itemmodule		= ccc.module
                                                                            AND	gi.itemtype			= 'mod'
                     WHERE		cm.course	= :course
                     ORDER BY   ccc.id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Activity Completion Criteria Info    */
                    $info = new stdClass();
                    $info->module_instance  = $instance->cm_id;     /* course_completion_criteria->moduleinstance   */
                    $info->ccc_criteria     = $instance->ccc_id;    /* course_completion_criteria->id ; course_completion_crit_compl->criteriaid             */
                    $info->module           = $instance->module;
                    $info->act_Id           = $instance->act_id;
                    $info->grade_id         = $instance->gi_id;     /* grade_items->id; grade_grades->itemid        */
                    $info->grade_pass       = $instance->gradepass; /* Compare with grade_grades->finalgrade        */
                    $info->tracking         = $instance->completion;
                    $info->tracking_grade   = $instance->completiongradeitemnumber;
                    $info->completion_view  = $instance->completionview;

                    /* Add */
                    $actCompletion[$instance->ccc_id] = $info;
                }//for_Rdo
            }//if_rdo

            return $actCompletion;
        }catch (Exception $ex) {
            throw $ex;
        }
    }//Get_InfoActivitiesCompletion

    /**
     * @param           $courseID
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    18/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the pre  courses
     *
     * Pre courses.     Array
     *      [courseid] = ccc_criteria
     */
    private static function Get_PreRequisitesCourses($courseID) {
        /* Variables    */
        global $DB;
        $preReqCourses = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']           = $courseID;
            $params['criteriatype']     = COMPLETION_CRITERIA_TYPE_COURSE;

            /* Execute  */
            $rdo = $DB->get_records('course_completion_criteria',$params,'id,courseinstance');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* id --> course_completion_criteria->id; course_completion_crit_compl-->criteriaid */
                    $preReqCourses[$instance->courseinstance] = $instance->id;
                }//for_each
            }//if_rdo

            return $preReqCourses;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_PreRequisitesCourses

    /**
     * @param           $courseID
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the courses, that their completion are based on me.
     */
    private static function Get_CoursesBasedOnMe($courseID) {
        /* Variables    */
        global $DB;
        $basedOnMe          = array();
        $infoCompletion     = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['courseinstance']   = $courseID;
            $params['criteriatype']     = COMPLETION_CRITERIA_TYPE_COURSE;

            /* Execute  */
            $rdo = $DB->get_records('course_completion_criteria',$params,'id,course');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $basedOnMe[$instance->course] = $instance->id;
                }//for_each
            }//if_rdo

            return $basedOnMe;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CoursesBasedOnMe

    /**
     * @param           $courseID
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    18/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the grade criteria for the course
     *
     * Grade criteria
     *          --> ccc_criteria.   Criteria ID.    course_completion_criteria->id; course_completion_crit_compl-->criteriaid
     *          --> grade_id.       grade_items->id; grade_grades->itemid
     *          --> grade_pass.
     */
    private static function Get_GradeCourseCriteria($courseID) {
        /* Variables    */
        global $DB;
        $gradeCriteria  = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseID;
            $params['type']     = COMPLETION_CRITERIA_TYPE_GRADE;

            /* SQL Instruction  */
            $sql = " SELECT		ccc.id,
                                gi.id		as 'gi_id',
                                ccc.gradepass
                     FROM		{course_completion_criteria}	ccc
                        JOIN	{grade_items}					gi		ON	gi.courseid		= ccc.course
                                                                        AND gi.itemtype		= 'course'
                     WHERE		ccc.course 			= :course
                        AND		ccc.criteriatype 	= :type ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $gradeCriteria = new stdClass();
                $gradeCriteria->ccc_criteria    = $rdo->id;     /* course_completion_criteria->id; course_completion_crit_compl-->criteriaid */
                $gradeCriteria->grade_id        = $rdo->gi_id;  /* grade_items->id; grade_grades->itemid        */
                $gradeCriteria->grade_pass      = $rdo->gradepass;

            }//if_Rdo

            return $gradeCriteria;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_GradeCourseCriteria

    /**
     * @param           $courseID
     * @return          array
     * @throws          Exception
     *
     * @creationDate    25/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * All completions criteria
     */
    private static function GetAll_CompletionsCriteria($courseID) {
        /* Variables    */
        global $DB;
        $allCompletionsCriteria = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseID;

            /* SQL Instruction  */
            $sql = " SELECT		id
                     FROM		{course_completion_criteria}
                     WHERE		course = :course
                     ORDER BY 	id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $allCompletionsCriteria[$instance->id] = $instance->id;
                }//for_rdo
            }//if_rdo

            return $allCompletionsCriteria;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetAll_CompletionsCriteria

    /**
     * @param           $infoUser
     * @param           $courseID
     * @param           $criteria
     * @return          int|null
     * @throws          Exception
     *
     * @creationDate    25/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the inconsistency connected with the user and activity with grade.
     */
    private static function Get_TypeInconsistency_GradeActivity($infoUser,$courseID,$criteria) {
        /* Variables    */
        global $DB;
        $modCompletion              = null;
        $paramsModCompletion        = null;
        $gradeUser                  = null;
        $paramsGradeUser            = null;
        $completionCriteriaCompl    = null;
        $paramsCritCompl            = null;
        $typeInconsistency          = null;

        try {
            /* Search Criteria      - Modules Completion    */
            $paramsModCompletion = array();
            $paramsModCompletion['coursemoduleid']   = $criteria->module_instance;
            $paramsModCompletion['userid']           = $infoUser->id;
            /* Model Completion - User  */
            $modCompletion = $DB->get_record('course_modules_completion',$paramsModCompletion);

            /* Search Criteria  - Grades Grades User        */
            $paramsGradeUser    = array();
            $paramsGradeUser['userid']       = $infoUser->id;
            $paramsGradeUser['itemid']       = $criteria->grade_id;
            /* Grade User Activity      */
            $gradeUser = $DB->get_record('grade_grades',$paramsGradeUser);

            /* Search Criteria - Course Completion Criteria Compl   */
            $paramsCritCompl = array();
            $paramsCritCompl['userid']       = $infoUser->id;
            $paramsCritCompl['course']       = $courseID;
            $paramsCritCompl['criteriaid']   = $criteria->ccc_criteria;
            $completionCriteriaCompl = $DB->get_record('course_completion_crit_compl',$paramsCritCompl);

            /* Check Grade User Exists  */
            if ($gradeUser) {
                /* Activity Done        */
                if ($criteria->grade_pass) {
                    /* Min Grade to complete Activity   */
                    if ($gradeUser->finalgrade >= $criteria->grade_pass) {
                        /* Should Exists    */
                        if ($modCompletion) {
                            if ($modCompletion->completionstate != COMPLETION_COMPLETE_PASS) {
                                $typeInconsistency = ACT_COMPLETED;
                            }else {
                                /* Should Exist */
                                if (!$completionCriteriaCompl) {
                                    $typeInconsistency = ACT_COMPLETED;
                                }
                            }
                        }else {
                            $typeInconsistency = ACT_COMPLETED;
                        }
                    }else {
                        /* Failed   */
                        if ($infoUser->timecompleted) {
                            $typeInconsistency = ACT_FAILED;
                        }else {
                            if ($modCompletion) {
                                if ($modCompletion->completionstate != COMPLETION_COMPLETE_FAIL) {
                                    $typeInconsistency = ACT_FAILED;
                                }else {
                                    /* Should not exists    */
                                    if ($completionCriteriaCompl) {
                                        $typeInconsistency = ACT_FAILED;
                                    }
                                }
                            }else {
                                $typeInconsistency = ACT_FAILED;
                            }
                        }
                    }
                }else {
                    /* No Min Grade to complete the activity        */
                    if ($gradeUser->finalgrade) {
                        /* Should Exists    */
                        if (!$modCompletion) {
                            $typeInconsistency = ACT_COMPLETED;
                        }else {
                            if ($modCompletion->completionstate != COMPLETION_COMPLETE_PASS) {
                                $typeInconsistency = ACT_COMPLETED;
                            }
                            /* Should Exists    */
                            if (!$completionCriteriaCompl) {
                                $typeInconsistency = ACT_COMPLETED;
                            }
                        }
                    }else {
                        /* Not Done */
                        if ($infoUser->timecompleted) {
                            $typeInconsistency = ACT_NOT_DONE_GRADE;
                        }else {
                            if ($modCompletion) {
                                if (($modCompletion->completionstate != COMPLETION_INCOMPLETE) || ($modCompletion->completionstate != COMPLETION_VIEWED)) {
                                    $typeInconsistency = ACT_NOT_DONE_GRADE;
                                }
                            }else {
                                /* Should not exist */
                                if ($completionCriteriaCompl) {
                                    $typeInconsistency = ACT_NOT_DONE_GRADE;
                                }
                            }
                        }
                    }//if_finalGrade
                }//if_else_criteria_pass
            }else {
                /* Activity Not Done    */
                if ($infoUser->timecompleted) {
                    $typeInconsistency  = ACT_NOT_DONE;
                }else {
                    if ($modCompletion) {
                        if ($modCompletion->completionstate != COMPLETION_INCOMPLETE) {
                            $typeInconsistency  = ACT_NOT_DONE;
                        }else if (($modCompletion->completionstate == COMPLETION_INCOMPLETE) && ($infoUser->timecompleted)) {
                            $typeInconsistency  = ACT_NOT_DONE;
                        }
                    }else {
                        /* Should Not exist             */
                        if ($completionCriteriaCompl) {
                            $typeInconsistency  = ACT_NOT_DONE;
                        }//if_notExists
                    }//if_ModCompletion
                }
            }//if_gradeUser

            return $typeInconsistency;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TypeInconsistency_GradeActivity


    /**
     * @param           $infoUser
     * @param           $courseID
     * @param           $criteria
     * @return          int|null
     * @throws          Exception
     *
     * @creationDate    25/05/2015
     * @author          eFAktor     (fbv)
     *
     * Description
     * Get the inconsistency connected with the user and activity no grade
     */
    private static function Get_TypeInconsistency_NoGradeActivity($infoUser,$courseID,$criteria) {
        /* Variables    */
        global $DB;
        $modCompletion              = null;
        $paramsModCompletion        = null;
        $modCompletion              = null;
        $paramsCritCompl            = null;
        $completionCriteriaCompl    = null;
        $typeInconsistency          = null;

        try {
            /* Search Criteria      - Modules Completion    */
            $paramsModCompletion = array();
            $paramsModCompletion['coursemoduleid']   = $criteria->module_instance;
            $paramsModCompletion['userid']           = $infoUser->id;
            /* Model Completion - User  */
            $modCompletion = $DB->get_record('course_modules_completion',$paramsModCompletion);

            /* Search Criteria - Course Completion Criteria Compl   */
            $paramsCritCompl = array();
            $paramsCritCompl['userid']       = $infoUser->id;
            $paramsCritCompl['course']       = $courseID;
            $paramsCritCompl['criteriaid']   = $criteria->ccc_criteria;
            $completionCriteriaCompl = $DB->get_record('course_completion_crit_compl',$paramsCritCompl);

            if ($criteria->completion_view) {
                if ($modCompletion) {
                    if ($modCompletion->completionstate == COMPLETION_NOT_VIEWED) {
                        /* Not Viewed   */
                        if ($infoUser->timecompleted) {
                            $typeInconsistency  = ACT_NOT_VIEWED;
                        }else {
                            /* Should Not exist             */
                            if ($completionCriteriaCompl) {
                                $typeInconsistency  = ACT_NOT_VIEWED;
                            }//if_Exists
                        }
                    }else {
                        /* Viewed       */
                        if ($modCompletion->completionstate != COMPLETION_VIEWED) {
                            $typeInconsistency = ACT_VIEWED;
                        }else {
                            /* Should exist             */
                            if (!$completionCriteriaCompl) {
                                $typeInconsistency  = ACT_VIEWED;
                            }//if_Not_Exists
                        }
                    }//if_NOTVIEWED_VIEWED
                }else {
                    if ($infoUser->timecompleted) {
                        $typeInconsistency  = ACT_NOT_DONE;
                    }else {
                        /* Should Not exist             */
                        if ($completionCriteriaCompl) {
                            $typeInconsistency  = ACT_NOT_DONE;
                        }//if_Exists
                    }
                }//if_modCompletion
            }//if_completionView

            return $typeInconsistency;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TypeInconsistency_NoGradeActivity

    /**
     * @param           $user
     * @param           $course
     * @param           $courseReq
     * @param           $criteria
     * @return          int|null
     * @throws          Exception
     *
     * @creationDate    25/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get if there is any inconsistency with course pre req
     */
    private static function Get_TypeInconsistency_PreReqCourse($user,$course,$courseReq,$criteria) {
        /* Variables    */
        global $DB;
        $paramsReq                  = null;
        $reqCompletion              = null;
        $paramsCritCompl            = null;
        $completionCriteriaCompl    = null;
        $typeInconsistency          = null;

        try {
            /* Search Criteria - Course Req Completion  */
            $paramsReq = array();
            $paramsReq['userid']       = $user->id;
            $paramsReq['course']       = $courseReq;
            $reqCompletion = $DB->get_record('course_completions',$paramsReq);

            /* Search Criteria - Course Completion Criteria Compl   */
            $paramsCritCompl = array();
            $paramsCritCompl['userid']       = $user->id;
            $paramsCritCompl['course']       = $course;
            $paramsCritCompl['criteriaid']   = $criteria;
            $completionCriteriaCompl = $DB->get_record('course_completion_crit_compl',$paramsCritCompl);

            if ($reqCompletion) {
                if (!$reqCompletion->timecompleted) {
                    /* Should Not Exist */
                    if ($completionCriteriaCompl) {
                        $typeInconsistency = REQ_NOT_COMPLETED;
                    }else if ($user->timecompleted) {
                        $typeInconsistency = REQ_NOT_COMPLETED;
                    }
                }

            }else {
                if ($user->timecompleted) {
                    $typeInconsistency = REQ_NOT_COMPLETED;
                }else {
                    /* Should Not Exist */
                    if ($completionCriteriaCompl) {
                        $typeInconsistency = REQ_NOT_COMPLETED;
                    }
                }
            }

            return $typeInconsistency;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TypeInconsistency_PreReqCourse

    /**
     * @param           $infoInconsistency
     * @param           $gradeCriteria
     * @param           $basedOnMe
     * @throws          Exception
     *
     * @creationDate    25/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean inconsistency for the user
     */
    private static function CleanInconsistency($infoInconsistency,$gradeCriteria,$basedOnMe) {
        /* Variables    */
        global $DB;
        $modCompletion              = null;
        $completionCriteriaCompl    = null;
        $infoCompletion             = null;
        $cleanBasedOnMe             = null;

        /* Start Transaction    */
        $transaction = $DB->start_delegated_transaction();

        try {
            /* Search Criteria      - Modules Completion    */
            $paramsModCompletion = array();
            $paramsModCompletion['coursemoduleid']   = $infoInconsistency->coursemoduleid;
            $paramsModCompletion['userid']           = $infoInconsistency->userid;
            /* Model Completion - User  */
            $modCompletion = $DB->get_record('course_modules_completion',$paramsModCompletion);

            /* Search Criteria  - Grades Grades User        */
            $paramsGradeUser    = array();
            $paramsGradeUser['userid']       = $infoInconsistency->userid;
            $paramsGradeUser['itemid']       = $infoInconsistency->gradeitemid;

            /* Search Criteria - Course Completion Criteria Compl   */
            $paramsCritCompl = array();
            $paramsCritCompl['userid']       = $infoInconsistency->userid;
            $paramsCritCompl['course']       = $infoInconsistency->courseid;
            $paramsCritCompl['criteriaid']   = $infoInconsistency->criteriaid;
            $completionCriteriaCompl = $DB->get_record('course_completion_crit_compl',$paramsCritCompl);

            switch ($infoInconsistency->inconsistency) {
                case COURSE_COMPLETED:
                    /* Update Course Completion */
                    $infoCompletion = new stdClass();
                    $infoCompletion->id                 = $infoInconsistency->completionid;
                    $infoCompletion->userid             = $infoInconsistency->userid;
                    $infoCompletion->course             = $infoInconsistency->courseid;
                    $infoCompletion->timecompleted      = time();
                    $DB->update_record('course_completions',$infoCompletion);

                    break;
                case ACT_COMPLETED:
                    /* course_modules_completion    -- COMPLETION_COMPLETE_PASS */
                    if ($modCompletion) {
                        $modCompletion->completionstate = COMPLETION_COMPLETE_PASS;

                        /* Update Modules Completion for the user   */
                        $DB->update_record('course_modules_completion',$modCompletion);
                    }else {
                        /* Not Exist Modules Completion */
                        $modCompletion = new stdClass();
                        $modCompletion->coursemoduleid  = $infoInconsistency->coursemoduleid;
                        $modCompletion->userid          = $infoInconsistency->userid;
                        $modCompletion->completionstate = COMPLETION_COMPLETE_PASS;
                        $modCompletion->timemodified    = time();

                        /* Insert Record    */
                        $modCompletion->id = $DB->insert_record('course_modules_completion',$modCompletion);
                    }//if_mod_Completion

                    /* course_completion_crit_compl -- Should exist             */
                    $completionCriteriaCompl = $DB->get_record('course_completion_crit_compl',$paramsCritCompl);
                    if (!$completionCriteriaCompl) {
                        /* Create Record    */
                        $completionCriteriaCompl = new stdClass();
                        $completionCriteriaCompl->userid        = $infoInconsistency->userid;
                        $completionCriteriaCompl->course        = $infoInconsistency->courseid;
                        $completionCriteriaCompl->criteriaid    = $infoInconsistency->criteriaid;
                        $completionCriteriaCompl->timecompleted = time();

                        $DB->insert_record('course_completion_crit_compl',$completionCriteriaCompl);
                    }else {
                        /* Update Record    */
                        $completionCriteriaCompl->timecompleted = time();
                        /* Execute   */
                        $DB->update_record('course_completion_crit_compl',$completionCriteriaCompl);
                    }//if_notExists

                    break;
                case ACT_FAILED:
                    /* course_modules_completion    -- COMPLETION_COMPLETE_FAIL */
                    if ($modCompletion) {
                        $modCompletion->completionstate = COMPLETION_COMPLETE_FAIL;

                        /* Update Modules Completion for the user   */
                        $DB->update_record('course_modules_completion',$modCompletion);
                    }else {
                        /* Not Exist Modules Completion */
                        $modCompletion = new stdClass();
                        $modCompletion->coursemoduleid  = $infoInconsistency->coursemoduleid;
                        $modCompletion->userid          = $infoInconsistency->userid;
                        $modCompletion->completionstate = COMPLETION_COMPLETE_FAIL;
                        $modCompletion->timemodified    = time();

                        /* Insert Record    */
                        $modCompletion->id = $DB->insert_record('course_modules_completion',$modCompletion);
                    }//if_mod_Completion

                    /* Delete course_completion_crit_compl -- For Activity  */
                    $DB->delete_records('course_completion_crit_compl',$paramsCritCompl);

                    /* Delete Grade Course Criteria Completion              */
                    if ($gradeCriteria) {
                        /* Update Params    */
                        $paramsGradeUser['itemid']      = $gradeCriteria->grade_id;
                        $paramsCritCompl['criteriaid']  = $gradeCriteria->ccc_criteria;

                        /* Delete course_completion_crit_compl  -- For Course  */
                        $DB->delete_records('course_completion_crit_compl',$paramsCritCompl);
                    }//if_gradeCriteria

                    /* Update Course Completion */
                    $infoCompletion = new stdClass();
                    $infoCompletion->id                 = $infoInconsistency->completionid;
                    $infoCompletion->userid             = $infoInconsistency->userid;
                    $infoCompletion->course             = $infoInconsistency->courseid;
                    $infoCompletion->timecompleted      = null;
                    $DB->update_record('course_completions',$infoCompletion);
                    $cleanBasedOnMe = true;

                    break;
                case ACT_NOT_DONE_GRADE:
                case ACT_NOT_DONE:
                    /* course_modules_completion    -- COMPLETION_INCOMPLETE */
                    if ($modCompletion) {
                        $modCompletion->completionstate = COMPLETION_INCOMPLETE;

                        /* Update Modules Completion for the user   */
                        $DB->update_record('course_modules_completion',$modCompletion);
                    }else {
                        /* Not Exist Modules Completion */
                        $modCompletion = new stdClass();
                        $modCompletion->coursemoduleid  = $infoInconsistency->coursemoduleid;
                        $modCompletion->userid          = $infoInconsistency->userid;
                        $modCompletion->completionstate = COMPLETION_INCOMPLETE;
                        $modCompletion->timemodified    = time();

                        /* Insert Record    */
                        $modCompletion->id = $DB->insert_record('course_modules_completion',$modCompletion);
                    }//if_mod_Completion

                    $DB->delete_records('course_modules_completion',$paramsModCompletion);
                    /* Delete course_completion_crit_compl -- For Activity  */
                    $DB->delete_records('course_completion_crit_compl',$paramsCritCompl);

                    /* Delete Grade Course Criteria Completion              */
                    if ($gradeCriteria) {
                        /* Update Params    */
                        $paramsGradeUser['itemid']      = $gradeCriteria->grade_id;
                        $paramsCritCompl['criteriaid']  = $gradeCriteria->ccc_criteria;

                        /* Delete course_completion_crit_compl  -- For Course  */
                        $DB->delete_records('course_completion_crit_compl',$paramsCritCompl);
                    }//if_gradeCriteria

                    /* Update Course Completion */
                    $infoCompletion = new stdClass();
                    $infoCompletion->id             = $infoInconsistency->completionid;
                    $infoCompletion->userid         = $infoInconsistency->userid;
                    $infoCompletion->course         = $infoInconsistency->courseid;
                    $infoCompletion->timecompleted  = null;
                    $DB->update_record('course_completions',$infoCompletion);
                    $cleanBasedOnMe = true;

                    break;
                case ACT_VIEWED:
                    /* course_modules_completions -- COMPLETION_VIEWED  */
                    if ($modCompletion) {
                        $modCompletion->completionstate = COMPLETION_VIEWED;

                        /* Update Modules Completion for the user   */
                        $DB->update_record('course_modules_completion',$modCompletion);
                    }else {
                        /* Not Exist Modules Completion */
                        $modCompletion = new stdClass();
                        $modCompletion->coursemoduleid  = $infoInconsistency->coursemoduleid;
                        $modCompletion->userid          = $infoInconsistency->userid;
                        $modCompletion->completionstate = COMPLETION_VIEWED;
                        $modCompletion->timemodified    = time();

                        /* Insert Record    */
                        $modCompletion->id = $DB->insert_record('course_modules_completion',$modCompletion);
                    }//if_mod_Completion

                    /* course_completion_crit_complt  --   */
                    $completionCriteriaCompl = $DB->get_record('course_completion_crit_compl',$paramsCritCompl);
                    if (!$completionCriteriaCompl) {
                        /* Create Record    */
                        $completionCriteriaCompl = new stdClass();
                        $completionCriteriaCompl->userid        = $infoInconsistency->userid;
                        $completionCriteriaCompl->course        = $infoInconsistency->courseid;
                        $completionCriteriaCompl->criteriaid    = $infoInconsistency->criteriaid;
                        $completionCriteriaCompl->timecompleted = time();

                        $DB->insert_record('course_completion_crit_compl',$completionCriteriaCompl);
                    }else {
                        /* Update Record    */
                        $completionCriteriaCompl->timecompleted = time();
                        /* Execute   */
                        $DB->update_record('course_completion_crit_compl',$completionCriteriaCompl);
                    }//if_notExists

                    break;
                case ACT_NOT_VIEWED:
                    /* course_modules_completions -- COMPLETION_NOT_VIEWED  */
                    if ($modCompletion) {
                        $modCompletion->completionstate = COMPLETION_NOT_VIEWED;

                        /* Update Modules Completion for the user   */
                        $DB->update_record('course_modules_completion',$modCompletion);
                    }else {
                        /* Not Exist Modules Completion */
                        $modCompletion = new stdClass();
                        $modCompletion->coursemoduleid  = $infoInconsistency->coursemoduleid;
                        $modCompletion->userid          = $infoInconsistency->userid;
                        $modCompletion->completionstate = COMPLETION_NOT_VIEWED;
                        $modCompletion->timemodified    = time();

                        /* Insert Record    */
                        $modCompletion->id = $DB->insert_record('course_modules_completion',$modCompletion);
                    }//if_mod_Completion

                    /* Delete course_completion_crit_compl -- For Activity  */
                    $DB->delete_records('course_completion_crit_compl',$paramsCritCompl);

                    /* Delete Grade Course Criteria Completion              */
                    if ($gradeCriteria) {
                        /* Update Params    */
                        $paramsGradeUser['itemid']      = $gradeCriteria->grade_id;
                        $paramsCritCompl['criteriaid']  = $gradeCriteria->ccc_criteria;

                        /* Delete course_completion_crit_compl  -- For Course  */
                        $DB->delete_records('course_completion_crit_compl',$paramsCritCompl);
                    }//if_gradeCriteria

                    /* Update Course Completion */
                    $infoCompletion = new stdClass();
                    $infoCompletion->id                 = $infoInconsistency->completionid;
                    $infoCompletion->userid             = $infoInconsistency->userid;
                    $infoCompletion->course             = $infoInconsistency->courseid;
                    $infoCompletion->timecompleted      = null;
                    $DB->update_record('course_completions',$infoCompletion);
                    $cleanBasedOnMe = true;

                    break;
                case REQ_NOT_COMPLETED:
                    /* Delete course_completion_crit_compl -- For Activity  */
                    $DB->delete_records('course_completion_crit_compl',$paramsCritCompl);

                    /* Delete Grade Course Criteria Completion              */
                    if ($gradeCriteria) {
                        /* Update Params    */
                        $paramsGradeUser['itemid']      = $gradeCriteria->grade_id;
                        $paramsCritCompl['criteriaid']  = $gradeCriteria->ccc_criteria;

                        /* Delete course_completion_crit_compl  -- For Course  */
                        $DB->delete_records('course_completion_crit_compl',$paramsCritCompl);
                    }//if_gradeCriteria

                    /* Update Course Completion */
                    $infoCompletion = new stdClass();
                    $infoCompletion->id                 = $infoInconsistency->completionid;
                    $infoCompletion->userid             = $infoInconsistency->userid;
                    $infoCompletion->course             = $infoInconsistency->courseid;
                    $infoCompletion->timecompleted      = null;
                    $DB->update_record('course_completions',$infoCompletion);
                    $cleanBasedOnMe = true;

                    break;
                default:
                    break;
            }//swiotch

            /* Remote Criteria Completion From the courses based on me  */
            if ($cleanBasedOnMe) {
                foreach ($basedOnMe as $course=>$criteria) {
                    /* Params   */
                    $paramsCritCompl['userid']       = $infoInconsistency->userid;
                    $paramsCritCompl['course']       = $infoInconsistency->courseid;
                    $paramsCritCompl['criteriaid']   = $criteria;
                    /* Delete Completion Criteria Compl - User */
                    $DB->delete_records('course_completion_crit_compl',$paramsCritCompl);

                    /* Update Completion Course */
                    $params = array();
                    $params['course'] = $course;
                    $params['userid'] = $infoInconsistency->userid;
                    $infoCompletion = $DB->get_record('course_completions',$params);
                    if ($infoCompletion) {
                        $infoCompletion->timecompleted = null;
                        $DB->update_record('course_completions',$infoCompletion);
                    }//infoCompletion
                }//for_each
            }//if_cleanBasedOnMe

            /* Update Course Inconsistency Record   */
            $infoInconsistency->fixed = 1;
            $DB->update_record('course_inconsistencies',$infoInconsistency);

            /* Commit   */
            $transaction->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $transaction->rollback($ex);

            throw $ex;
        }//try_catch
    }//CleanInconsistency
}//InconsistenciesCompletions