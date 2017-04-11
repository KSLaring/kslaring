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

// require_once( '../../../../config.php');

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
}