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
    public static function suspend_user($suspend) {
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
    }//suspend_user


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
    public static  function unsuspend_user($unsuspend) {
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
    }//unsuspend_user

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
    public static function delete_user($delete,$confirm,$url) {
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
    }//delete_user

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
    public static function get_users_cohort_category($context_cat,$user_id,$sort,$dir,$offset=0, $limit=0,$extra_sql,&$params) {
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
                $my_cohorts = self::get_my_cohorts($user_id,$context_cat);
                $sql        = self::get_users_cohort_category_sql_not_admin($extra_sql,$my_cohorts);
            }else {

                $sql = self::get_users_cohort_category_sql_admin($extra_sql);
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
                    $user->roles        = ($instance->jobroles ? self::get_jobroles_name($instance->jobroles) :'');
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
    }//get_users_cohort_category

    /**
     * Description
     * Display table with users
     *
     * @param           array   $lstusers
     * @param           object  $context_cat
     * @param                   $url
     * @param                   $sort
     * @param                   $field
     *
     * @return                  null|string
     * @throws                  Exception
     *
     * @creationDate    25/04/2017
     * @author          eFaktor     (fbv)
     */
    public static function display_myusers($lstusers,$context_cat,$url,$sort,$field) {
        /* Variables */
        $out = null;

        try {
            // My users block
            $out .= html_writer::start_div('block_my_users');
                // My users table
                $out .= html_writer::start_tag('table',array('class' => 'generaltable'));
                    // Header
                    $out .= self::add_header_myusers_table($context_cat->instanceid,$sort,$field);
                    // Content
                    $out .= self::add_content_myusers_table($lstusers,$context_cat,$url);
                $out .= html_writer::end_tag('table');
            $out .= html_writer::end_div();//block_my_users

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//display_myusers

    /********************/
    /* PRIVATE STATIC   */
    /********************/

    /**
     * Description
     * Add the table header
     *
     * @param           object $cat
     * @param                  $dir
     * @param                  $field
     *
     * @return                  null|string
     * @throws                  Exception
     *
     * @creationDate    25/04/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_header_myusers_table($cat,$dir,$field) {
        /* Variables */
        $header     = null;
        $dirfirst   = null;
        $dirlast    = null;
        // Headers
        $strCohort      = get_string('cohort','cohort');
        $strCompany     = get_string('company','local_myusers');
        $strJobroles    = get_string('job_roles','local_myusers');
        $strFirst       = get_string('firstname');
        $strLast        = get_string('lastname');
        $strMail        = get_string('email');
        $strAccess      = get_string('lastaccess');
        $strEdit        = get_string('edit');
        $urlFirst       = null;
        $urlLast        = null;
        $urlAccess      = null;
        $sort           = null;

        try {
            switch ($dir) {
                case 'ASC':
                    $sort = "DESC";
                    break;

                case 'DESC':
                    $sort = 'ASC';
                    break;

                default:
                    $sort = 'ASC';
                    break;
            }
            // Set order
            switch ($field) {
                case 'firstname':
                    $urlFirst   = "<a href=\"myusers.php?id=$cat&amp;sort=$field&amp;dir=$sort;&amp;order='1'\">".$strFirst."</a>";
                    $urlLast    = "<a href=\"myusers.php?id=$cat&amp;sort='lastname'&amp;dir='ASC'\">".$strLast."</a>";
                    $urlAccess  = "<a href=\"myusers.php?id=$cat&amp;sort='lastaccess'&amp;dir='ASC'\">".$strAccess."</a>";

                    break;

                case 'lastname':
                    $urlFirst   = "<a href=\"myusers.php?id=$cat&amp;sort='firstname'&amp;dir='ASC'\">".$strFirst."</a>";
                    $urlLast    = "<a href=\"myusers.php?id=$cat&amp;sort=$field&amp;dir=$sort\">".$strLast."</a>";
                    $urlAccess  = "<a href=\"myusers.php?id=$cat&amp;sort='lastaccess'&amp;dir='ASC'\">".$strAccess."</a>";

                    break;

                case 'lastaccess':
                    $urlFirst   = "<a href=\"myusers.php?id=$cat&amp;sort='firstname'&amp;dir='ASC'\">".$strFirst."</a>";
                    $urlLast    = "<a href=\"myusers.php?id=$cat&amp;sort='lastname'&amp;dir='ASC'\">".$strLast."</a>";
                    $urlAccess  = "<a href=\"myusers.php?id=$cat&amp;sort=$field&amp;dir=$sort\">".$strAccess."</a>";

                    break;

                default:
                    $urlFirst   = "<a href=\"myusers.php?id=$cat&amp;sort='firstname'&amp;dir='ASC'\">".$strFirst."</a>";
                    $urlLast    = "<a href=\"myusers.php?id=$cat&amp;sort='lastname'&amp;dir='ASC'\">".$strLast."</a>";
                    $urlAccess  = "<a href=\"myusers.php?id=$cat&amp;sort='lastaccess'&amp;dir='ASC'\">".$strAccess."</a>";

                    break;
            }//fieldSort

            // Add Headers
            $header .=  html_writer::start_tag('thead');
                $header .=  html_writer::start_tag('tr',array('class' => 'header_users'));
                    // Firstname - Lastname
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $urlFirst . ' / ' . $urlLast;
                    $header .= html_writer::end_tag('th');
                    // Cohort
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strCohort;
                    $header .= html_writer::end_tag('th');
                    // eMail
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strMail;
                    $header .= html_writer::end_tag('th');
                    // Company
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strCompany;
                    $header .= html_writer::end_tag('th');
                    // Job roles
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strJobroles;
                    $header .= html_writer::end_tag('th');
                    // Last Access
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $urlAccess;
                    $header .= html_writer::end_tag('th');
                    // Action
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strEdit;
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('thead');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_myusers_table

    /**
     * Description
     * Add the content of the table
     *
     * @param           array  $lstusers
     * @param           object $context_cat
     * @param                  $url
     *
     * @return                 null|string
     * @throws                 Exception
     *
     * @creationDate    25/04/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_content_myusers_table($lstusers,$context_cat,$url) {
        /* Variables */
        $content        = null;
        $urlUser        = null;
        $lnkUser        = null;
        // Headers
        $strCohort      = get_string('cohort','cohort');
        $strCompany     = get_string('company','local_myusers');
        $strJobroles    = get_string('job_roles','local_myusers');
        $strUser        = get_string('user');
        $strFirst       = get_string('firstname');
        $strLast        = get_string('lastname');
        $strMail        = get_string('email');
        $strAccess      = get_string('lastaccess');
        $strEdit        = get_string('edit');

        try {
            if ($lstusers) {
                $urlUser = new moodle_url('/user/profile.php');
                foreach ($lstusers as $user) {
                    $urlUser->param('id',$user->id);
                    $content .=  html_writer::start_tag('tr');
                        // Firstname - Lastname
                        $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strFirst . ' / ' . $strLast));
                            $lnkUser = '<a href="'. $urlUser . '">'. $user->firstname . ' ' . $user->lastname .'</a>';
                            $content .= $lnkUser;
                        $content .= html_writer::end_tag('td');
                        // Cohort
                        $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strCohort));
                            $content .= $user->cohort;
                        $content .= html_writer::end_tag('td');
                        // eMail
                        $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strMail));
                            $content .= $user->email;
                        $content .= html_writer::end_tag('td');
                        // Company
                        $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strCompany));
                            $content .= $user->company;
                        $content .= html_writer::end_tag('td');
                        // Job roles
                        $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strJobroles));
                            $content .= $user->roles;
                        $content .= html_writer::end_tag('td');
                        // Last Access
                        $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strAccess));
                            $content .= ($user->lastaccess ? format_time(time() - $user->lastaccess) : get_string('never'));
                        $content .= html_writer::end_tag('td');
                        // Action
                        $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strEdit));
                            $content .= self::add_actions_link($user,$context_cat,$url);

                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_lstusrs
            }//if_lstusers

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_content_myusers_table

    /**
     * Description
     * Add action links
     * 
     * @param           $user
     * @param           $context
     * @param           $url
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    25/04/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_actions_link($user,$context,$url) {
        /* Variables */
        global $USER,$OUTPUT,$DB;
        global $SESSION,$SITE;
        $actions    = null;
        $buttons    = array();
        $strEdit    = get_string('edit');
        $strDel     = get_string('delete');
        $strSus     = get_string('suspenduser', 'admin');
        $strUnsus   = get_string('unsuspenduser', 'admin');
        $strUnlock  = get_string('unlockaccount', 'admin');

        try {
            if (self::can_edit_user($user->id,$context)) {
                // delete button
                if (has_capability('moodle/user:delete', $context)) {
                    if (is_mnet_remote_user($user) or $user->id == $USER->id or is_siteadmin($user)) {
                        // no deleting of self, mnet accounts or admins allowed
                    } else {
                        $buttons[] = html_writer::link(new moodle_url($url, array('delete'=>$user->id, 'sesskey'=>sesskey())),
                                                       html_writer::empty_tag('img',
                                                                               array('src'   => $OUTPUT->pix_url('t/delete'), 'alt'=>$strDel, 'class'=>'iconsmall')),
                                                                               array('title' => $strDel));
                    }
                }

                // suspend button
                if (has_capability('moodle/user:update', $context)) {
                    if (is_mnet_remote_user($user)) {
                        // mnet users have special access control, they can not be deleted the standard way or suspended
                        $accessctrl = 'allow';
                        if ($acl = $DB->get_record('mnet_sso_access_control', array('username'=>$user->username, 'mnet_host_id'=>$user->mnethostid))) {
                            $accessctrl = $acl->accessctrl;
                        }
                        $changeaccessto = ($accessctrl == 'deny' ? 'allow' : 'deny');
                        $buttons[] = " (<a href=\"?acl={$user->id}&amp;accessctrl=$changeaccessto&amp;sesskey=".sesskey()."\">".get_string($changeaccessto, 'mnet') . " access</a>)";

                    } else {
                        if ($user->suspended) {
                            $buttons[] = html_writer::link(new moodle_url($url, array('unsuspend'=>$user->id, 'sesskey'=>sesskey())),
                                                           html_writer::empty_tag('img',
                                                                                   array('src'   =>$OUTPUT->pix_url('t/show'), 'alt'=>$strUnsus, 'class'=>'iconsmall')),
                                                                                   array('title' =>$strUnsus));
                        } else {
                            if ($user->id == $USER->id or is_siteadmin($user)) {
                                // no suspending of admins or self!
                            } else {
                                $buttons[] = html_writer::link(new moodle_url($url, array('suspend'=>$user->id, 'sesskey'=>sesskey())),
                                                               html_writer::empty_tag('img',
                                                                                       array('src'=>$OUTPUT->pix_url('t/hide'), 'alt'=>$strSus, 'class'=>'iconsmall')),
                                                                                       array('title'=>$strSus));
                            }
                        }

                        if (login_is_lockedout($user)) {
                            $buttons[] = html_writer::link(new moodle_url($url, array('unlock'=>$user->id, 'sesskey'=>sesskey())),
                                                           html_writer::empty_tag('img',
                                                                                   array('src'=>$OUTPUT->pix_url('t/unlock'), 'alt'=>$strUnlock, 'class'=>'iconsmall')),
                                                                                   array('title'=>$strUnlock));
                        }
                    }
                }

                // edit button
                if (has_capability('moodle/user:update', $context)) {
                    // prevent editing of admins by non-admins
                    if (is_siteadmin($USER) or !is_siteadmin($user)) {
                        $SESSION->cat = $context->instanceid;
                        $buttons[] = html_writer::link(new moodle_url('/local/myusers/editmyusers.php', array('id'=>$user->id, 'course'=>$SITE->id)),
                                                       html_writer::empty_tag('img',
                                                                               array('src'=>$OUTPUT->pix_url('t/edit'), 'alt'=>$strEdit, 'class'=>'iconsmall')),
                                                                               array('title'=>$strEdit));
                    }
                }

                $actions = implode(' ' ,$buttons);
            }else {
                $actions = ' ';
            }//if_else;

            return $actions ;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_actions_link

    /**
     * @param           $userid
     * @param           $context_cat
     * @return          bool
     *
     * @creationDate    30/01/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the current user can edit a specific user.
     */
    private static function can_edit_user($userid,$context_cat) {
        if (is_siteadmin($userid)) {
            return true;
        }else if (has_capability('moodle/category:manage', $context_cat,$userid)){
            return false;
        }else {
            return true;
        }//if_else
    }//can_edit_user

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
    private static function get_users_cohort_category_sql_not_admin($extra_sql,$my_cohorts) {
        /* Variables */
        $sql = null;
        $cohorts_lst = 0;

        if ($my_cohorts) {
            $cohorts_lst = implode(',',$my_cohorts);
        }//my_cohorts

        // SQL Instruction
        $sql = " SELECT	      CONCAT(c.id,'_',u.id) as 'cu_id',
                              c.id as 'cohortid',
                              u.id,
                              c.name,
                              u.firstname,
                              u.lastname,
                              u.suspended,
                              u.mnethostid,
                              u.email,
                              u.city,
                              u.country,
                              GROUP_CONCAT(DISTINCT CONCAT(co.industrycode,' - ',co.name) ORDER BY co.name SEPARATOR '</br>') as 'company',
                              uid.jobroles,
                              u.lastaccess
                  FROM		  {cohort}			   			c
                    JOIN	  {cohort_members}	   			cm	    ON		cm.cohortid	    = c.id
                    JOIN	  {user}						u		ON		u.id			= cm.userid
                                                                        AND     u.deleted       = 0
                    LEFT JOIN {user_info_competence_data}	uid		ON 		uid.userid 		= u.id
                    LEFT JOIN {report_gen_companydata}		co		ON		co.id			= uid.companyid
                  WHERE       c.id IN ($cohorts_lst) ";

        if ($extra_sql) {
            $sql .= " AND " . str_replace('cohort','c.idnumber',$extra_sql);
        }//if_extra_sql

        // Group by
        $sql .= ' GROUP BY c.id,u.id ';

        return $sql;
    }//get_users_cohort_category_sql_not_admin

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
    private static function get_users_cohort_category_sql_admin($extra_sql) {
        /* Variables */
        $sql = null;

        // SQL Instruction
        $sql = " SELECT	      CONCAT(c.id,'_',u.id) as 'cu_id',
                              c.id as 'cohortid',
                              u.id,
                              c.name,
                              u.firstname,
                              u.lastname,
                              u.suspended,
                              u.mnethostid,
                              u.email,
                              u.city,
                              u.country,
                              GROUP_CONCAT(DISTINCT CONCAT(co.industrycode,' - ',co.name) ORDER BY co.name SEPARATOR '</br>') as 'company',
                              uid.jobroles,
                              u.lastaccess
                 FROM		  {cohort}			   			c
                    JOIN	  {cohort_members}	   			cm	ON	cm.cohortid = c.id
                    JOIN	  {user}						u	ON	u.id		= cm.userid
                                                                AND u.deleted   = 0
                    LEFT JOIN {user_info_competence_data}	uid	ON 	uid.userid 	= u.id
                    LEFT JOIN {report_gen_companydata}		co	ON	co.id		= uid.companyid
                 WHERE        c.contextid = :context ";

        if ($extra_sql) {
            $sql .= 'AND ' . str_replace('cohort','c.idnumber',$extra_sql);
        }//if_extra_sql

        // Group by
        $sql .= ' GROUP BY c.id,u.id ';

        return $sql;
    }//get_users_cohort_category_sql_admin

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
    private static function get_my_cohorts($user_id,$context_cat) {
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
    }//get_my_cohorts

    /**
     * Description
     * Get the jobroles connected to the user
     *
     * @param           String  $jobroles
     *
     * @return                  null
     * @throws                  Exception
     *
     * @creationDate    30/01/2014
     * @author          eFaktor     (fbv)
     */
    private static function get_jobroles_name($jobroles) {
        /* Variables */
        global $DB;
        $rdo = null;
        $sql = null;
        $jr_lst = null;

        try {
            if (substr($jobroles,0,1) == ',') {
                $jr_lst = substr($jobroles,1);
            }else {
                $jr_lst = $jobroles;
            }

            //SQL Instruction
            $sql = " SELECT   GROUP_CONCAT(DISTINCT jr.name ORDER BY jr.name SEPARATOR ',</br> ') as 'job_roles'
                     FROM	  {report_gen_jobrole} jr
                     WHERE	  jr.id IN ($jr_lst)
                     ORDER BY jr.name ";

            // Execute
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                return $rdo->job_roles;
            }else {
                return null;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_jobroles_name
}//MyUsers