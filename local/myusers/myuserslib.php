<?php
/**
 * Users Admin - Category plugin - Library
 *
 * Description
 *
 * @package         local
 * @subpackage      myusers
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      04/11/2014
 * @author          eFaktor     (fbv)
 *
 */

class MyUsers {
    /* PUBLIC STATIC    */
    /**
     * @param           $suspend
     * @throws          Exception
     *
     * @creationDate    30/01/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Suspend the user
     */
    public static function SuspendUser($suspend) {
        global $DB, $USER, $CFG;

        try {
            if ($user = $DB->get_record('user', array('id'=>$suspend, 'mnethostid'=>$CFG->mnet_localhost_id, 'deleted'=>0))) {
                if (!is_siteadmin($user) and $USER->id != $user->id and $user->suspended != 1) {
                    $user->suspended = 1;
                    // Force logout.
                    \core\session\manager::kill_user_sessions($user->id);
                    user_update_user($user, false);
                }
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SuspendUser


    /**
     * @param           $unsuspend
     * @throws          Exception
     *
     * @creationDate    30/01/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unsuspend the user
     */
    public static  function UnsuspendUser($unsuspend) {
        global $DB, $CFG;

        try {
            if ($user = $DB->get_record('user', array('id'=>$unsuspend, 'mnethostid'=>$CFG->mnet_localhost_id, 'deleted'=>0))) {
                if ($user->suspended != 0) {
                    $user->suspended = 0;
                    user_update_user($user, false);
                }
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UnsuspendUser

    /**
     * @param           $delete
     * @param           $confirm
     * @param           $url
     * @throws          Exception
     *
     * @creationDate    30/01/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete the user.
     */
    public static function DeleteUser($delete,$confirm,$url) {
        global $DB, $CFG,$OUTPUT;

        try {
            $user = $DB->get_record('user', array('id'=>$delete, 'mnethostid'=>$CFG->mnet_localhost_id), '*', MUST_EXIST);

            if (is_siteadmin($user->id)) {
                print_error('useradminodelete', 'error');
            }

            if ($confirm != md5($delete)) {
                echo $OUTPUT->header();
                $fullname = fullname($user, true);
                echo $OUTPUT->heading(get_string('deleteuser', 'admin'));
                $optionsyes = array('delete'=>$delete, 'confirm'=>md5($delete), 'sesskey'=>sesskey());
                echo $OUTPUT->confirm(get_string('deletecheckfull', '', "'$fullname'"), new moodle_url($url, $optionsyes), $url);
                echo $OUTPUT->footer();
                die;
            } else if (data_submitted() and !$user->deleted) {
                if (delete_user($user)) {
                    \core\session\manager::gc(); // Remove stale sessions.
                    redirect($url);
                } else {
                    \core\session\manager::gc(); // Remove stale sessions.
                    echo $OUTPUT->header();
                    echo $OUTPUT->notification($url, get_string('deletednot', '', fullname($user, true)));
                }
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//DeleteUser

    /**
     * @param           $user_id
     * @param           $my_user
     * @param           $context_cat
     * @return          bool
     *
     * @creationDate    30/01/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the current user can edit a specific user.
     */
    public static function CanEditUser($user_id,$my_user,$context_cat) {
        if (is_siteadmin($user_id)) {
            return true;
        }else if (has_capability('moodle/category:manage', $context_cat,$my_user)){
            return false;
        }else {
            return true;
        }//if_else
    }//CanEditUser

    /**
     * @param           $context_cat
     * @param           $user_id
     * @param           $sort
     * @param           $dir
     * @param       int $offset
     * @param       int $limit
     * @param           $extra_sql
     * @param           $params
     * @return          array
     * @throws          Exception
     *
     * @creationDate    30/01/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get a list of all the users connected with the same cohort the category given and the category admin user/admin site.
     */
    public static function GetUsersCohortCategory($context_cat,$user_id,$sort,$dir,$offset=0, $limit=0,$extra_sql,&$params) {
        /* Variables    */
        global $DB;
        $my_cohorts = null;

        try {
            /* Users list   */
            $user_lst = array();

            /* Search Criteria  */
            $params['context'] = $context_cat;
            $params['user_id'] = $user_id;

            /* SQL Instruction  */
            if (!is_siteadmin($user_id)) {
                $my_cohorts = self::GetMy_Cohorts($user_id,$context_cat);
                $sql        = self::GetUsersCohortCategory_SqlNotAdmin($extra_sql,$my_cohorts);
            }else {

                $sql = self::GetUsersCohortCategory_SqlAdmin($extra_sql);
            }//if_else

            $sql .= " ORDER BY " . str_replace('cohort','c.name',$sort) . " " . $dir;

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params,$offset,$limit);
            if ($rdo) {
                foreach ($rdo as $instance) {

                    /* Add User */
                    $user = new stdClass();
                    $user->id           = $instance->id;
                    $user->cohort       = $instance->name;
                    $user->firstname    = $instance->firstname;
                    $user->lastname     = $instance->lastname;
                    $user->suspended    = $instance->suspended;
                    $user->mnethostid   = $instance->mnethostid;
                    $user->email        = $instance->email;
                    $user->company      = $instance->company;
                    $user->roles        = self::GetJobRolesUser_Name($instance->id);
                    $user->city         = $instance->city;
                    $user->country      = $instance->country;
                    $user->lastaccess   = $instance->lastaccess;

                    $user_lst[$instance->cu_id] = $user;
                }//for_rdo_user

            }//if_rdo

            return $user_lst;
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsersCohortCategory



    /********************/
    /* PRIVATE STATIC   */
    /********************/

    /**
     * @param           $extra_sql
     * @param           $my_cohorts
     * @return          string
     *
     * @creationDate    30/01/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Build the SQL to get all the users connected with the cohort of the category admin user.
     * The category admin user is not the admin user.
     */
    private static function GetUsersCohortCategory_SqlNotAdmin($extra_sql,$my_cohorts) {
        /* Variables */
        $sql = null;
        $cohorts_lst = 0;

        if ($my_cohorts) {
            $cohorts_lst = implode(',',$my_cohorts);
        }//my_cohorts

        $sql = " SELECT		CONCAT(c.id,'_',u.id) as 'cu_id',
                            u.id,
                            c.name,
                            u.firstname,
                            u.lastname,
                            u.suspended,
                            u.mnethostid,
                            u.email,
                            u.city,
                            u.country,
                            uc.company,
                            u.lastaccess
                FROM		{cohort}				c
                    JOIN	{cohort_members}		cm		ON		cm.cohortid 	= c.id
                    JOIN	{user}					u		ON		u.id			= cm.userid
                                                            AND     u.deleted       = 0
                    LEFT JOIN		(
                                     SELECT		uid.userid,
                                                rgc.name as 'company'
                                     FROM		{user_info_data}			uid
                                        JOIN	{user_info_field}			uif		ON		uif.id			= uid.fieldid
                                                                                    AND		uif.shortname	= 'rgcompany'
                                        JOIN	{report_gen_companydata}	rgc		ON		rgc.id			= uid.data
                                     ) uc ON uc.userid = u.id
                WHERE		c.id IN ($cohorts_lst) ";

        if ($extra_sql) {
            $sql .= " AND " . str_replace('cohort','c.idnumber',$extra_sql);
        }//if_extra_sql

        return $sql;
    }//GetUsersCohortCategory_SqlNotAdmin

    /**
     * @param           $extra_sql
     * @return          string
     *
     * @creationDate    30/01/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Build the SQL to get all the users connected with the category's cohort
     */
    private static function GetUsersCohortCategory_SqlAdmin($extra_sql) {
        /* Variables */
        $sql = null;

        $sql = " SELECT		CONCAT(c.id,'_',u.id) as 'cu_id',
                            c.id,
                            u.id,
                            c.name,
                            u.firstname,
                            u.lastname,
                            u.suspended,
                            u.mnethostid,
                            u.email,
                            u.city,
                            u.country,
                            uc.company,
                            u.lastaccess
                 FROM		    {cohort}				    c
                    JOIN	    {cohort_members}		    cm	    ON		cm.cohortid	    = c.id
                    JOIN	    {user}					    u		ON		u.id			= cm.userid
                                                                    AND     u.deleted       = 0
                    LEFT JOIN		(
                                    SELECT		uid.userid,
                                                rgc.name as 'company'
                                    FROM		{user_info_data}			uid
                                        JOIN	{user_info_field}			uif		ON		uif.id			= uid.fieldid
                                                                                    AND		uif.shortname	= 'rgcompany'
                                        JOIN	{report_gen_companydata}	rgc		ON		rgc.id			= uid.data
                                ) uc ON uc.userid = u.id
                 WHERE      c.contextid = :context
              ";

        if ($extra_sql) {
            $sql .= 'AND ' . str_replace('cohort','c.idnumber',$extra_sql);
        }//if_extra_sql

        return $sql;
    }//GetUsersCohortCategory_SqlAdmin

    /**
     * @static
     * @param           $user_id
     * @param           $context_cat
     * @return          array
     * @throws          Exception
     *
     * @creationDate    04/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all cohorts connected to the user
     */
    private static function GetMy_Cohorts($user_id,$context_cat) {
        /* Variables    */
        global $DB;
        $my_cohorts = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['user_id'] = $user_id;
            $params['context'] = $context_cat;

            /* SQL Instruction  */
            $sql = " SELECT 	cm.cohortid
                     FROM		{cohort_members}		cm
                        JOIN	{cohort}				c	ON  c.id 			= cm.cohortid
                                                            AND c.contextid		= :context
                     WHERE		cm.userid = :user_id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $cohort) {
                    $my_cohorts[$cohort->cohortid] = $cohort->cohortid;
                }
            }//if_rdo

            return $my_cohorts;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetMy_Cohorts

    /**
     * @static
     * @param           $user_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    30/01/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the jobroles connected to the user
     */
    private static function GetJobRolesUser_Name($user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Params   */
            $params = array();
            $params['user_id'] = $user_id;

            /* SQL Isntructiton  */
            $sql = " SELECT		uid.data
                     FROM		{user_info_data}	uid
                        JOIN	{user_info_field}   uif		ON		uif.id		    = uid.fieldid
                                                            AND		uif.datatype	= 'rgjobrole'
                     WHERE		uid.userid = :user_id ";
            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $jr_lst = $rdo->data;

                if (substr($jr_lst,0,1) == ',') {
                    $jr_lst = substr($jr_lst,1);
                }
                if ($jr_lst) {
                    /* SQL Instruction  */
                    $sql = " SELECT		GROUP_CONCAT(DISTINCT rg_jr.name ORDER BY rg_jr.name SEPARATOR ',</br> ') as 'job_roles'
                             FROM		{report_gen_jobrole}  rg_jr
                             WHERE      rg_jr.id IN ($jr_lst) ";

                    /* Execute  */
                    $rdo = $DB->get_record_sql($sql);
                    if ($rdo) {
                        return $rdo->job_roles;
                    }else {
                        return null;
                    }//if_else_rdo
                }else {
                    return null;
                }

            }else {
                return null;
            }//if_else_rdo


        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetJobRolesUser_Name
}//MyUsers