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

require_once($CFG->dirroot . '/enrol/waitinglist/lib.php');

//use renderable;
//use renderer_base;
//use stdClass;

/**
 * Class containing data for the local_friadmin course_detail table
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_coursedetail_table extends local_friadmin_widget implements renderable {

    // The table column names
    protected $colnames = array("header", "data");

    // The id of the course to display
    protected $courseid = 0;

    /**
     * Construct the coursedetail table renderable.
     */
    /**
     * @param       $courseId
     * @throws      Exception
     *
     * @updateDate  24/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add Exception
     * Add Comments
     */
    public function __construct($courseId) {
        /* Variables    */

        try {
            // Create the data object and set the first values
            parent::__construct();

            /* Add Course Id    */
            $this->courseid = $courseId;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//constructor

    /**
     * @return          string      $out The rendered Moodle table
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Create the Moodle table with the saved data
     *
     * @updateDate      24/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add comments
     * Add exception
     * Clean code
     */
    public function get_table_html() {
        /* Variables    */
        $out        = '';
        $table      = null;
        $tableModel = null;
        $result     = null;

        try {
            /* Create Table */
            $table = new \html_table();
            $table->id                          = 'coursedetails';
            $table->colclasses                  = $this->colnames;
            $table->attributes['cellspacing']   = '0';
            $table->attributes['class']         = 'generaltable';

            // Get the data for the table rows,
            // format the date columns
            $tableModel = new local_friadmin_coursedetail_table_sql_model($this->courseid);

            /* Add content from the table   */
            $result = $tableModel->data[0];
            if ($result) {
                $result = $this->format_date($result, array('date', 'deadline'));
                $result = $this->set_responsiblename($result);
                $result = $this->set_availseats($result);
                $result = $this->remove_id($result);
                $result = $this->row_to_columns($result);

                $table->data = $result;
                $out = \html_writer::table($table);
            }//if_result

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_table_html

    /**
     * @param           $data       The table data
     * @param           $fields     The fields containing dates
     * @return          mixed       The modified table data/Exception
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Format the date fields from UNIX timestamp to userdate.
     *
     * @updateDate      22/06/2015
     * @author          eFaktor     (fbv)
     */
    protected function format_date($data, $fields) {
        /* Variables    */

        try {
            foreach ($fields as $field) {
                if (isset($data[$field]) && $data[$field]) {
                    $data[$field] = '<span class="nowrap">' .
                        userdate($data[$field], '%d.%m.%Y', 99, false) . '</span>';
                } else {
                    $data[$field] = '-';
                }//if_Else
            }//for_each

            return $data;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//format_date


    /**
     * @param           $data       The table data
     * @return          mixed       The modified table data
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Remove the courseid
     *
     * @updateDate      24/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add exception,..
     */
    protected function remove_id($data) {
        /* Variables    */

        try {
            /* Remove course Id */
            unset($data['courseid']);

            return $data;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//remove_id

    /**
     * @param           $data       The table data
     * @return          mixed       The modified table data
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Add a link field
     *
     * @updateDate      24/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add comments, exception, clean code
     */
    protected function add_courselink($data) {
        /* Variables    */
        $url    = null;

        try {
            /* Set the url  */
            $url = new moodle_url('/course/view.php?id=' . $this->courseid);

            /* Save the url */
            $data['link'] = '<a href="' . $url . '">' . $url . '</a>';

            return $data;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_courselink

    /**
     * @param           $data       The table data
     * @return          mixed       The modified table data
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Get the manager = responsible user name
     * if the responsible field holds a number
     *
     * @updateDate      24/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add comments, exception, clean code
     */
    protected function set_responsiblename($data) {
        /* Variables    */
        global $DB;

        try {
            /* Check if is a numeric value  */
            if (is_numeric($data['responsible'])) {
                /* Get he name of the course manager    */
                if ($user = $DB->get_record('user', array('id' => $data['responsible']))) {
                    $data['responsible'] = fullname($user);
                }//if_user
            }//if_numeric

            return $data;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//set_responsiblename



    /**
     * @param           $data       The table data
     * @return          mixed       The modified table data
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Add the the available seats for the courses.
     *
     * @updateDate      24/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add comments, exception, clean code
     */
    protected function set_availseats($data) {
        /* Variables    */
        global $DB;
        $instance           = null;
        $enrolWaitingList   = null;

        try {
            /* Get the instance for Enrolment Waiting List  */
            $instance = $DB->get_record('enrol',array('courseid' => $data['courseid'], 'enrol' => 'waitinglist'));
            if ($instance) {
                /* Get the available Seats  */
                $enrolWaitingList = new enrol_waitinglist_plugin();
                $data['seats'] = $enrolWaitingList->get_vacancy_count($instance);
            }

            return $data;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//set_availseats

    /**
     * @param           $data       The table data
     * @return          array       The modified table data
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Switch the data row to columns with titles in the first column
     *
     * @updateDate      24/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add comments,exception...
     */
    protected function row_to_columns($data) {
        /* Variables    */
        $result = array();

        try {
            /* Add the content to the table */
            foreach ($data as $key => $field) {
                /**
                 * @updateDate  22/06/2015
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Temporay
                 */
                if (($key != 'targetgroup')) {
                    $result[] = array(get_string('course_' . $key, 'local_friadmin'), $field);
                }//if_key_temporary
            }//for_Each

            return $result;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//row_to_columns
}
