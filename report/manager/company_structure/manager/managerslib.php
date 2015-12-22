<?php
/**
 * Report Competence Manager - Managers Library
 *
 * Description
 *
 * @package         report/manager
 * @subpackage      company_structure/manager
 * @copyright       2010 eFaktor
 *
 * @creationDate    21/12/2015
 * @author          eFaktor     (fbv)
 *
 */
define('MAX_MANAGERS_SELECTOR_PAGE',100);

Class Managers {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $addSearch
     * @param           $removeSearch
     * @param           $level
     * @param           $company
     *
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise selectors to add and remove managers to/from the company
     *
     */
    public static function Init_Managers_Selectors($addSearch,$removeSearch,$level,$company) {
        /* Variables */
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;
        $hashAdd    = null;
        $hashRemove = null;

        try {
            /* Initialise variables */
            $name       = 'manager_selector';
            $path       = '/report/manager/company_structure/manager/js/search.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpOne     = array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%');
            $grpTwo     = array('nomatchingusers', 'moodle', '%%SEARCHTERM%%');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpOne,$grpTwo,$grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
                             );

            /* Super Users - Add Selector       */
            self::Init_Managers_AddSelector($addSearch,$jsModule,$level,$company);
            /* Super Users - Remove Selector    */
            self::Init_Managers_RemoveSelector($removeSearch,$jsModule,$level,$company);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Managers_Selectors

    /**
     * @param           $search
     * @param           $company
     * @param           $level
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find the managers connected with the company
     */
    public static function FindManagers_Selector($search,$company,$level) {
        /* Variables */
        global $DB;
        $availableManagers  = array();
        $managers           = array();
        $sql                = null;
        $params             = null;
        $rdo                = null;
        $locate             = '';
        $extra              = null;
        $groupName          = null;
        $total              = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['company']  = $company;
            $params['level']    = $level;

            /* SQL Instruction */
            $sql = " SELECT	u.id,
                            u.firstname,
                            u.lastname,
                            u.email
                     FROM 	  {report_gen_company_manager}	cm
                        JOIN  {user}						u	ON 	u.id 		= cm.managerid
                                                                AND	u.deleted 	= 0
                     WHERE	cm.companyid 		= :company
                        AND	cm.hierarchylevel	= :level ";

            /* Search Option */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= ") AND (";
                    }
                    $locate .= " LOCATE('" . $str . "',u.firstname)
                                 OR
                                 LOCATE('" . $str . "',u.lastname)
                                 OR
                                 LOCATE('" . $str . "',CONCAT(u.firstname,' ',u.lastname))
                                 OR
                                 LOCATE('". $str . "',u.email) ";
                }//if_search_opt

                $sql .= " 	AND ($locate) ";
            }//if_search

            /* Order    */
            $sql .= " ORDER BY u.firstname, u.lastname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $total = count($rdo);
                if ($total > MAX_MANAGERS_SELECTOR_PAGE) {
                    $availableManagers = self::TooMany_UsersSelector($search,$total);
                }else {
                    if ($search) {
                        $groupName = get_string('current_users_matching', 'report_manager', $search);
                    }else {
                        $groupName = get_string('current_users', 'report_manager');
                    }//if_serach

                    /* Get Users    */
                    foreach ($rdo as $instance) {
                        $managers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    /* Add users    */
                    $availableManagers[$groupName] = $managers;
                }//if_max
            }else {
                /* Info to return */
                $groupName = get_string('no_managers','report_manager');
                $availableManagers[$groupName]  = array('');
            }//if_rdo

            return $availableManagers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindManagers_Selector

    /**
     * @param           $search
     * @param           $company
     * @param           $level
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find potential managers.
     */
    public static function FindPotentialManagers_Selector($search,$company,$level) {
        /* Variables */
        global $DB;
        $availableManagers  = array();
        $managers           = array();
        $sql                = null;
        $params             = null;
        $rdo                = null;
        $locate             = '';
        $extra              = null;
        $groupName          = null;
        $total              = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['company']  = $company;
            $params['level']    = $level;

            /* SQL Instruction  */
            $sql = " SELECT		u.id,
                                u.firstname,
                                u.lastname,
                                u.email
                     FROM			{user}						  u
                        LEFT JOIN	{report_gen_company_manager}  cm	ON  cm.managerid 		= u.id
                                                                        AND	cm.companyid 		= :company
                                                                        AND	cm.hierarchylevel 	= :level
                     WHERE		u.deleted   = 0
                        AND		u.username != 'guest'
                        AND		cm.id IS NULL ";

            /* Search Option */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= ") AND (";
                    }
                    $locate .= " LOCATE('" . $str . "',u.firstname)
                                 OR
                                 LOCATE('" . $str . "',u.lastname)
                                 OR
                                 LOCATE('" . $str . "',CONCAT(u.firstname,' ',u.lastname))
                                 OR
                                 LOCATE('". $str . "',u.email) ";
                }//if_search_opt

                $sql .= " 	AND ($locate) ";
            }//if_search

            /* Order    */
            $sql .= " ORDER BY u.firstname, u.lastname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $total = count($rdo);
                if ($total > MAX_MANAGERS_SELECTOR_PAGE) {
                    $availableManagers = self::TooMany_UsersSelector($search,$total);

                }else {
                    if ($search) {
                        $groupName = get_string('pot_users_matching', 'report_manager', $search);
                    }else {
                        $groupName = get_string('pot_users', 'report_manager');
                    }//if_serach

                    /* Get Users    */
                    foreach ($rdo as $instance) {
                        $managers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    /* Add Users */
                    $availableManagers[$groupName] = $managers;
                }//if_tooMany
            }//if_Rdo

            return $availableManagers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindPotentialManagers_Selector

    /**
     * @param           $level
     * @param           $company
     * @param           $managersLst
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add managers to the company
     * Manager is a reporter
     */
    public static function AddManagers($level,$company,$managersLst) {
        /* Variables    */
        global $DB;
        $trans          = null;
        $infoManager    = null;
        $infoReporter   = null;
        $time           = null;

        /* Start Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time   */
            $time = time();

            foreach ($managersLst as $manager) {
                /* New Manager  */
                $infoManager = new stdClass();
                $infoManager->managerid         = $manager;
                $infoManager->companyid         = $company;
                $infoManager->hierarchylevel    = $level;
                $infoManager->timecreated       = $time;

                /* Execute  */
                $DB->insert_record('report_gen_company_manager',$infoManager);

                /* New Reporter */
                $infoReporter = new stdClass();
                $infoReporter->reporterid        = $manager;
                $infoReporter->companyid         = $company;
                $infoReporter->hierarchylevel    = $level;
                $infoReporter->timecreated       = $time;

                /* Execute  */
                $DB->insert_record('report_gen_company_reporter',$infoReporter);
            }//for_managers

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//AddManagers

    /**
     * @param           $level
     * @param           $company
     * @param           $managersLst
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Remove managers from company
     * A manager is also a reporter
     */
    public static function RemoveManagers($level,$company,$managersLst) {
        /* Variables */
        global $DB;
        $trans  = null;
        $sql    = null;
        $params = null;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Search Criteria  */
            $params = array();
            $params['level']    = $level;
            $params['company']  = $company;

            /* SQL Instruction - Manager */
            $sql = " DELETE FROM {report_gen_company_manager}
                     WHERE  hierarchylevel  = :level
                        AND companyid       = :company
                        AND managerid IN ($managersLst) ";

            /* Execute  */
            $DB->execute($sql,$params);

            /* SQL Instruction - Reporters */
            $sql = " DELETE FROM {report_gen_company_reporter}
                     WHERE  hierarchylevel  = :level
                        AND companyid       = :company
                        AND reporterid IN ($managersLst) ";

            /* Execute  */
            $DB->execute($sql,$params);

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//RemoveManagers

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $search
     * @param           $jsModule
     * @param           $level
     * @param           $company
     *
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise selector to add managers to the company
     */
    private static function Init_Managers_AddSelector($search,$jsModule,$level,$company) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindPotentialManagers_Selector';
            $options['name']        = 'addselect';
            $options['multiselect'] = true;

            /* Connect Selector User    */
            $hash                           = md5(serialize($options));
            $USER->manager_selectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_managers_selector',
                                          array('addselect',$hash, $level,$company, $search),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Managers_AddSelector

    /**
     * @param           $search
     * @param           $jsModule
     * @param           $level
     * @param           $company
     *
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise selector to remove managers from the company
     */
    private static function Init_Managers_RemoveSelector($search,$jsModule,$level,$company) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindManagers_Selector';
            $options['name']        = 'removeselect';
            $options['multiselect'] = true;

            /* Connect Selector User    */
            $hash                           = md5(serialize($options));
            $USER->manager_selectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_managers_selector',
                                          array('removeselect',$hash, $level,$company, $search),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Managers_RemoveSelector

    /**
     * @param           $search
     * @param           $total
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the options to show when there are too many users
     */
    private static function TooMany_UsersSelector($search,$total) {
        /* Variables    */
        $availableUsers = array();
        $info           = null;
        $tooMany        = null;
        $searchMore     = null;

        try {
            if ($search) {
                /* Info too many    */
                $info = new stdClass();
                $info->count    = $total;
                $info->search   = $search;

                /* Get Info to show  */
                $tooMany    = get_string('toomanyusersmatchsearch', '', $info);
                $searchMore = get_string('pleasesearchmore');

            }else {
                /* Get Info to show */
                $tooMany    = get_string('toomanyuserstoshow', '', $total);
                $searchMore = get_string('pleaseusesearch');
            }//if_search

            /* Info to return   */
            $availableUsers[$tooMany]       = array('');
            $availableUsers[$searchMore]    = array('');

            return $availableUsers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//TooMany_UsersSelector
}//Managers