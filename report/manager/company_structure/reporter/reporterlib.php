<?php
/**
 * Report Competence Manager - Reporters Library
 *
 * Description
 *
 * @package         report/reporter
 * @subpackage      company_structure/reporter
 * @copyright       2010 eFaktor
 *
 * @creationDate    22/12/2015
 * @author          eFaktor     (fbv)
 *
 */
define('MAX_REPORTERS_SELECTOR_PAGE',100);

Class Reporters {
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
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise selectors to add and remove reporters to/from the company
     */
    public static function Init_Reporters_Selectors($addSearch,$removeSearch,$level,$company) {
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
            $name       = 'reporter_selector';
            $path       = '/report/manager/company_structure/reporter/js/search.js';
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
            self::Init_Reporters_AddSelector($addSearch,$jsModule,$level,$company);
            /* Super Users - Remove Selector    */
            self::Init_Reporters_RemoveSelector($removeSearch,$jsModule,$level,$company);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Reporters_Selectors

    /**
     * @param           $search
     * @param           $company
     * @param           $level
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find existing reporters connected with the company
     */
    public static function FindReporters_Selector($search,$company,$level) {
        /* Variables */
        global $DB;
        $availableReporters     = array();
        $reporters              = array();
        $sql                    = null;
        $params                 = null;
        $rdo                    = null;
        $locate                 = '';
        $extra                  = null;
        $groupName              = null;
        $total                  = null;

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
                     FROM 		{report_gen_company_reporter}	cr
                        JOIN	{user}						    u	ON 	u.id 		= cr.reporterid
                                                                    AND	u.deleted 	= 0
                     WHERE	cr.companyid 		= :company
                        AND	cr.hierarchylevel	= :level ";

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

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $total = count($rdo);
                if ($total > MAX_REPORTERS_SELECTOR_PAGE) {
                    $availableReporters = self::TooMany_UsersSelector($search,$total);
                }else {
                    if ($search) {
                        $groupName = get_string('current_users_matching', 'report_manager', $search);
                    }else {
                        $groupName = get_string('current_users', 'report_manager');
                    }//if_serach

                    /* Get Users    */
                    foreach ($rdo as $instance) {
                        $reporters[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    /* Add users    */
                    $availableReporters[$groupName] = $reporters;
                }//if_max
            }else {
                /* Info to return */
                $groupName = get_string('no_reporters','report_manager');
                $availableReporters[$groupName]  = array('');
            }//if_rdo

            return $availableReporters;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindReporters_Selector


    /**
     * @param           $search
     * @param           $company
     * @param           $level
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find potential reporters
     */
    public static function FindPotentialReporters_Selector($search,$company,$level) {
        /* Variables */
        global $DB;
        $availableReporters     = array();
        $reporters              = array();
        $sql                    = null;
        $params                 = null;
        $rdo                    = null;
        $locate                 = '';
        $extra                  = null;
        $groupName              = null;
        $total                  = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['company']  = $company;
            $params['level']    = $level;

            /* SQL Instruction */
            $sql = " SELECT		u.id,
                                u.firstname,
                                u.lastname,
                                u.email
                     FROM			{user}						  u
                        LEFT JOIN	{report_gen_company_reporter} cr	ON 	cr.reporterid 		= u.id
                                                                        AND	cr.companyid 		= :company
                                                                        AND	cr.hierarchylevel 	= :level
                     WHERE		u.deleted   = 0
                        AND		u.username != 'guest'
                        AND		cr.id IS NULL ";

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
                if ($total > MAX_REPORTERS_SELECTOR_PAGE) {
                    $availableReporters = self::TooMany_UsersSelector($search,$total);
                }else {
                    if ($search) {
                        $groupName = get_string('pot_users_matching', 'report_manager', $search);
                    }else {
                        $groupName = get_string('pot_users', 'report_manager');
                    }//if_serach

                    /* Get Users    */
                    foreach ($rdo as $instance) {
                        $reporters[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    /* Add Users */
                    $availableReporters[$groupName] = $reporters;
                }//if_tooMany
            }//if_Rdo

            return $availableReporters;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindPotentialReporters_Selector

    /**
     * @param           $level
     * @param           $company
     * @param           $reportersLst
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add reporters to the company
     */
    public static function AddReporters($level,$company,$reportersLst) {
        /* Variables */
        global $DB;
        $trans          = null;
        $infoReporter   = null;
        $time           = null;

        /* Start Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time */
            $time = time();

            foreach ($reportersLst as $reporter) {
                /* New Reporter */
                $infoReporter = new stdClass();
                $infoReporter->reporterid        = $reporter;
                $infoReporter->companyid         = $company;
                $infoReporter->hierarchylevel    = $level;
                $infoReporter->timecreated       = $time;

                /* Execute  */
                $DB->insert_record('report_gen_company_reporter',$infoReporter);
            }//for_reporters

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//AddReporters

    /**
     * @param           $level
     * @param           $company
     * @param           $reportersLst
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Remove reporters from company
     */
    public static function RemoveReporters($level,$company,$reportersLst) {
        /* Variables */
        global $DB;
        $trans  = null;
        $sql    = null;
        $params = null;

        /* Start Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Search Criteria  */
            $params = array();
            $params['level']    = $level;
            $params['company']  = $company;

            /* SQL Instruction */
            $sql = " DELETE FROM {report_gen_company_reporter}
                     WHERE  hierarchylevel  = :level
                        AND companyid       = :company
                        AND reporterid IN ($reportersLst) ";

            /* Execute  */
            $DB->execute($sql,$params);

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//RemoveReporters

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $search
     * @param           $total
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    22/12/2015
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

    /**
     * @param           $search
     * @param           $jsModule
     * @param           $level
     * @param           $company
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise selector to add reporters to the company
     */
    private static function Init_Reporters_AddSelector($search,$jsModule,$level,$company) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindPotentialReporters_Selector';
            $options['name']        = 'addselect';
            $options['multiselect'] = true;

            /* Connect Selector User    */
            $hash                            = md5(serialize($options));
            $USER->reporter_selectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_reporters_selector',
                                          array('addselect',$hash, $level,$company, $search),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Reporters_AddSelector

    /**
     * @param           $search
     * @param           $jsModule
     * @param           $level
     * @param           $company
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise selector to remove reporters from the company
     */
    private static function Init_Reporters_RemoveSelector($search,$jsModule,$level,$company) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindReporters_Selector';
            $options['name']        = 'removeselect';
            $options['multiselect'] = true;

            /* Connect Selector User    */
            $hash                            = md5(serialize($options));
            $USER->reporter_selectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_reporters_selector',
                                          array('removeselect',$hash, $level,$company, $search),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Reporters_RemoveSelector
}//reporters