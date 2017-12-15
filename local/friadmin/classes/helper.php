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
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user is a super user
     */
    public static function CheckCapabilityFriAdmin() {
        /* Variables    */
        global $DB, $USER;
        $contextCat     = null;
        $contextCourse  = null;
        $contextSystem  = null;
        $params         = null;
        $rdo            = null;
        $sql            = null;

        try {
            if (is_siteadmin($USER)) {
                return true;
            }
            /* Search Criteria  */
            $params = array();
            $params['user']         = $USER->id;
            $contextCat             = CONTEXT_COURSECAT;
            $contextCourse          = CONTEXT_COURSE;
            $contextSystem          = CONTEXT_SYSTEM;

            /* SQL Instruction  */
            $sql = " SELECT		ra.id
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 		r.id			= ra.roleid
                                                            AND		r.archetype		IN ('manager','coursecreator')
                        JOIN	{context}		    ct		ON		ct.id			= ra.contextid
                                                            AND		ct.contextlevel	IN ($contextCat,$contextCourse,$contextSystem)
                     WHERE		ra.userid 		= :user ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CheckCapabilityFriAdmin


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
        $context        = null;
        $contextCat     = null;
        $contextCourse  = null;
        $contextSystem  = null;

        try {
            /* Get all the categories   */
            $categoriesLst = $DB->get_records('course_categories');

            /* Search Criteria  */
            $params = array();
            $params['user']     = $USER->id;
            $contextCat         = CONTEXT_COURSECAT;
            $contextCourse      = CONTEXT_COURSE;
            $contextSystem      = CONTEXT_SYSTEM;

            /* SQL Instruction  */
            $sql = " SELECT		ra.id
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 	r.id			= ra.roleid
                                                            AND	r.archetype		IN ('manager','coursecreator')
                                                            AND r.shortname     = r.archetype
                        JOIN    {context}           ct      ON  ct.id			= ra.contextid
                                                            AND	ct.contextlevel	IN ($contextCat,$contextCourse,$contextSystem)
                     WHERE		ra.userid 		= :user
                        AND		ra.contextid 	= :context ";

            /* For each Category checks if the user has permissions */
            foreach ($categoriesLst as $category) {
                /* Get Context Category */
                $context = context_coursecat::instance($category->id);

                /**
                 * @updateDate      22/06/2015
                 * @author          eFaktor     (fbv)
                 *
                 * Description
                 * Admin site can see everything
                 */
                if (!has_capability('moodle/category:manage', $context)
                    &&
                    !has_capability('moodle/course:create', $context)) {
                    /* Execute   */
                    $params['context'] = $context->id;
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
        $leveloneobjs   = array();
        $competences    = null;

        try {
            /* Get user competence  */
            $competences = Competence::get_competence_data($userid);

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

    /**
     * Get the user's local template category.
     *
     * @return int The category id
     */
    public static function get_localtempcategory() {
        global $DB, $USER;

        $categoryid = 0;

        if ($record = $DB->get_record('friadmin_local_templates', array('userid' => $USER->id))) {
            $categoryid = $record->categoryid;

            // Always check if the selected category still exists because the category may have been deleted.
            if (!$DB->record_exists('course_categories', array('id' => $categoryid))) {
                $DB->delete_records('friadmin_local_templates', array('userid' => $USER->id));
                $categoryid = 0;
            }
        }

        return $categoryid;
    }

    /**
     * Check if the user selected template category exists.
     *
     * @return bool True or false
     */
    public static function localtempcategory_exists($catid = 0) {
        global $DB;

        return $DB->record_exists('course_categories', array('id' => $catid));
    }

    /**
     * Get the user's local template category.
     *
     * @return array The course ids for the two template types.
     */
    public static function get_preftemplate_selection() {
        global $DB, $USER;

        $preferredtemplates = array(
            TEMPLATE_TYPE_EVENT => 0,
            TEMPLATE_TYPE_NETCOURSE => 0
        );

        if ($result = $DB->get_records('friadmin_preferred_template', array('userid' => $USER->id))) {
            foreach ($result as $row) {
                if ($row->type == TEMPLATE_TYPE_EVENT) {
                    $preferredtemplates[TEMPLATE_TYPE_EVENT] = $row->courseid;
                } else if ($row->type == TEMPLATE_TYPE_NETCOURSE) {
                    $preferredtemplates[TEMPLATE_TYPE_NETCOURSE] = $row->courseid;
                }
            }
        }

        return $preferredtemplates;
    }

    /**
     * Get the user managed categories and the template list
     *
     * @return      array       An array with the two popup lists
     * @throws      Exception
     */
    public static function get_usercategories_data() {
        /* Variables    */
        global $CFG;
        $result = null;
        $pluginInfo = null;
        $templateCatId = null;
        $courseCat = null;
        $templateCourses = null;

        try {
            require_once($CFG->libdir . '/coursecatlib.php');

            /* Plugin Info      */
            $pluginInfo = get_config('local_friadmin');
            $localtempcategory = self::get_localtempcategory();

            /* Result Structure */
            $result = array(
                'localtempcategory' => $localtempcategory,
                'localtempcategoryexists' => self::localtempcategory_exists($localtempcategory),
                'preftemplates' => self::get_preftemplate_selection(),
                'categories' => array(),
                'eventtemplates' => array(),
                'netcoursetemplates' => array()
            );

            /* Get Categories   */
            $result['categories'] = self::getMyCategories();

            /* Fill the data    */
            if ($pluginInfo) {
                if (isset($pluginInfo->template_category) && ($pluginInfo->template_category)) {
                    /* Get Template Category && Course Category */
                    $templateCatId = $pluginInfo->template_category;

                    // Get Courses category.
                    $sql = " SELECT c.id,
                            c.fullname,
                            c.format,
                            c.visible
                     FROM   {course} c 
                     WHERE  (c.category = $templateCatId
                             OR
                             c.category =  $localtempcategory
                             )";

                    // Add the chosen course formats to the query if any.
                    $selformats = $pluginInfo->template_list;

                    if (!empty($selformats)) {
                        $selformats = explode(',', $selformats);
                        $formatsqlarray = array();
                        foreach ($selformats as $formatname) {
                            $formatsqlarray[] = 'c.format = \'' . $formatname . '\'';
                        }
                        $whereselformats = ' AND (';
                        $whereselformats .= implode(' OR ', $formatsqlarray);
                        $whereselformats .= ')';

                        $sql .= $whereselformats;
                    }

                    global $DB;
                    $templateCourses = $DB->get_records_sql($sql);

                    /* Add to result Structure  */
                    foreach ($templateCourses as $templateCo) {
                        if ($templateCo->visible) {
                            if (strpos($templateCo->format, 'classroom') !== false) {
                                $result['eventtemplates'][$templateCo->id] = $templateCo->fullname;
                            } else {
                                $result['netcoursetemplates'][$templateCo->id] = $templateCo->fullname;
                            }
                        }//if_visible
                    }//for_templatesCourse
                }//if_template_category
            }//if_pluginInfo

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_popup_data

    /**
     * Get the user related location related menu data for
     * the municipality, sectors and locations
     *
     * @param int|null $userId
     * @param int|null $municipalityid
     * @param int|null $sectorid
     *
     * @return           array
     * @throws           Exception
     */
    public static function get_user_locationdata($userId = null, $municipalityid = null,
        $sectorid = null) {
        global $USER;
        $result = null;
        $leveloneobjs = null;
        $leveloneobjsfiltered = array();
        $userleveloneids = array();

        // Set the $municipalityid to null if it is 0 or -1 to get all sectors and locations.
        if ($municipalityid <= 0) {
            $municipalityid = null;
        }

        // Set the $sectorid to null if it is 0 or -1 to get all locations.
        if ($sectorid <= 0) {
            $sectorid = null;
        }

        try {
            // Result structure.
            $result = array(
                'municipality' => array(),
                'sector' => array(),
                'location' => array()
            );

            if (is_null($userId)) {
                $userId = $USER->id;
            }//id_userid

            // Get the competence related municipalities
            // The $leveloneobjs array contains objects with
            // id, name and industrycode properties.
            $leveloneobjs = self::get_levelone_municipalities($userId);

            //Get all my municipalities.
            foreach ($leveloneobjs as $obj) {
                $result['municipality'][$obj->id] = $obj->name;
                $userleveloneids[] = $obj->id;

                // If a municipality has been selected then use only that one.
                if (!is_null($municipalityid)) {
                    if ($municipalityid == $obj->id) {
                        $leveloneobjsfiltered[] = $obj;
                    }
                } else {
                    $leveloneobjsfiltered[] = $obj;
                }
            }//for_levelone_obj

            //Get all categories where the user is a super user.
            //$myCategories = self::getMyCategories();

            if (!empty($leveloneobjsfiltered)) {
                // Get the sectors for the relevant municipalities via industrycodes.
                $leveltwoobjs = self::get_leveltwo_sectors($leveloneobjsfiltered);

                foreach ($leveltwoobjs as $obj) {
                    if (!in_array($obj->id, $result['sector'])) {
                        $result['sector'][$obj->id] = $obj->name;
                    }
                }

                if (is_null($sectorid)) {
                    // Get the locations for the relevant municipalities via levelone ids
                    $locationsobjs = self::get_locations($leveloneobjsfiltered);
                } else {
                    $locationsobjs = self::get_locations_for_sector($sectorid);
                }

                foreach ($locationsobjs as $obj) {
                    if (!in_array($obj->id, $result['location'])) {
                        $result['location'][$obj->id] = $obj->name;
                    }
                }
            }//obj_filteref

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_user_locationdata

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * Get the locations with id, name
     *
     * @param Object $leveloneobjsfiltered The array of objects with the municipalities
     *
     * @return mixed Array|null The array with the sector objects
     */
    /**
     * @param        $leveloneobjsfiltered
     *
     * @return       array|null
     * @throws       Exception
     *
     * @updateDate  18/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add comments, exception...
     */
    protected function get_locations($leveloneobjsfiltered) {
        /* Variables */
        global $DB;
        $ids = array();
        $locations = null;

        try {
            if ($leveloneobjsfiltered) {
                /* Get the municipality ids */
                foreach ($leveloneobjsfiltered as $obj) {
                    if (!in_array($obj->id, $ids)) {
                        $ids[] = $obj->id;
                    }
                }

                /* Get the location ids and names with given municipality ids */
                if (!empty($ids)) {
                    /* SQL Instruction  */
                    $sql = "  SELECT    id,
                                        name
                              FROM      {course_locations}
                              WHERE     levelone ";

                    /* Get search criteria  */
                    list($in, $params) = $DB->get_in_or_equal($ids);

                    /* Execute  */
                    $locations = $DB->get_records_sql($sql . $in, $params);
                }//if_Empty
            }//if_leveloneobjsfiltered

            return $locations;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_locations

    /**
     * Get the locations for the given sectors with id, name
     *
     * @param int $sectorid The selected sector id
     *
     * @return mixed Array|null The array with the sector objects
     *
     * @throws  Exception
     */
    protected function get_locations_for_sector($sectorid) {
        global $DB;
        $locations = null;

        try {
            if ($sectorid) {
                // Get the location ids and names for the given sector id.
                $sql = "SELECT	lo.id,
                                lo.name
                        FROM {course_locations} lo
                        -- LEVLE TWO
                        JOIN {report_gen_company_relation}	cr_two	
                          ON cr_two.parentid = lo.levelone
                          AND cr_two.companyid = :sector_selected
                        JOIN {report_gen_companydata}	co_two 
                          ON co_two.id = cr_two.companyid
                        -- LEVEL ONE
                        JOIN {report_gen_company_relation}	cr_one 
                          ON cr_one.companyid = cr_two.parentid
                        -- LEVEL ZERO
                        JOIN {report_gen_company_relation}	cr_zero 
                          ON cr_zero.companyid = cr_one.companyid  ";

                // Get search criteria.
                $params = array('sector_selected' => $sectorid);

                $locations = $DB->get_records_sql($sql, $params);
            }

            return $locations;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_locations_for_sector

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

    /**
     * Duplicate the coruse with the given data.
     *
     * @param object $data The course data
     *
     * @return array|string
     * @throws Exception
     */
    public static function duplicate_course($data) {
        $result = self::create_course($data);

        return $result;
    }

    /**
     * Create the course with the given data
     * Restore the course from an exisitng course backup file.
     * Create a course backup if non exists.
     *
     * @param object $data The form result.
     */
    /**
     * @return          array|string
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * @updateDate      23/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Rebuild function.
     *  - Add Exception
     *  - Check if the user has the correct permissions
     *  - Create a Fake Permission before create the new course from the template and
     *  remove later.
     *  - Errors is an array. So, it has to be converted into a string
     */
    protected static function create_course($data) {
        /* Variables    */
        global $DB, $CFG;

        $result = '';
        $fakepermission = null;
        $newcourseid = null;
        $error = null;
        $coursedata = null;
        $newcourse = null;
        $info = null;
        $errormsg = null;
        $admin = get_admin();

        try {
            $coursedata = array(
                'userid' => $admin->id,
                'sourcedir' => $CFG->dataroot . '/temp/test/',
                'categoryid' => $data->selcategory,
                'fullname' => $data->selfullname,
                'shortname' => $data->selshortname,
                'startdate' => $data->startdate,
            );

            list($newcourseid, $error) = self::restore_course((int)$data->id, $coursedata, $data->includeusers, true);

            if (empty($error)) {
                // Get the infos for the new course.
                $newcourse = $DB->get_record('course', array('id' => $newcourseid), '*', MUST_EXIST);
                if ($newcourse) {
                    $info = array('id' => $newcourse->id,
                        'shortname' => $newcourse->shortname,
                        'fullname' => $newcourse->fullname
                    );

                    $result .= '<p class="result">';
                    $result .= get_string('coursetemplate_result', 'local_friadmin', $info) . '</p>';
                } else {
                    /* The course should exist when no processor errors had been generated */
                    $result = '<p class="result">';
                    $result .= get_string('coursetemplate_error', 'local_friadmin') . '</p>';
                }//if_else_new_course
            } else {
                $result .= $error;
            }

            return array($info, $result);
        } catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//create_course

    /**
     * Restore a backup course file with the given parameters for the new course.
     *
     * @param int $cid The course id of the course to restore
     * @param array $options The backup options
     * @param int $withusers Backup with users or without
     * @param bool $forcebackup If true always create a new course backup, don't use an existing one
     *
     * @return array With the course id and the error
     * @throws Exception
     * @throws dml_transaction_exception
     * @throws restore_controller_exception
     */
    protected static function restore_course($cid, $options, $withusers = 1, $forcebackup = false) {
        /* Variables */
        global $CFG, $DB,$SESSION;
        $error      = '';
        $courseid   = null;
        $component  = 'backup';
        $filearea   = 'course';
        $itemid     = '0';
        $sourcefile = null;

        try {
            require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

            $coursecontext  = context_course::instance($cid);
            $fs             = get_file_storage();

            $backupid = self::create_backup_if_needed($cid, $withusers, $forcebackup);

            /**
             * @updateDate      29/06/2016
             * @author          eFaktor     (fbv)
             *
             * Description
             * Remove get_file_inof because it compares with course = 1
             */
            //$browser    = get_file_browser();
            //$fileinfo   = $browser->get_file_info($coursecontext, $component, $filearea, $itemid);

            //if (is_a($fileinfo, 'file_info_stored')) {
                $files      = $fs->get_area_files($coursecontext->id, $component, $filearea, $itemid);
                $sourcefile = self::newest_stored_file($files);
            //}

            if (is_null($sourcefile)) {
                $error .= 'No backupfile found for course id ' . $cid . "<br>\n";
                return array($courseid, $error);
            }

            // Extract the file.
            $packer     = get_file_packer('application/vnd.moodle.backup');
            $backupid   = restore_controller::get_tempdir_name(SITEID, $options['userid']);
            $path       = "$CFG->tempdir/backup/$backupid/";
            if (!$packer->extract_to_pathname($sourcefile, $path)) {
                $error .= 'Invalid backup file ' . $sourcefile->get_filename() . "<br>\n";
                return array($courseid, $error);
            }

            // Start delegated transaction.
            $transaction = $DB->start_delegated_transaction();

            // Create new course.
            $courseid = restore_dbops::create_new_course(fix_utf8($sourcefile->get_filename()),
                                                         'restored-' . $backupid, $options['categoryid']);

            /**
             * @updateDate  16/11/2016
             * @author      eFaktor     (fbv)
             *
             * Description
             * Save fullname and shortname to restore the course
             * and send welcome emails with the right name
             */
            if (!isset($SESSION->friadmin_fullname)) {
                $SESSION->friadmin_fullname = null;
            }//fullname

            if (!isset($SESSION->friadmin_shortname)) {
                $SESSION->friadmin_shortname = null;
            }//shortname

            $SESSION->friadmin_fullname     = fix_utf8($options['fullname']);
            $SESSION->friadmin_shortname    = fix_utf8($options['shortname']);


            // Restore backup into course.
            $controller = new restore_controller($backupid,
                                                 $courseid,
                                                 backup::INTERACTIVE_NO,
                                                 backup::MODE_SAMESITE,
                                                 $options['userid'],
                                                 backup::TARGET_NEW_COURSE);

            if ($controller->execute_precheck()) {
                $controller->execute_plan();
            } else {
                unset($SESSION->friadmin_fullname);
                unset($SESSION->friadmin_shortname);
                $error .= 'Precheck fails for ' . $sourcefile->get_filename() . ' ... skipping' . "<br>\n";
                $results = $controller->get_precheck_results();
                foreach ($results as $type => $messages) {
                    foreach ($messages as $index => $message) {
                        $error .= 'precheck ' . $type . '[' . $index . '] = ' . $message . "<br>\n";
                    }
                }
                try {
                    $transaction->rollback(new Exception('Prechecked failed'));
                } catch (Exception $e) {
                    unset($transaction);
                    $controller->destroy();
                    unset($controller);

                    return array($courseid, $error);
                }
            }

            // Commit and clean up.
            unset($SESSION->friadmin_fullname);
            unset($SESSION->friadmin_shortname);
            $transaction->allow_commit();
            unset($transaction);
            $controller->destroy();
            unset($controller);

            // Set the parameters chosen by the user.
            $course = new stdClass;
            $course->id = $courseid;
            $course->fullname = fix_utf8($options['fullname']);
            $course->shortname = $options['shortname'];
            if (isset($options['startdate'])) {
                $course->startdate = $options['startdate'];
            }
            $DB->update_record('course', $course);

            // Update waitinglist when users are not included
            if (!$withusers) {
                enrol_waitinglist_plugin::update_restored_instance($cid,$courseid);
                // Other enrolment methods dfferent to waiting list
                self::update_restored_instance($cid,$courseid);
            }

            return array($courseid, $error);
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//restore_course

    /**
     * Description
     * Restore all instance connected with the differents enrolment methods
     * after the course has been duplicated
     *
     * @creationDate        30/01/2017
     * @author              eFaktor     (fbv)
     *
     * @param       integer $oldcourse
     * @param       integer $courseid
     *
     * @throws              Exception
     */
    private static function update_restored_instance($oldcourse,$courseid) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $rdoOld     = null;
        $sql        = null;
        $sqlWhere   = null;
        $sqlNew     = null;
        $params     = null;

        try {
            // Search criteria
            $params = array();
            $params['enrol'] = 'waitinglist';

            // Sql Instruction
            $sql = " SELECT e.*
                     FROM   {enrol} e
                     WHERE  e.enrol != :enrol ";

            // Old course
            $params['course'] = $oldcourse;
            $sqlWhere = " AND e.courseid = :course ";
            // Execute
            $rdoOld = $DB->get_records_sql($sql . $sqlWhere,$params);
            if ($rdoOld) {
                foreach ($rdoOld as $instance) {
                    // New course
                    $params['course']       = $courseid;
                    $params['other_enrol']  = $instance->enrol;

                    // Add criterias
                    $sqlWhere = " AND e.courseid = :course 
                                  AND e.enrol    = :other_enrol ";

                    // Build new sql
                    $sqlNew = $sql . $sqlWhere;

                    // Execute
                    $rdo = $DB->get_record_sql($sqlNew,$params);
                    if ($rdo) {
                        // Update
                        $rdo->status            = $instance->status;
                        $rdo->sortorder         = $instance->sortorder;
                        $rdo->name              = $instance->name;
                        $rdo->enrolperiod       =  $instance->enrolperiod;
                        $rdo->enrolstartdate    = $instance->enrolstartdate;
                        $rdo->enrolenddate      = $instance->enrolenddate;
                        $rdo->expirynotify      = $instance->expirynotify;
                        $rdo->expirythreshold   = $instance->expirythreshold;
                        $rdo->notifyall         = $instance->notifyall;
                        $rdo->password          = $instance->password;
                        $rdo->cost              = $instance->cost;
                        $rdo->currency          = $instance->currency;
                        $rdo->roleid            = $instance->roleid;
                        $rdo->customint1        = $instance->customint1;
                        $rdo->customint2        = $instance->customint2;
                        $rdo->customint3        = $instance->customint3;
                        $rdo->customint4        = $instance->customint4;
                        $rdo->customint5        = $instance->customint5;
                        $rdo->customint6        = $instance->customint6;
                        $rdo->customint7        = $instance->customint7;
                        $rdo->customint8        = $instance->customint8;
                        $rdo->customchar1       = $instance->customchar1;
                        $rdo->customchar2       = $instance->customchar2;
                        $rdo->customchar3       = $instance->customchar3;
                        $rdo->customdec1        = $instance->customdec1;
                        $rdo->customdec2        = $instance->customdec2;
                        $rdo->customtext1       = $instance->customtext1;
                        $rdo->customtext2       = $instance->customtext2;
                        $rdo->customtext3       = $instance->customtext3;
                        $rdo->customtext4       = $instance->customtext4;
                        $rdo->company           = $instance->company;

                        // Execute
                        $DB->update_record('enrol',$rdo);
                    }//if_rdo
                }//for_rdoOld
            }//if_rdoOld

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//update_restored_instance

    /**
     * Get the newest file in the file area.
     *
     * @param array $files The stored files
     *
     * @return null|stored_file The newest file
     */
    protected static function newest_stored_file($files) {
        $newesttime = 0;
        $newest = null;

        /* @var $f stored_file */
        foreach ($files as $f) {
            if ($f->get_filename() === '.') {
                continue;
            }

            if ($f->get_timecreated() > $newesttime) {
                $newesttime = $f->get_timecreated();
                $newest = $f;
            }
        }

        return $newest;
    }

    /**
     * Create an automatic course backup if none exisits.
     *
     * @param int $cid The course id of the course to restore
     * @param int $withusers Backup with users or without
     * @param bool $forcebackup If true always create a new course backup, don't use an existing one
     *
     * @return      null|int        The backup id if success, null if failure
     * @throws      Exception
     */
    protected static function create_backup_if_needed($cid, $withusers = 1, $forcebackup = false) {
        /* Variables */
        global $CFG, $DB;
        $result     = null;
        $component  = 'backup';
        $filearea   = 'course';
        $itemid     = '0';

        try {
            $coursecontext  = context_course::instance($cid);
            $fs             = get_file_storage();

            if ($forcebackup || $fs->is_area_empty($coursecontext->id, $component, $filearea)) {
                // Always delete existing backup files.
                $fs->delete_area_files($coursecontext->id, $component, $filearea);

                $course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
                if ($course) {
                    $admin = get_admin();
                    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
                    $bc = new backup_controller(backup::TYPE_1COURSE, $cid, backup::FORMAT_MOODLE,
                        backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $admin->id);

                    $bc->get_plan()->get_setting('users')->set_value($withusers);

                    // Set the default filename.
                    $format = $bc->get_format();
                    $type = $bc->get_type();
                    $id = $bc->get_id();
                    $users = $bc->get_plan()->get_setting('users')->get_value();
                    $anonymised = $bc->get_plan()->get_setting('anonymize')->get_value();
                    $filename = backup_plan_dbops::get_default_backup_filename($format, $type,
                        $id, $users, $anonymised);
                    $bc->get_plan()->get_setting('filename')->set_value($filename);

                    // Backup the course.
                    $bc->set_status(backup::STATUS_AWAITING);
                    $bc->execute_plan();

                    // Get the results.
                    $backupid = $bc->get_backupid();
                    //$results = $bc->get_results();

                    $bc->destroy();

                    $result = $backupid;
                }
            }

            return $result;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch

    }
}
