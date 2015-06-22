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

//namespace local_friadmin;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->dirroot . '/user/profile/field/competence/competencelib.php');

//use moodle_url;
//use context_system;
//use stdClass;

/**
 * The Friadmin class with utility methods
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_helper {

    /**
     * Get course categories where the user is admin
     *
     * @param Int $userid The user id
     *
     * @return mixed Array|null Null or array with the category names
     */
    public static function get_categories_admin() {
        global $DB;
        $result = null;

        // Get the course categories where the user is admin
        $courecats = $DB->get_records('course_categories');
        $coursecat_names = array();
        foreach ($courecats as $courecat) {
            $coursecat_obj = coursecat::get($courecat->id);
            if ($coursecat_obj->has_manage_capability()) {
//                $coursecat_names[] = $courecat->name;
                $coursecat_names[$courecat->id] = $courecat->name;
            }
        }

        if (!empty($coursecat_names)) {
            $result = $coursecat_names;
        }

        return $result;
    }

    /**
     * @return          null
     * @throws          Exception
     *
     * @creationDate    17/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all categories where the users ir a super user
     *
     * @updateDate      22/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Admin site can see everything
     */
    public static function getMyCategories() {
        /* Variables    */
        global $DB,$USER;
        $myCategories   = null;
        $categoriesLst  = null;
        $contextCat     = null;

        try {
            /* Get all the categories   */
            $categoriesLst = $DB->get_records('course_categories');

            /* Search Criteria  */
            $params = array();
            $params['archetype']    = 'manager';
            $params['context']      = null;
            $params['user']         = $USER->id;
            $params['level']        = CONTEXT_COURSECAT;

            /* SQL Instruction  */
            $sql = " SELECT		ra.id
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 		r.id			= ra.roleid
                                                            AND		r.archetype		= :archetype
                        JOIN    {context}           ct      ON      ct.id			= ra.contextid
                                                            AND     ct.contextlevel = :level
                     WHERE		ra.userid 		= :user
                        AND		ra.contextid 	= :context ";

            /* For each Category checks if the user has permissions */
            foreach ($categoriesLst as $category) {
                /* Get Context Category */
                $contextCat = CONTEXT_COURSECAT::instance($category->id);
                /**
                 * @updateDate      22/06/2015
                 * @author          eFaktor     (fbv)
                 *
                 * Description
                 * Admin site can see everything
                 */
                if (!has_capability('moodle/category:manage', $contextCat)) {
                    /* Execute   */
                    $params['context'] = $contextCat->id;
                    $rdo = $DB->get_record_sql($sql,$params);
                    if ($rdo) {
                        /* Add Category */
                        $myCategories[$category->id] = $category->name;
                        /* If there are subcategories connected with, the user will also have permissions for them  */
                        self::getSubcategories($category->id,$myCategories);
                    }//if_Rdo
                }else {
                    /* Add Category */
                    $myCategories[$category->id] = $category->name;
                    /* If there are subcategories connected with, the user will also have permissions for them  */
                    self::getSubcategories($category->id,$myCategories);
                }
            }//for_Each_Category

            return $myCategories;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getMyCategories

    /**
     * Get the levelone locations with id, name and industrycode
     *
     * @param Int $userid The user id
     *
     * @return mixed Array|null The levelone locations
     */
    public static function get_levelone_municipalities_old($userid) {
        global $DB;
        $ids = array();
        $leveloneobjs = null;

        // Get the user competences
        $competences = Competence::Get_CompetenceData($userid);

        // Get the levleone ids
        foreach ($competences as $comp) {
            if (!in_array($comp->levelOne, $ids)) {
                $ids[] = $comp->levelOne;
            }
        }

        // Get the levelone ids, names and industrycodes with given ids
        if (!empty($ids)) {
            $sql = "
                SELECT
                  id,
                  name,
                  industrycode
                FROM {report_gen_companydata}
                WHERE id
            ";
            list($in, $params) = $DB->get_in_or_equal($ids);

            $leveloneobjs = $DB->get_records_sql($sql . $in, $params);
        }

        return $leveloneobjs;
    }

    /**
     * Get the sectors with id, name and industrycode
     *
     * @param Object $leveloneobjsfiltered The array of objects with the municipalities
     *
     * @return mixed Array|null The array with the sector objects
     */
    public static function get_leveltwo_sectors_old($leveloneobjsfiltered) {
        global $DB;
        $industrycodes = array();
        $leveltwoobjs = null;

        // Get the industriecodes
        foreach ($leveloneobjsfiltered as $obj) {
            if (!in_array($obj->industrycode, $industrycodes)) {
                $industrycodes[] = $obj->industrycode;
            }
        }

        // Get the leveltwo ids, names and industrycodes with given industrycodes
        if (!empty($industrycodes)) {
            $sql = "
                SELECT
                  id,
                  name,
                  industrycode
                FROM {report_gen_companydata}
                WHERE hierarchylevel = 2
                  AND industrycode
            ";
            list($in, $params) = $DB->get_in_or_equal($industrycodes);

            $leveltwoobjs = $DB->get_records_sql($sql . $in, $params);
        }

        return $leveltwoobjs;
    }

    /**
     * Get the levelone locations with id, name and industrycode
     *
     * @param Int $userid The user id
     *
     * @return mixed Array|null The levelone locations
     */
    /**
     * @param           $userid
     * @return          array|null
     * @throws          Exception
     *
     * @updateDate      18/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add comments, exception ...
     */
    public static function get_levelone_municipalities($userid) {
        /* Variables    */
        global $DB;
        $ids            = array();
        $leveloneobjs   = null;
        $competences    = null;

        try {
            /* Get user competence  */
            $competences = Competence::Get_CompetenceData($userid);

            /* Get Level One connected with user    */
            if ($competences) {
                foreach ($competences as $comp) {
                    if (!in_array($comp->levelOne, $ids)) {
                        $ids[] = $comp->levelOne;
                    }
                }

                /* Get the levelone ids, names and industrycodes with given ids */
                if (!empty($ids)) {
                    /* SQL Instruction  */
                    $sql = " SELECT id,
                                    name,
                                    industrycode
                             FROM   {report_gen_companydata}
                             WHERE  id ";

                    /* Add search criteria  */
                    list($in, $params) = $DB->get_in_or_equal($ids);

                    /* Execute  */
                    $leveloneobjs = $DB->get_records_sql($sql . $in, $params);
                }//if_ids
            }//if_competences

            return $leveloneobjs;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_levelone_municipalities

    /**
     * @param           $leveloneobjsfiltered
     * @return          array|null
     * @throws          Exception
     *
     * @updateDate      17/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all sectors connected with level one
     */
    public static function get_leveltwo_sectors($leveloneobjsfiltered) {
        /* Variables    */
        global $DB;
        $levelOne       = array();
        $levelSectors   = null;
        $parents        = null;

        try {
            if ($leveloneobjsfiltered) {
                /* Get Level One */
                foreach ($leveloneobjsfiltered as $obj) {
                    $levelOne[$obj->id] = $obj->id;
                }//for_level_one_filtered

                /* Get all sectors connected with level one */
                $parents = implode(',',$levelOne);
                /* SQL Instruction  */
                $sql = "  SELECT    rc.id,
                                    rc.name,
                                    rc.industrycode
                          FROM      {report_gen_companydata}        rc
                              JOIN  {report_gen_company_relation}   rcr ON    rcr.companyid = rc.id
                                                                    AND   rcr.parentid  IN ($parents)
                          WHERE     rc.hierarchylevel = 2
                              AND   rcr.parentid ";

                /* Execute  */
                $levelSectors = $DB->get_records_sql($sql);
            }//if_level_one_filteres

            return $levelSectors;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_leveltwo_sectors

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @param           $categoryId
     * @param           $myCategories
     * @throws          Exception
     *
     * @creationDate    18/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get and add the subcategories connected with to my categories list
     */
    private static function getSubcategories($categoryId,&$myCategories) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['parent'] = $categoryId;

            /* Execute  */
            $rdo = $DB->get_records('course_categories',$params);
            if ($rdo) {
                /* Add subcategories to my categories list  */
                foreach ($rdo as $instance) {
                    $myCategories[$instance->id] = $instance->name;
                }//for_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getSubcategories
}
