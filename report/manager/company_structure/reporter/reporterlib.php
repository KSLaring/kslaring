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
 * Report Competence Manager - Reporters Library
 *
 * Description
 *
 * @package         report/reporter
 * @subpackage      company_structure/reporter
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
     * Description
     * Initialise selectors to add and remove reporters to/from the company
     *
     * @param           $addSearch
     * @param           $removeSearch
     * @param           $level
     * @param           $parents
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function Init_Reporters_Selectors($addSearch,$removeSearch,$level,$parents) {
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
            // Initialise variables
            $name       = 'reporter_selector';
            $path       = '/report/manager/company_structure/reporter/js/search.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpOne     = array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%');
            $grpTwo     = array('nomatchingusers', 'moodle', '%%SEARCHTERM%%');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpOne,$grpTwo,$grpThree);

            // Initialise js module
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
                             );

            // Reporters - add selector
            self::Init_Reporters_AddSelector($addSearch,$jsModule,$level,$parents);
            // Repoters - remove selector
            self::Init_Reporters_RemoveSelector($removeSearch,$jsModule,$level,$parents);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Reporters_Selectors

    /**
     * Description
     * Find existing reporters connected with the company
     *
     * @param           $search
     * @param           $parents
     * @param           $level
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function FindReporters_Selector($search,$parents,$level) {
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
        $tardis                 = null;

        try {
            // Search criteria
            $params = array();
            $params['level']    = $level;

            // SQL Instruction
            $sql = " SELECT	DISTINCT
                              u.id,
                            u.firstname,
                            u.lastname,
                              u.email,
                              IF(cr.mapped='TARDIS',1,0) as 'tardis'
                     FROM 		{report_gen_company_reporter}	cr
                        JOIN	{user}						    u	ON 	u.id 		= cr.reporterid
                                                                    AND	u.deleted 	= 0
                     WHERE	cr.hierarchylevel	= :level ";

            // Get companies levels
            switch ($level) {
                case 0:
                    $params['levelzero'] = $parents[$level];

                    $sql .= " AND cr.levelzero = :levelzero
                              AND cr.levelone   IS NULL
                              AND cr.leveltwo   IS NULL
                              AND cr.levelthree IS NULL ";

                    break;
                case 1:
                    $params['levelzero']    = $parents[$level-1];
                    $params['levelone']     = $parents[$level];

                    $sql .= " AND cr.levelzero = :levelzero
                              AND cr.levelone  = :levelone
                              AND cr.leveltwo   IS NULL
                              AND cr.levelthree IS NULL ";

                    break;
                case 2:
                    $params['levelzero']    = $parents[$level-2];
                    $params['levelone']     = $parents[$level-1];
                    $params['leveltwo']     = $parents[$level];

                    $sql .= " AND cr.levelzero = :levelzero
                              AND cr.levelone  = :levelone
                              AND cr.leveltwo  = :leveltwo
                              AND cr.levelthree IS NULL ";

                    break;
                case 3:
                    $params['levelzero']    = $parents[$level-3];
                    $params['levelone']     = $parents[$level-2];
                    $params['leveltwo']     = $parents[$level-1];
                    $params['levelthree']   = $parents[$level];

                    $sql .= " AND cr.levelzero  = :levelzero
                              AND cr.levelone   = :levelone
                              AND cr.leveltwo   = :leveltwo
                              AND cr.levelthree = :levelthree ";
                    break;
            }//switch_level

            // Search option
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

            // Order
            $sql .= " ORDER BY u.firstname, u.lastname ";

            // Execute
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

                    // Get users
                    $tardis = array();
                    foreach ($rdo as $instance) {
                        if ($instance->tardis) {
                            $tardis[$instance->id] = $instance->id;
                        }
                        $reporters[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    // Add users
                    $availableReporters[$groupName] = $reporters;
                }//if_max
            }else {
                // Info to return
                $groupName = get_string('no_reporters','report_manager');
                $availableReporters[$groupName]  = array('');
            }//if_rdo

            return array($availableReporters,$tardis);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindReporters_Selector


    /**
     * Description
     * Find potential reporters
     *
     * @param           $search
     * @param           $parents
     * @param           $level
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function FindPotentialReporters_Selector($search,$parents,$level) {
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
        $tardis                 = null;

        try {
            // Search criteria
            $params = array();
            $params['level']    = $level;

            // SQL Instruction
            $sql = " SELECT	DISTINCT	
                                  u.id,
                                u.firstname,
                                u.lastname,
                                u.email
                     FROM			{user}						  u
                        LEFT JOIN	{report_gen_company_reporter} cr	ON 	cr.reporterid 		= u.id
                                                                        AND	cr.hierarchylevel 	= :level ";

            // Get companies level
            switch ($level) {
                case 0:
                    $params['levelzero'] = $parents[$level];

                    $sql .= " AND cr.levelzero = :levelzero
                              AND cr.levelone   IS NULL
                              AND cr.leveltwo   IS NULL
                              AND cr.levelthree IS NULL ";

                    break;
                case 1:
                    $params['levelzero']    = $parents[$level-1];
                    $params['levelone']     = $parents[$level];

                    $sql .= " AND cr.levelzero = :levelzero
                              AND cr.levelone  = :levelone
                              AND cr.leveltwo   IS NULL
                              AND cr.levelthree IS NULL ";

                    break;
                case 2:
                    $params['levelzero']    = $parents[$level-2];
                    $params['levelone']     = $parents[$level-1];
                    $params['leveltwo']     = $parents[$level];

                    $sql .= " AND cr.levelzero = :levelzero
                              AND cr.levelone  = :levelone
                              AND cr.leveltwo  = :leveltwo
                              AND cr.levelthree IS NULL ";

                    break;
                case 3:
                    $params['levelzero']    = $parents[$level-3];
                    $params['levelone']     = $parents[$level-2];
                    $params['leveltwo']     = $parents[$level-1];
                    $params['levelthree']   = $parents[$level];

                    $sql .= " AND cr.levelzero  = :levelzero
                              AND cr.levelone   = :levelone
                              AND cr.leveltwo   = :leveltwo
                              AND cr.levelthree = :levelthree ";
                    break;
            }//switch_level

            // Criteria
            $sql .= " WHERE		u.deleted   = 0
                        AND		u.username != 'guest'
                        AND		cr.id IS NULL ";

            // Search option
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

            // Order
            $sql .= " ORDER BY u.firstname, u.lastname ";

            // Execute
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

                    // Get users
                    foreach ($rdo as $instance) {
                        $reporters[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    // Add users
                    $availableReporters[$groupName] = $reporters;
                }//if_tooMany
            }//if_Rdo

            return array($availableReporters,$tardis);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindPotentialReporters_Selector

    /**
     * Description
     * Add reporters to the company
     *
     * @param           $level
     * @param           $parents
     * @param           $reportersLst
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function AddReporters($level,$parents,$reportersLst) {
        /* Variables */
        global $DB;
        $trans          = null;
        $infoReporter   = null;
        $time           = null;
        $levelZero      = null;
        $levelOne       = null;
        $levelTwo       = null;
        $levelThree     = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();

            // Get companies level
            switch ($level) {
                case 0:
                    if ($parents[$level] != 0) {
                        $levelZero = $parents[$level];
                    }

                    break;
                case 1:
                    $levelZero  = $parents[$level-1];
                    $levelOne   = $parents[$level];

                    break;
                case 2:
                    $levelZero  = $parents[$level-2];
                    $levelOne   = $parents[$level-1];
                    $levelTwo   = $parents[$level];

                    break;
                case 3:
                    $levelZero  = $parents[$level-3];
                    $levelOne   = $parents[$level-2];
                    $levelTwo   = $parents[$level-1];
                    $levelThree = $parents[$level];

                    break;
            }//switch

            foreach ($reportersLst as $reporter) {
                if ($levelZero) {
                    // New reporter
                    $infoReporter = new stdClass();
                    $infoReporter->reporterid        = $reporter;
                    $infoReporter->levelzero         = $levelZero;
                    $infoReporter->levelone          = $levelOne;
                    $infoReporter->leveltwo          = $levelTwo;
                    $infoReporter->levelthree        = $levelThree;
                    $infoReporter->hierarchylevel    = $level;
                    $infoReporter->timecreated       = $time;
                    $infoReporter->mapped            = 'MANUAL';

                    // Execute
                    $DB->insert_record('report_gen_company_reporter',$infoReporter);
                }
            }//for_reporters

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//AddReporters

    /**
     * Description
     * Remove reporters from company
     *
     * @param           $level
     * @param           $parents
     * @param           $reportersLst
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function RemoveReporters($level,$parents,$reportersLst) {
        /* Variables */
        global $DB;
        $trans      = null;
        $sql        = null;
        $params     = null;
        $sqlLevels  = null;
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Search criteria
            $params = array();
            $params['level']    = $level;
            $params['mapped']   = 'TARDIS';

            // Get companies level
            switch ($level) {
                case 0:
                    $params['levelzero'] = $parents[$level];

                    $sqlLevels = "  AND levelzero = :levelzero
                                    AND levelone    IS NULL
                                    AND leveltwo    IS NULL
                                    AND levelthree  IS NULL ";

                    break;
                case 1:
                    $params['levelzero']  = $parents[$level-1];
                    $params['levelone']   = $parents[$level];

                    $sqlLevels = "  AND levelzero = :levelzero
                                    AND levelone  = :levelone
                                    AND leveltwo    IS NULL
                                    AND levelthree  IS NULL ";

                    break;
                case 2:
                    $params['levelzero']  = $parents[$level-2];
                    $params['levelone']   = $parents[$level-1];
                    $params['leveltwo']   = $parents[$level];

                    $sqlLevels = "  AND levelzero = :levelzero
                                    AND levelone  = :levelone
                                    AND leveltwo  = :leveltwo
                                    AND levelthree  IS NULL ";

                    break;
                case 3:
                    $params['levelzero']    = $parents[$level-3];
                    $params['levelone']     = $parents[$level-2];
                    $params['leveltwo']     = $parents[$level-1];
                    $params['levelthree']   = $parents[$level];

                    $sqlLevels = "  AND levelzero   = :levelzero
                                    AND levelone    = :levelone
                                    AND leveltwo    = :leveltwo
                                    AND levelthree  = :levelthree ";

                    break;
            }//switch

            // SQL Instruction
            $sql = " DELETE FROM {report_gen_company_reporter}
                     WHERE  hierarchylevel  = :level
                        AND mapped          != :mapped
                        AND reporterid IN ($reportersLst) ";

            // Execute
            $DB->execute($sql . $sqlLevels,$params);

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//RemoveReporters

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Get the options to show when there are too many users
     *
     * @param           $search
     * @param           $total
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function TooMany_UsersSelector($search,$total) {
        /* Variables    */
        $availableUsers = array();
        $info           = null;
        $tooMany        = null;
        $searchMore     = null;

        try {
            if ($search) {
                // Info too many
                $info = new stdClass();
                $info->count    = $total;
                $info->search   = $search;

                // Get info to show
                $tooMany    = get_string('toomanyusersmatchsearch', '', $info);
                $searchMore = get_string('pleasesearchmore');

            }else {
                // Get info to show
                $tooMany    = get_string('toomanyuserstoshow', '', $total);
                $searchMore = get_string('pleaseusesearch');
            }//if_search

            // Info to return
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
     * @param           $parents
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise selector to add reporters to the company
     */
    private static function Init_Reporters_AddSelector($search,$jsModule,$level,$parents) {
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
                                          array('addselect',$hash, $level,$parents, $search),
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
     * @param           $parents
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise selector to remove reporters from the company
     */
    private static function Init_Reporters_RemoveSelector($search,$jsModule,$level,$parents) {
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
                                          array('removeselect',$hash, $level,$parents, $search),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Reporters_RemoveSelector
}//reporters