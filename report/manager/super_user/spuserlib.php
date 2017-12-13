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
 * Report Competence Manager - Super Users Library.
 *
 * @package         report
 * @subpackage      manager/super_user
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    14/10/2015
 * @author          eFaktor     (fbv)
 */

define('SP_USER_COMPANY_STRUCTURE_LEVEL','level_');
define('MAX_USERS_SELECTOR_PAGE',100);

class SuperUser {
    /***********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $addSearch
     * @param           $removeSearch
     * @param           $removeSelected
     *
     * @throws          Exception
     *
     * @creationDate    22/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise the user selectors
     */
    public static function Init_SuperUsers_Selectors($addSearch,$removeSearch,$removeSelected) {
        /* Variables    */
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
            $name       = 'super_user_selector';
            $path       = '/report/manager/super_user/js/search.js';
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
            self::Init_SuperUsers_AddSelector($addSearch,$jsModule);
            /* Super Users - Remove Selector    */
            self::Init_SuperUsers_RemoveSelector($removeSearch,$jsModule,$removeSelected);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_SuperUsers_Selectors

    /**
     *
     * @throws          Exception
     *
     * @creationDate    22/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise the list boxes for the organization structure
     */
    public static function Init_Organization_Structure() {
        /* Variables    */
        global $USER,$PAGE;
        $options    = null;
        $hash       = null;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;

        try {
            /* Initialise variables */
            $name       = 'organization';
            $path       = '/report/manager/super_user/js/structure.js';
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


            $PAGE->requires->js_init_call('M.core_user.init_structure',
                                          array(SP_USER_COMPANY_STRUCTURE_LEVEL),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Organization_Structure

    /**
     * @param           $search
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    16/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find the super users that meet the criteria
     */
    public static function FindSuperUsers_Selector($search,$levelZero,$levelOne=0,$levelTwo=0,$levelThree=0) {
        /* Variables    */
        global $DB;
        $total          = null;
        $params         = null;
        $sql            = null;
        $sqlWhere       = null;
        $rdo            = null;
        $availableUsers = array();
        $groupName      = null;
        $users          = array();
        $locate         = '';
        $extra          = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['zero']    = $levelZero;
            $params['one']     = $levelOne;
            $params['two']     = $levelTwo;


            /* SQL Instruction  */
            $sql = " SELECT	  DISTINCT	sp.userid,
                                        u.firstname,
                                        u.lastname,
                                        u.email
                     FROM	  {report_gen_super_user}	sp
                        JOIN  {user}					u	ON 		u.id 		= sp.userid
                                                            AND		u.deleted 	= 0 ";

            /* Get Level Condition  */
            if ($levelZero && $levelOne && $levelTwo && $levelThree) {
                $sqlWhere = ' WHERE (sp.levelzero = :zero    AND sp.levelone = :one   AND sp.leveltwo = :two     AND sp.levelthree IN (' . $levelThree . ')) ';
            }else if ($levelZero && $levelOne && $levelTwo && !$levelThree) {
                $sqlWhere = ' WHERE (sp.levelzero = :zero    AND sp.levelone = :one   AND sp.leveltwo = :two     AND sp.levelthree IS NULL) ';
            }else if ($levelZero && $levelOne && !$levelTwo && !$levelThree) {
                $sqlWhere = ' WHERE (sp.levelzero = :zero    AND sp.levelone = :one   AND sp.leveltwo IS NULL    AND sp.levelthree IS NULL) ';
            }else {
                $sqlWhere = ' WHERE (sp.levelzero = :zero    AND sp.levelone IS NULL  AND sp.leveltwo IS NULL    AND sp.levelthree IS NULL) ';
            }

            /* Search   */
            if ($search) {
                if ($sqlWhere) {
                    $sqlWhere .= ' AND ';
                }else {
                    $sqlWhere = ' WHERE ';
                }

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

                $sql .= $sqlWhere . " ($locate) ";
            }//if_search

            /* ORDER */
            $sql .= $sqlWhere . " ORDER BY u.firstname, u.lastname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $total = count($rdo);
                if ($total > MAX_USERS_SELECTOR_PAGE) {
                    $availableUsers = self::TooMany_UsersSelector($search,$total);
                }else {
                    if ($search) {
                        $groupName = get_string('current_users_matching', 'report_manager', $search);
                    }else {
                        $groupName = get_string('current_users', 'report_manager');
                    }//if_serach

                    /* Get Users    */
                    foreach ($rdo as $instance) {
                        $users[$instance->userid] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    /* Add users    */
                    $availableUsers[$groupName] = $users;
                }//if_tooMany
            }else {
                /* Info to return */
                $groupName = get_string('no_users','report_manager');
                $availableUsers[$groupName]  = array('');
            }//if_rdo

            return $availableUsers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindSuperUsers_Selector


    /**
     * @param           $search
     * @param           $levelZero
     * @param       int $levelOne
     * @param       int $levelTwo
     * @param       int $levelThree
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    16/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find users that meet the criteria
     */
    public static function FindPotentialUsers_Selector($search,$levelZero,$levelOne=0,$levelTwo=0,$levelThree=0) {
        /* Variables    */
        global $DB;
        $total          = null;
        $sql            = null;
        $sqlLeft        = null;
        $params         = null;
        $rdo            = null;
        $availableUsers = array();
        $groupName      = null;
        $users          = array();
        $locate         = '';
        $extra          = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['zero']    = $levelZero;
            $params['one']     = $levelOne;
            $params['two']     = $levelTwo;

            /* SQL LEFT JOIN    */
            if ($levelZero && $levelOne && $levelTwo && $levelThree) {
                $sqlLeft = ' AND (sp.levelzero = :zero    AND sp.levelone = :one   AND sp.leveltwo = :two     AND sp.levelthree IN (' . $levelThree . ')) ';
            }else if ($levelZero && $levelOne && $levelTwo && !$levelThree) {
                $sqlLeft = ' AND (sp.levelzero = :zero    AND sp.levelone = :one   AND sp.leveltwo = :two     AND sp.levelthree IS NULL) ';
            }else if ($levelZero && $levelOne && !$levelTwo && !$levelThree) {
                $sqlLeft = ' AND (sp.levelzero = :zero    AND sp.levelone = :one   AND sp.leveltwo IS NULL    AND sp.levelthree IS NULL) ';
            }else {
                $sqlLeft = ' AND (sp.levelzero = :zero    AND sp.levelone IS NULL  AND sp.leveltwo IS NULL    AND sp.levelthree IS NULL) ';
            }

            /* SQL Instruction  */
            $sql = " SELECT	    u.id,
                                u.firstname,
                                u.lastname,
                                u.email
                     FROM	        {user}	                u
                        LEFT JOIN   {report_gen_super_user}	sp	ON sp.userid = u.id " . $sqlLeft .
                   "
                     WHERE	u.deleted = 0
                        AND	sp.id IS NULL
                        AND u.username != 'guest' ";

            /* Search   */
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
                if ($total > MAX_USERS_SELECTOR_PAGE) {
                    $availableUsers = self::TooMany_UsersSelector($search,$total);

                }else {
                    if ($search) {
                        $groupName = get_string('pot_users_matching', 'report_manager', $search);
                    }else {
                        $groupName = get_string('pot_users', 'report_manager');
                    }//if_serach

                    /* Get Users    */
                    foreach ($rdo as $instance) {
                        $users[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    /* Add Users */
                    $availableUsers[$groupName] = $users;
                }//if_tooMany
            }else {
                if ($search) {
                    $groupName = get_string('pot_users_matching', 'report_manager', $search);
                }else {
                    $groupName = get_string('pot_users', 'report_manager');
                }//if_serach

                $availableUsers[$groupName] = array('');
            }//if_rdo

            return $availableUsers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindPotentialUsers_Selector

    /**
     * @param           $data
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the levels connected with super users
     */
    public static function GetLevels_SuperUser($data) {
        /* Variables    */
        $nameLevel  = null;
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;

        try {
            /* Level Zero   */
            $nameLevel = SP_USER_COMPANY_STRUCTURE_LEVEL . 0;
            $levelZero = $data->$nameLevel;

            /* Level One    */
            $nameLevel = SP_USER_COMPANY_STRUCTURE_LEVEL . 1;
            if ($data->$nameLevel) {
                $levelOne  = $data->$nameLevel;
            }//if_levelOne

            /* Level Two    */
            $nameLevel = SP_USER_COMPANY_STRUCTURE_LEVEL . 2;
            if ($data->$nameLevel) {
                $levelTwo  = $data->$nameLevel;
            }//if_levelTwo

            /* Level Three  */
            $nameLevel = SP_USER_COMPANY_STRUCTURE_LEVEL . 3;
            if ($data->$nameLevel) {
                $levelThree = array_flip($data->$nameLevel);
                unset($levelThree[0]);
                $levelThree = array_flip($levelThree);
            }

            return array($levelZero,$levelOne,$levelTwo,$levelThree);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetLevels_SuperUser

    /**
     * @param           $superUsers
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     *
     * @throws          Exception
     *
     * @creationDate    21/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add new super users
     */
    public static function AddSuperUsers($superUsers,$levelZero,$levelOne,$levelTwo,$levelThree) {
        /* Variables    */
        global $DB;
        $infoSuperUser = null;

        try {
            /* Add Super User   */
            foreach ($superUsers as $user) {
                /* Super User   */
                $infoSuperUser = new stdClass();
                $infoSuperUser->userid      = $user;
                $infoSuperUser->levelzero   = $levelZero;
                $infoSuperUser->levelone    = $levelOne;
                $infoSuperUser->leveltwo    = $levelTwo;
                $infoSuperUser->levelthree  = null;

                /* Check Level Three    */
                if ($levelThree) {
                    /* Create the super user for each level three   */
                    foreach ($levelThree as $company) {
                        $infoSuperUser->levelthree = $company;

                        /* Create Super User    */
                        $DB->insert_record('report_gen_super_user',$infoSuperUser);
                    }//for_levelThree
                }else {
                    /* Create Super User    */
                    $DB->insert_record('report_gen_super_user',$infoSuperUser);
                }//if_levelThree
            }//for_superUser
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddSuperUsers


    /**
     * @param           $superUsers
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     *
     * @throws          Exception
     *
     * @creationDate    22/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Remove super users
     */
    public static function RemoveSuperUsers($superUsers,$levelZero,$levelOne,$levelTwo,$levelThree) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql = null;
        $rdo = null;


        try {
            /* Search Criteria  */
            $params = array();

            $params['zero']    = $levelZero;
            $params['one']     = $levelOne;
            $params['two']     = $levelTwo;

            /* Delete Super Users   */
            foreach ($superUsers as $user) {
                $params['user'] = $user;

                /* SQL Instruction  */
                $sql = " DELETE
                         FROM	{report_gen_super_user}
                         WHERE	userid = :user ";

                /* Get Level Condition  */
                if ($levelZero && $levelOne && $levelTwo && $levelThree) {
                    $levelThree = implode(',',$levelThree);
                    $sql .= ' AND (levelzero = :zero    AND     levelone = :one   AND   leveltwo = :two     AND     levelthree IN (' . $levelThree . ')) ';
                }else if ($levelZero && $levelOne && $levelTwo && !$levelThree) {
                    $sql .= ' AND (levelzero = :zero    AND     levelone = :one   AND   leveltwo = :two     AND     levelthree IS NULL) ';
                }else if ($levelZero && $levelOne && !$levelTwo && !$levelThree) {
                    $sql .= ' AND (levelzero = :zero    AND     levelone = :one   AND   leveltwo IS NULL    AND     levelthree IS NULL) ';
                }else {
                    $sql .= ' AND (levelzero = :zero    AND     levelone IS NULL  AND   leveltwo IS NULL    AND     levelthree IS NULL) ';
                }

                /* Execute  */
                $DB->execute($sql,$params);
            }//for_each_super_user
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//RemoveSuperUsers

    /************/
    /* PRIVATE */
    /***********/

    /**
     * @param           $search
     * @param           $total
     * @return          array
     * @throws          Exception
     *
     * @creationDate    16/10/2015
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
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    22/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise the selector to add super users
     */
    private static function Init_SuperUsers_AddSelector($search,$jsModule) {
        /* Variables    */
        global $USER,$PAGE;
        $options    = null;
        $hash       = null;


        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindPotentialUsers_Selector';
            $options['name']        = 'addselect';
            $options['multiselect'] = true;

            /* Connect Selector User    */
            $hash                       = md5(serialize($options));
            $USER->userselectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_super_user_selector',
                array('addselect',SP_USER_COMPANY_STRUCTURE_LEVEL,$hash, null, $search,null),
                false,
                $jsModule
            );

            return $hash;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_SuperUsersSelectors


    /**
     * @param           $search
     * @param           $jsModule
     * @param           $removeSelected
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    22/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise the selector to remove super users
     */
    private static function Init_SuperUsers_RemoveSelector($search,$jsModule,$removeSelected) {
        /* Variables    */
        global $USER,$PAGE;
        $options    = null;
        $hash       = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindSuperUsers_Selector';
            $options['name']        = 'removeselect';
            $options['multiselect'] = true;

            /* Connect Selector User    */
            $hash                       = md5(serialize($options));
            $USER->userselectors[$hash] = $options;

            /* Supers Users selected to delete  */
            if ($removeSelected) {
                $removeSelected = implode(',',$removeSelected);
            }
            $PAGE->requires->js_init_call('M.core_user.init_super_user_selector',
                array('removeselect',SP_USER_COMPANY_STRUCTURE_LEVEL,$hash, null, $search,$removeSelected),
                false,
                $jsModule
            );

            return $hash;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_SuperUsers_RemoveSelector
}//SuperUser