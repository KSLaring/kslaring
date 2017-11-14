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
 * Report Competence Manager - Managers Library
 *
 * Description
 *
 * @package         report/manager
 * @subpackage      company_structure/manager
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
     * Description
     * Initialise selectors to add and remove managers to/from the company
     *
     * @param           $addSearch
     * @param           $removeSearch
     * @param           $level
     * @param           $parents
     *
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function Init_Managers_Selectors($addSearch,$removeSearch,$level,$parents) {
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
            $name       = 'manager_selector';
            $path       = '/report/manager/company_structure/manager/js/search.js';
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

            // Managers - Add Selector
            self::Init_Managers_AddSelector($addSearch,$jsModule,$level,$parents);
            // Managers - Remove selector
            self::Init_Managers_RemoveSelector($removeSearch,$jsModule,$level,$parents);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Managers_Selectors

    /**
     * Description
     * Find the managers connected with the company
     *
     * @param           $search
     * @param           $parents
     * @param           $level
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function FindManagers_Selector($search,$parents,$level) {
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
        $tardis             = null;

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
                              IF(cm.mapped='TARDIS',1,0) as 'tardis'
                     FROM 	  {report_gen_company_manager}	cm
                        JOIN  {user}						u	ON 	u.id 		= cm.managerid
                                                                AND	u.deleted 	= 0
                     WHERE	  cm.hierarchylevel	= :level ";

            // Get companies levels
            switch ($level) {
                case 0:
                    $params['levelzero'] = $parents[$level];

                    $sql .= " AND cm.levelzero = :levelzero
                              AND cm.levelone   IS NULL
                              AND cm.leveltwo   IS NULL
                              AND cm.levelthree IS NULL ";

                    break;
                case 1:
                    $params['levelzero']    = $parents[$level-1];
                    $params['levelone']     = $parents[$level];

                    $sql .= " AND cm.levelzero = :levelzero
                              AND cm.levelone  = :levelone
                              AND cm.leveltwo   IS NULL
                              AND cm.levelthree IS NULL ";

                    break;
                case 2:
                    $params['levelzero']    = $parents[$level-2];
                    $params['levelone']     = $parents[$level-1];
                    $params['leveltwo']     = $parents[$level];

                    $sql .= " AND cm.levelzero = :levelzero
                              AND cm.levelone  = :levelone
                              AND cm.leveltwo  = :leveltwo
                              AND cm.levelthree IS NULL ";

                    break;
                case 3:
                    $params['levelzero']    = $parents[$level-3];
                    $params['levelone']     = $parents[$level-2];
                    $params['leveltwo']     = $parents[$level-1];
                    $params['levelthree']   = $parents[$level];

                    $sql .= " AND cm.levelzero  = :levelzero
                              AND cm.levelone   = :levelone
                              AND cm.leveltwo   = :leveltwo
                              AND cm.levelthree = :levelthree ";
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
                if ($total > MAX_MANAGERS_SELECTOR_PAGE) {
                    $availableManagers = self::TooMany_UsersSelector($search,$total);
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
                        $managers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    // Add users
                    $availableManagers[$groupName] = $managers;
                }//if_max
            }else {
                // Info to return
                $groupName = get_string('no_managers','report_manager');
                $availableManagers[$groupName]  = array('');
            }//if_rdo

            return array($availableManagers,$tardis);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindManagers_Selector

    /**
     * Description
     * Find potential managers.
     *
     * @param           $search
     * @param           $parents
     * @param           $level
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function FindPotentialManagers_Selector($search,$parents,$level) {
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
        $tardis             = null;

        try {
            // Search criteria
            $params = array();
            $params['level']    = $level;

            // SQL instruction
            $sql = " SELECT	  DISTINCT
                                  u.id,
                                  u.firstname,
                                  u.lastname,
                                  u.email,
                                  IF(cm.mapped = 'TARDIS',1,0) as 'mapped'
                     FROM		  {user}						u
                        LEFT JOIN {report_gen_company_manager}  cm	ON  cm.managerid 		= u.id
                                                                    AND	cm.hierarchylevel 	= :level ";

            // Get companies level
            switch ($level) {
                case 0:
                    $params['levelzero'] = $parents[$level];

                    $sql .= " AND cm.levelzero = :levelzero
                              AND cm.levelone   IS NULL
                              AND cm.leveltwo   IS NULL
                              AND cm.levelthree IS NULL ";

                    break;
                case 1:
                    $params['levelzero']    = $parents[$level-1];
                    $params['levelone']     = $parents[$level];

                    $sql .= " AND cm.levelzero = :levelzero
                              AND cm.levelone  = :levelone
                              AND cm.leveltwo   IS NULL
                              AND cm.levelthree IS NULL ";

                    break;
                case 2:
                    $params['levelzero']    = $parents[$level-2];
                    $params['levelone']     = $parents[$level-1];
                    $params['leveltwo']     = $parents[$level];

                    $sql .= " AND cm.levelzero = :levelzero
                              AND cm.levelone  = :levelone
                              AND cm.leveltwo  = :leveltwo
                              AND cm.levelthree IS NULL ";

                    break;
                case 3:
                    $params['levelzero']    = $parents[$level-3];
                    $params['levelone']     = $parents[$level-2];
                    $params['leveltwo']     = $parents[$level-1];
                    $params['levelthree']   = $parents[$level];

                    $sql .= " AND cm.levelzero  = :levelzero
                              AND cm.levelone   = :levelone
                              AND cm.leveltwo   = :leveltwo
                              AND cm.levelthree = :levelthree ";
                    break;
            }//switch_level

            // Criteria
            $sql .= " WHERE		u.deleted   = 0
                        AND		u.username != 'guest'
                        AND		cm.id IS NULL ";

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
                if ($total > MAX_MANAGERS_SELECTOR_PAGE) {
                    $availableManagers = self::TooMany_UsersSelector($search,$total);
                }else {
                    if ($search) {
                        $groupName = get_string('pot_users_matching', 'report_manager', $search);
                    }else {
                        $groupName = get_string('pot_users', 'report_manager');
                    }//if_serach

                    // Get users
                    foreach ($rdo as $instance) {
                        $managers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    // Add users
                    $availableManagers[$groupName] = $managers;
                }//if_tooMany
            }//if_Rdo

            return array($availableManagers,$tardis);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindPotentialManagers_Selector

    /**
     * Description
     * Add managers to the company
     * Manager is a reporter
     *
     * @param           $level
     * @param           $parents
     * @param           $managersLst
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function AddManagers($level,$parents,$managersLst) {
        /* Variables    */
        global $DB;
        $trans          = null;
        $infoManager    = null;
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

            foreach ($managersLst as $manager) {
                if ($levelZero) {
                    // New manager
                    $infoManager = new stdClass();
                    $infoManager->managerid         = $manager;
                    $infoManager->levelzero         = $levelZero;
                    $infoManager->levelone          = $levelOne;
                    $infoManager->leveltwo          = $levelTwo;
                    $infoManager->levelthree        = $levelThree;
                    $infoManager->hierarchylevel    = $level;
                    $infoManager->timecreated       = $time;
                    $infoManager->mapped            = 'MANUAL';

                    // Execute
                    $DB->insert_record('report_gen_company_manager',$infoManager);
                }
            }//for_managers

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//AddManagers

    /**
     * Description
     * Remove managers from company
     * A manager is also a reporter
     *
     * @param           $level
     * @param           $parents
     * @param           $managersLst
     *
     * @throws          Exception
     *
     * @creationDate    22/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function RemoveManagers($level,$parents,$managersLst) {
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
            $params['mapped']   = 'MANUAL';

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

            // Sql instruction
            $sql = " DELETE FROM {report_gen_company_manager}
                     WHERE  hierarchylevel  = :level
                        AND mapped          = : mapped
                        AND managerid IN ($managersLst) ";

            // Execute
            $DB->execute($sql . $sqlLevels,$params);

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//RemoveManagers

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Initialise selector to add managers to the company
     *
     * @param           $search
     * @param           $jsModule
     * @param           $level
     * @param           $parents
     *
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function Init_Managers_AddSelector($search,$jsModule,$level,$parents) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            // Initialise Options Selector
            $options = array();
            $options['class']       = 'FindPotentialManagers_Selector';
            $options['name']        = 'addselect';
            $options['multiselect'] = true;

            // Connecte selector user
            $hash                           = md5(serialize($options));
            $USER->manager_selectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_managers_selector',
                                          array('addselect',$hash, $level,$parents, $search),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Managers_AddSelector

    /**
     * Description
     * Initialise selector to remove managers from the company
     *
     * @param           $search
     * @param           $jsModule
     * @param           $level
     * @param           $parents
     *
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function Init_Managers_RemoveSelector($search,$jsModule,$level,$parents) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            // Initialise options selector
            $options = array();
            $options['class']       = 'FindManagers_Selector';
            $options['name']        = 'removeselect';
            $options['multiselect'] = true;

            // Connect selector user
            $hash                           = md5(serialize($options));
            $USER->manager_selectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_managers_selector',
                                          array('removeselect',$hash, $level,$parents, $search),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Managers_RemoveSelector

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
     * @creationDate    21/12/2015
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
}//Managers