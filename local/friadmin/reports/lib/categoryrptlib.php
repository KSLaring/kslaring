<?php
// This file is part of Historical (Local)
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
//
// * Historical(local) - Historicallib
// *
// * @package         local                                                 !
// * @subpackage      historical/reports                                    !
// * @copyright       2017        eFaktor {@link http://www.efaktor.no}     !
// *                                                                        !
// * @updateDate      20/01/2017                                            !
// * @author          eFaktor     (nas)                                     !

class friadminrpt
{

    /**
     * Description
     * Gets all the course-categories with courses connected to them and returns them in a single array
     *
     * @param           NULL
     *
     * @return          array   All the course-categories
     * @throws          Exception
     *
     * @updateDate    08/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_categories() {
        // Variables!
        global $DB;
        $categories     = array();  // Array declaration for the categories.
        $rdo            = null;     // Used to query the database.

        $query = "SELECT  ca.id,
		                  ca.name
                  FROM    {course_categories} ca";

        try {
            $categories[0] = get_string('selectone', 'local_friadmin'); // Sets the first value in the array as "Select one...".
            $rdo = $DB->get_records_sql($query);

            // Gets all the categories from the kurskategori table that does have courses in them.
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $categories[$instance->id] = $instance->name;
                }
            }
            return $categories;
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_categories



    /**
     * Description
     * Gets all the course-categories with courses connected to them and returns them in a single array
     *
     * @param           NULL
     *
     * @return          array   All the course-categories
     * @throws          Exception
     *
     * @updateDate    08/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_courses() {
        // Variables!
        global $DB;
        $courses     = array();  // Array declaration for the categories.
        $rdo            = null;     // Used to query the database.

        $query = "SELECT  c.id,
		                  c.fullname
                  FROM    {course} c";

        try {
            $courses[0] = get_string('selectone', 'local_friadmin'); // Sets the first value in the array as "Select one...".
            $rdo = $DB->get_records_sql($query);

            // Gets all the categories from the kurskategori table that does have courses in them.
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $courses[$instance->id] = $instance->fullname;
                }
            }
            return $courses;
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_categories

    /**
     * Description
     * Gets all the courses based on category and used only by the javascript
     *
     * @param           integer     $category   The Category ID
     * @return          array|null  All the courses for javascript purposes
     * @throws          Exception
     *
     * @updateDate    08/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_courses_js($category) {
        // Variables!
        global $DB;
        $courses    = array();  // Array declaration for the courses.
        $rdo        = null;     // Used to query the database.

        // Query gets the courseid and the coursename based on the categoryid parameter in this function.
        $coursequery = "SELECT        c.id,
		                              c.fullname
                        FROM          {course} c
                          INNER JOIN  {course_categories} ca ON ca.id = c.category
                        WHERE         ca.id = :category
                        ORDER BY      c.fulllname";
        try {
            $courses[0] = get_string('selectone', 'local_historical'); // Sets the first value in the array as "Select one...".
            $rdo = $DB->get_records_sql($coursequery, array('category' => $category));

            return $rdo;
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_courses_js

    /**
     * Description
     * A function used to get the correct javasript values, it calls the M.core_user.init_courses in the javascript
     * @param           $course
     * @param           $category
     * @param           $prevcourse
     *
     * @updateDate    08/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_javascript_values($course, $category, $prevcourse) {

        global $PAGE;

        /* Initialise variables */
        $name       = 'lst_courses';
        $path       = '/local/friadmin/reports//js/report.js';
        $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
        $grpthree   = array('none', 'moodle');
        $strings    = array($grpthree);

        /* Initialise js module */
        $jsmodule = array('name'        => $name,
            'fullpath'    => $path,
            'requires'    => $requires,
            'strings'     => $strings
        );

        $PAGE->requires->js_init_call('M.core_user.init_courses',
            array($course, $category, $prevcourse),
            false,
            $jsmodule
        );
    }
}