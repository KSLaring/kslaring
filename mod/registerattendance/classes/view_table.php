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

/**
 * Class containing data for the mod_registerattendance view page table
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_registerattendance_view_table extends mod_registerattendance_widget implements renderable {
    /** @var object The reference to the main object. */
    protected $registerattendance = null;

    /** @var array The fecxitable column names. */
    protected $colnames = array('fullname', 'municipality', 'workplace', 'attended');

    /** @var object The course module object. */
    protected $cm = null;

    /** @var array The table column titles. */
    protected $colheaders = array();

    /** @var object The related filter data returned from the form. */
    protected $filterdata = null;

    /** @var string whether the data should be downloaded in some format, or '' to display it. */
    public $download = '';

    /** @var object The SQL model. */
    protected $sqlmodel = null;

    /**
     * Construct the view page table.
     *
     * @param string $baseurl
     * @param object $filterdata
     *
     * @throws Exception
     */
    public function __construct($baseurl, $filterdata = null, $cm = null, $registerattendance = null) {
        parent::__construct();

        $this->registerattendance = $registerattendance;
        $this->cm = $cm;
        $this->data->baseurl = $baseurl;
        $this->filterdata = $filterdata;

        // If excel download use »excel« as parameter.
        $this->download = optional_param('download', $this->download, PARAM_ALPHA);

        // Create the table column titles.
        foreach ($this->colnames as $name) {
            $this->colheaders[] = get_string('view_' . $name, 'mod_registerattendance');
        }
    }

    /**
     * Create the Moodle flexitable with the saved data
     *
     * @return string The rendered Moodle flexitable
     * @throws Exception
     */
    public function render() {
        global $CFG;
        $out = '';
        $table = null;
        $tablemodel = null;
        $result = null;

        // Create the table.
        $table = new mod_registerattendance_extended_flexible_table('attendancelist');

        $table->define_columns($this->colnames);
        $table->define_headers($this->colheaders);
        $table->define_baseurl($this->data->baseurl);

        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'attendancelist');
        $table->set_attribute('class', 'generaltable');
        $table->column_class('attended', 'attended');

        $table->sortable(true, 'lastname', SORT_ASC);
        $table->maxsortkeys = 1;
        //$table->no_sorting('attended');
        $table->collapsible(false);

        $table->defaultdownloadformat = 'excel';
        $table->show_download_buttons_at(array(TABLE_P_BOTTOM));

        $courseshortname = format_string($this->registerattendance->course->shortname, true,
            array('context' => context_course::instance($this->registerattendance->course->id)));
        $filename = $this->table_download_filename($courseshortname, $this->cm->name);
        $table->is_downloading($this->download, $filename,
            $courseshortname . ' ' . format_string($this->cm->name, true));

        if (!$table->is_downloading()) {
            $table->initialbars(true);
        } else {
            $table->initialbars(false);
            $table->pageable(false);
        }

        $table->setup();

        $sort = $table->get_sql_sort();

        // Either filter by given search name when set or by the alphabetical filter, don't filter with both.
        if (!empty($this->filterdata['searchname'])) {
            // Reset the alphabetical filter when searchname is given.
            if (isset($table->sess)) {
                $table->sess->i_first = '';
                $table->sess->i_last = '';
            }
            list($where1, $whereparams1) = $this->get_sql_namesearch_where($this->filterdata['searchname']);
            list($where2, $whereparams2) = $table->get_sql_where();
            if (empty($where2)) {
                $where = $where1;
                $whereparams = $whereparams1;
            } else {
                $where = '(' . $where2 . ') AND ' . $where1;
                $whereparams = array_merge($whereparams2, $whereparams1);
            }
        } else {
            list($where, $whereparams) = $table->get_sql_where();
        }

        // Get the data for the table rows from the database.
        $maxperpage = 0;
        $firstrow = 0;
        if (!$table->is_downloading()) {
            $maxperpage = empty($this->filterdata['showperpage']) ? MAX_LISTED_USERS : $this->filterdata['showperpage'];
            $usercount = (int)count_enrolled_users(context_course::instance($this->cm->course));
            $table->pagesize($maxperpage, $usercount);
            $firstrow = $table->get_page_start();
        }

        $tablemodel = new mod_registerattendance_view_table_sql_model(
            $this->filterdata,
            $sort,
            $where,
            $whereparams,
            $firstrow,
            $maxperpage,
            $this->cm);

        $this->sql_model = $tablemodel;

        // Get and process the data.
        if ($result = $tablemodel->data) {
            // Add the fullname needed for the flexitable alphabetic name filter.
            $result = $this->add_fullname($result);

            // Sort the table data by »attended« if the user has chosen to sort by »attended«.
            if (strpos($sort, 'attended') !== false) {
                $sortcolumns = $table->get_sort_columns();
                $result = $this->sortby_attended($result, $sortcolumns['attended']);
            }

            // Add the checkboxes with the attended state.
            if (!$table->is_downloading()) {
                $result = $this->add_attended_checkboxes($result, array('attended'));
            }
        }

        if (!$table->is_downloading()) {
            // Update the paging bar with the actual values.
            if ($tablemodel->countrecords > $maxperpage) {
                $table->pagesize($maxperpage, $tablemodel->countrecords);
            } else {
                $table->pageable(false);
            }

            ob_start();
            $table->format_and_add_array_of_rows($result);
            $out = ob_get_clean();
        } else {
            $table->format_and_add_array_of_rows($result, false);
            $table->finish_output();
        }

        return $out;
    }

    /**
     * Add the user's fullname, remove firstname and lastname
     *
     * @param object $data Object with user data objects
     *
     * @return array The extended user data
     */
    protected function add_fullname($data) {
        $result = $data;

        if ($data) {
            $result = array_map(array($this, 'callback_fullname'), $data);
        }

        return $result;
    }

    /**
     * Callback for the user's fullname
     *
     * @param object $row The user object from the database
     *
     * @return object The extended user data row
     */
    protected function callback_fullname($row) {
        $row->fullname = fullname($row);

        return $row;
    }

    /**
     * Callback to filter the users that attended
     *
     * @param object $row The user object from the database
     *
     * @return bool
     */
    protected function callback_show_attended($row) {
        return $row->attended == 1;
    }

    /**
     * Callback to filter the users that have not attended
     *
     * @param object $row The user object from the database
     *
     * @return bool
     */
    protected function callback_show_not_attended($row) {
        return $row->attended == 0;
    }

    /**
     * Set the checkboxes with the attended state
     *
     * @param array  $data      The data set
     * @param string $sortorder The sort order
     *
     * @return array
     */
    protected function sortby_attended($data, $sortorder = 'ASC') {
        $result = array();

        // Sort the array objects by »attended«.
        core_collator::asort_objects_by_property($data, 'attended');

        // If sortorder is »DESC« then reverse the array.
        if ($sortorder !== 'ASC') {
            $data = array_reverse($data);
        }

        $result = $data;

        return $result;
    }

    /**
     * Set the checkboxes with the attended state
     *
     * @param object $data   The data set
     * @param array  $fields The fields to change
     *
     * @return array
     */
    protected function add_attended_checkboxes($data, $fields) {
        $result = array();
        $isarray = null;

        foreach ($data as $row) {
            if (is_array($row)) {
                $isarray = true;
                $row = (object)$row;
            } else {
                $isarray = false;
            }//if_is_Array

            foreach ($fields as $field) {
                if (isset($row->$field)) {
                    $checked = '';
                    $value = 0;
                    if ($row->$field) {
                        $checked = ' checked="checked"';
                        $value = 1;
                    }
                    $html = '<input type="checkbox" data-userid="' . $row->id . '" data-cmid="' . $this->cm->id .
                        '" id="userid_' . $row->id . '" name="userid_' . $row->id . '"' . $checked . '>';
                    $row->$field = $html;
                }
            }//for_each_field

            if ($isarray) {
                $result[] = (array)$row;
            } else {
                $result[] = $row;
            }//if_isArray
        }//for_each_row

        return $result;
    }

    /**
     * Construct the sql to search for the given name part in the firstname and lastname.
     *
     * @param string $searchname The name part to search for
     *
     * @return array The sql query part and params
     */
    protected function get_sql_namesearch_where($searchname) {
        global $DB;

        $likefirstname = $DB->sql_like('firstname', ':firstname', false);
        $likelastname = $DB->sql_like('lastname', ':lastname', false);
        $where = "($likefirstname OR $likelastname)";
        $whereparams = array(
            'firstname' => '%' . $searchname . '%',
            'lastname' => '%' . $searchname . '%'
        );

        return array($where, $whereparams);
    }


    /**
     * Create a filename for use when downloading data. It is
     * expected that this will be passed to flexible_table::is_downloading, which
     * cleans the filename of bad characters and adds the file extension.
     *
     * @param string $courseshortname the course shortname.
     * @param string $modname         the module name.
     *
     * @return string the filename.
     */
    function table_download_filename($courseshortname, $modname) {
        return $courseshortname . '-' . format_string($modname, true) . '-' .
        get_string('list', 'mod_registerattendance');
    }
}
