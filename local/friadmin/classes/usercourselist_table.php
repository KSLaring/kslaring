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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/enrol/waitinglist/lib.php');

/**
 * Class containing data for the local_friadmin usercourse_list table
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_usercourselist_table extends local_friadmin_widget implements renderable {

    // The table column names.
    protected $colnames = array("name", "date", "seats", "deadline", "municipality", "location", "edit");

    // The table column titles.
    protected $colheaders = array();

    // The user municipality list.
    protected $userleveloneids = null;

    // Categories connected with user.
    protected $mycategories = array();

    // The related filter data returned from the form.
    protected $filterdata = null;

    /**
     * Construct the courselist_page renderable.
     *
     * @param               $baseurl
     * @param       null    $userleveloneids
     * @param       null    $usercategories
     * @param       null    $filterdata
     *
     * @throws              Exception
     */
    public function __construct($baseurl, $userleveloneids = null, $usercategories = null,
        $filterdata = null) {

        try {
            // Create the data object and set the first values.
            parent::__construct();

            $this->data->baseurl = $baseurl;
            $this->userleveloneids = $userleveloneids;
            $this->filterdata = $filterdata;
            $this->mycategories = $usercategories;

            // Create the table column titles.
            foreach ($this->colnames as $name) {
                $this->colheaders[] = get_string('usercourse_' . $name, 'local_friadmin');
            } // End for_Each.
        } catch (Exception $ex) {
            throw $ex;
        } // End try_catch.
    } // End constructor.

    /**
     * Create the Moodle flexitable with the saved data
     *
     * @return          string      The rendered Moodle flexitable
     * @throws          Exception
     */
    public function get_table_html() {
        global $CFG;
        $out = null;
        $table = null;
        $tablemodel = null;
        $result = null;

        try {
            // Add reference.
            require_once($CFG->libdir . '/tablelib.php');

            // Create table.
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
            // format the date columns and add the course edit link behind the course name.
            $tablemodel = new local_friadmin_usercourselist_table_sql_model(
                $this->userleveloneids,
                $this->mycategories,
                $this->filterdata,
                $table->get_sql_sort('courselist'));


            if ($result = $tablemodel->data) {
                $morecourses = false;
                if (count($result) > local_friadmin\friadmin::MAX_LISTED_COURSES) {
                    $morecourses = true;
                }
                $result = $this->format_date($result, array('date', 'deadline'));
                $result = $this->add_availseats($result);
                $result = $this->add_course_link_and_icons($result);

                ob_start();
                $table->format_and_add_array_of_rows($result);
                $out = ob_get_clean();

                if ($morecourses) {
                    $out .= html_writer::tag('p',
                        get_string('usercourse_morecourses', 'local_friadmin'),
                        array('class' => 'morecourses'));
                }
            } // End if_result.

            return $out;
        } catch (Exception $ex) {
            throw $ex;
        } // End try_catch.
    } // End get_table_html.

    /**
     * Add the link to the course detail page to the course name
     * and an edit icon with a link to the course settings page.
     *
     * @param array $data The table data
     *
     * @return  array  The modified table data
     * @throws  Exception
     */
    protected function add_course_link_and_icons($data) {
        global $OUTPUT;
        $result = array();
        $isarray = null;
        $namelink = null;
        $icon = null;
        $link = null;
        $detailslink = null;
        $urldetail = null;
        $urledit = null;

        try {
            foreach ($data as $row) {
                if (is_array($row)) {
                    $isarray = true;
                    $row = (object)$row;
                } else {
                    $isarray = false;
                } // End if_is_array.

                // Set Detail Name Link.
                $urldetail = new moodle_url('/course/view.php',
                    array('id' => $row->courseid));
                $namelink = html_writer::link($urldetail, $row->name);
                $row->name = $namelink;

                // Set Detail Link.
                $icon = $OUTPUT->pix_icon('t/viewdetails', get_string('show', 'local_friadmin'));
                $detailslink = html_writer::link($urldetail, $icon);

                $row->edit = $detailslink;

                if ($isarray) {
                    $result[] = (array)$row;
                } else {
                    $result[] = $row;
                } // End if_isArray.
            } // End for_each_row.

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        } // End try_catch.
    } // End add_course_link_and_icons.

    /**
     * Add the the available seats for the courses.
     *
     * @param array $data The table data
     *
     * @return array The modified table data
     * @throws Exception
     */
    protected function add_availseats($data) {
        global $DB;
        $result = array();
        $isarray = null;
        $instance = null;
        $enrolwaitinglist = null;

        try {
            foreach ($data as $row) {
                if (is_array($row)) {
                    $isarray = true;
                    $row = (object)$row;
                } else {
                    $isarray = false;
                } // End if_isArray.

                // Get Instance Enrolment Waiting List.
                $instance = $DB->get_record('enrol', array('courseid' => $row->courseid,
                    'enrol' => 'waitinglist'));
                if ($instance) {
                    // Get Seats.
                    $enrolwaitinglist = new enrol_waitinglist_plugin();
                    $row->seats = $enrolwaitinglist->get_vacancy_count($instance);
                } // End if_instance.

                if ($isarray) {
                    $result[] = (array)$row;
                } else {
                    $result[] = $row;
                } // End if_isArray.
            } // End for_each_row.
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        } // End try_catch.
    } // End add_availseats.

    /**
     * Format the date fields from UNIX timestamp to userdate.
     *
     * @param array $data   The table data
     * @param array $fields The fields containing dates
     *
     * @return          array
     * @throws          Exception
     */
    protected function format_date($data, $fields) {
        $result = array();
        $isarray = null;

        try {
            foreach ($data as $row) {
                if (is_array($row)) {
                    $isarray = true;
                    $row = (object)$row;
                } else {
                    $isarray = false;
                } // End if_is_Array.

                foreach ($fields as $field) {
                    if (isset($row->$field) && ($row->$field)) {
                        $row->$field = '<span class="nowrap">' . userdate($row->$field,
                                '%d.%m.%Y', 99, false) . '</span>';
                    } else {
                        $row->$field = '-';
                    }
                } // End if_Else.

                if ($isarray) {
                    $result[] = (array)$row;
                } else {
                    $result[] = $row;
                } // End if_isArray.
            } // End for_each_row.

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        } // End try_catch.
    } // End format_date.

    /**
     * Add a counter in the first table row.
     *
     * @param array $data The table data
     *
     * @return          array
     * @throws          Exception
     */
    protected function add_course_counter($data) {
        $result = array();
        $isarray = null;
        $counter = 1;

        try {
            foreach ($data as $row) {
                if (is_array($row)) {
                    $isarray = true;
                    $row = (object)$row;
                } else {
                    $isarray = false;
                } // End if_is_Array.

                $row->counter = $counter++;

                if ($isarray) {
                    $result[] = (array)$row;
                } else {
                    $result[] = $row;
                } // End if_isArray.
            } // End for_each_row.

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        } // End try_catch.
    } // End format_date.
}
