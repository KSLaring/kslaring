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

//    public function __construct() {
//    }

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
     * Get the levelone locations with id, name and industrycode
     *
     * @param Int $userid The user id
     *
     * @return mixed Array|null The levelone locations
     */
    public static function get_levelone_municipalities($userid) {
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
    public static function get_leveltwo_sectors($leveloneobjsfiltered) {
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
}
