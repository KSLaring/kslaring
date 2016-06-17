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
 * Class containing data for the local_friadmin course_list table
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_courselist_table extends local_friadmin_widget implements renderable {

    // The table column names
    //protected $colnames = array("edit", "name", "date", "seats", "deadline", "length",
    //                            "municipality", "sector", "location");
    protected $colnames = array("edit", "name", "date", "seats", "deadline",
                                "municipality", "location");

    // The table column titles
    protected $colheaders = array();

    // The user municipality list
    protected $userleveloneids = null;

    /**
     * @var         array
     *
     * @updateDate  17/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Categories connected with user
     */
    protected  $myCategories    = array();

    // The related filter data returned from the form
    protected $filterdata = null;

    /**
     * @param               $baseurl
     * @param       null    $userleveloneids
     * @param       null    $usercategories
     * @param       null    $filterdata
     *
     * @throws              Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Construct the courselist_page renderable.
     *
     * @updateDate  17/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add the user categories parameter
     */
    public function __construct($baseurl, $userleveloneids = null, $usercategories = null,
        $filterdata = null) {
        /* Variables    */

        try {
            // Create the data object and set the first values
            parent::__construct();

            $this->data->baseurl    = $baseurl;
            $this->userleveloneids  = $userleveloneids;
            $this->filterdata       = $filterdata;
            $this->myCategories     = $usercategories;

            // Create the table column titles
            foreach ($this->colnames as $name) {
                $this->colheaders[] = get_string('course_' . $name, 'local_friadmin');
            }//for_Each
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//constructor

    /**
     * @return          string      The rendered Moodle flexitable
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Create the Moodle flexitable with the saved data
     *
     * @updateDate      17/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the user categories parameter
     */
    public function get_table_html() {
        /* Variables    */
        global $CFG;
        $out        = null;
        $table      = null;
        $tableModel = null;
        $result     = null;

        try {
            /* Add reference */
            require_once($CFG->libdir . '/tablelib.php');

            /* Create table */
            $table = new flexible_table('courselist');

            $table->define_columns($this->colnames);
            $table->define_headers($this->colheaders);
            $table->define_baseurl($this->data->baseurl);

            $table->set_attribute('cellspacing', '0');
            $table->set_attribute('id', 'courselist');
            $table->set_attribute('class', 'generaltable');

            $table->sortable(true, 'name', SORT_ASC);
            $table->collapsible(false);

            $table->setup();

            // Get the data for the table rows,
            // format the date columns and add the course edit link behind the course name
            /**
             * @updateDate  17/06/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Add the user categories parameter
             */
            $table_model = new local_friadmin_courselist_table_sql_model($this->userleveloneids,
                                                                         $this->myCategories,
                                                                         $this->filterdata,
                                                                         $table->get_sql_sort('courselist'));

            if ($result = $table_model->data) {
                $result = $this->format_date($result, array('date', 'deadline'));
                $result = $this->add_availseats($result);
                $result = $this->add_course_link_and_icons($result);
            }//if_result

            ob_start();
            $table->format_and_add_array_of_rows($result);
            $out = ob_get_clean();

            return $out;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_table_html

    /**
     * @param array     $data       The table data
     *
     * @return          array       The modified table data
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Add the link to the course detail page to the course name
     * and an edit icon with a link to the course settings page.
     */
    protected function add_course_link_and_icons($data) {
        /* Variables    */
        global $OUTPUT;
        $result         = array();
        $isArray        = null;
        $nameLink       = null;
        $icon           = null;
        $link           = null;
        $detailsLink    = null;
        $urlDetail      = null;
        $urlEdit        = null;

        try {
            foreach ($data as $row) {
                if (is_array($row)) {
                    $isArray    = true;
                    $row        = (object)$row;
                } else {
                    $isArray = false;
                }//if_is_array

                /* Set Detail Name  Link    */
                $urlDetail = new moodle_url('/local/friadmin/coursedetail.php', array('id' => $row->courseid));
                $nameLink   = html_writer::link($urlDetail, $row->name);
                $row->name  = $nameLink;

                /* Set Edit Link    */
                $urlEdit = new moodle_url('/course/edit.php', array('id' => $row->courseid));
                $icon    = $OUTPUT->pix_icon('t/edit', get_string('edit', 'local_friadmin'));
                $link    = html_writer::link($urlEdit, $icon);

                /* Set Detail  Link    */
                $icon        = $OUTPUT->pix_icon('t/viewdetails', get_string('show', 'local_friadmin'));
                $detailsLink = html_writer::link($urlDetail, $icon);

                $row->edit = $link . ' ' . $detailsLink;

                if ($isArray) {
                    $result[] = (array)$row;
                } else {
                    $result[] = $row;
                }//if_isArray
            }//for_each_row

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_course_link_and_icons

    /**
     * @param array     $data       The table data
     *
     * @return          array       The modified table data
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Add the the available seats for the courses.
     */
    protected function add_availseats($data) {
        /* Variables    */
        global $DB;
        $result             = array();
        $isArray            = null;
        $instance           = null;
        $enrolWaitingList   = null;

        try {
            foreach ($data as $row) {
                if (is_array($row)) {
                    $isArray    = true;
                    $row        = (object)$row;
                } else {
                    $isArray    = false;
                }//if_isArray

                /* Get Instance Enrolment Waiting List  */
                $instance = $DB->get_record('enrol', array('courseid' => $row->courseid,'enrol' => 'waitinglist'));
                if ($instance) {
                    /* Get Seats    */
                    $enrolWaitingList = new enrol_waitinglist_plugin();
                    $row->seats       = $enrolWaitingList->get_vacancy_count($instance);
                }//if_instance

                if ($isArray) {
                    $result[] = (array)$row;
                } else {
                    $result[] = $row;
                }//if_isArray
            }//for_each_row
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_availseats

    /**
     * @param stdClass  $data       The table data
     * @param array     $fields     The fields containing dates
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Format the date fields from UNIX timestamp to userdate.
     *
     * @updateDate  22/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Format date
     */
    protected function format_date($data, $fields) {
        /* Variables    */
        $result     = array();
        $isArray    = null;

        try {
            foreach ($data as $row) {
                if (is_array($row)) {
                    $isArray    = true;
                    $row        = (object)$row;
                } else {
                    $isArray    = false;
                }//if_is_Array

                foreach ($fields as $field) {
                    if (isset($row->$field) && ($row->$field)) {
                        $row->$field = '<span class="nowrap">' . userdate($row->$field,'%d.%m.%Y', 99, false) . '</span>';
                    } else {
                        $row->$field = '-';
                    }
                }//if_Else

                if ($isArray) {
                    $result[] = (array)$row;
                } else {
                    $result[] = $row;
                }//if_isArray
            }//for_each_row

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//format_date
}
