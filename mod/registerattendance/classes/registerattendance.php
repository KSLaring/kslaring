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

namespace mod_registerattendance;

require_once(__DIR__ . '/../lib.php');

use moodle_url;
use context_system;
use stdClass;

/**
 * The Register attendance class
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class registerattendance {

    // Set the max courses listed in the user course listing.
    const MAX_LISTED_USERS = 10;

    // The selector renderable.
    protected $selector = null;

    // The filter renderable.
    protected $filter = null;

    // The linklist renderable.
    protected $linklist = null;

    // The page renderable.
    protected $page = null;

    // The course object.
    protected $course = null;

    // The module object.
    protected $cm = null;

    // The module context.
    protected $context = null;

    // The course completion object.
    protected $completion = null;

    // The renderer.
    protected $output = null;

    // $canregister is used to determine if a user can register participants.
    protected $canregister = null;

    /**
     * Register Attendance constructor.
     */
    public function __construct() {
    }

    /*
     * Inititialize the pages
     *
     * Set the required information: context and pagelayout
     */
    public function init_page($pagelayout = 'incourse') {
        global $PAGE;

        $this->context = \context_module::instance($this->cm->id);

        $PAGE->set_context($this->context);
        $PAGE->set_pagelayout($pagelayout);
    }

    /*
     * Set up the view page references
     *
     *
     * @param object $page The course object
     * @param object $filter The filter object
     * @param object $table The table object
     * @param \mod_registerattendance_renderer $output The module renderer
     * @param object $course The course object
     * @param object $cm The course module
     * @param object $completion The course completion
     */
    public function set_view_references($page, $filter, $table,
        \mod_registerattendance_renderer $output, $course, $cm, $completion) {
        $this->page = $page;
        $this->filter = $filter;
        $this->table = $table;
        $this->output = $output;
        $this->course = $course;
        $this->cm = $cm;
        $this->completion = $completion;

        $coursecontext = \context_course::instance($course->id);
        $this->canregister = has_capability('mod/registerattendance:registerattendance', $coursecontext);

        $this->page->registerattendance = $this;
    }

    /*
     * Set up the view page
     */
    public function setup_view_page() {
        global $PAGE;

        $data = $this->page->data;

        $PAGE->set_url($data->url);
        $PAGE->set_docs_path('');
        $PAGE->set_title($data->title);

        $PAGE->requires->yui_module('moodle-mod_registerattendance-changecompletionstate',
            'M.mod_registerattendance.changecompletionstate.init', array(), null, true);
    }

    /*
     * Display the view page
     */
    public function display_view_page() {
        $output = $this->output;

        $this->filter->render();
        $this->page->data->filter = $this->filter;
        $this->page->data->table = $this->table;

        if (empty($this->table->download)) {
            echo $output->header();
            echo $output->render($this->page);
            echo $output->footer();
        } else {
            echo $output->render($this->page);
        }
    }

    /*
     * Set up the bulkregister page references
     *
     *
     * @param object $page The course object
     * @param \mod_registerattendance_renderer $output The module renderer
     * @param object $course The course object
     * @param object $cm The course module
     * @param object $completion The course completion
     */
    public function set_bulkregister_references($page, $selector,
        \mod_registerattendance_renderer $output, $course, $cm, $completion) {
        $this->page = $page;
        $this->selector = $selector;
        $this->output = $output;
        $this->course = $course;
        $this->cm = $cm;
        $this->completion = $completion;

        $coursecontext = \context_course::instance($course->id);
        $this->canregister = has_capability('mod/registerattendance:registerattendance', $coursecontext);

        $this->page->registerattendance = $this;
    }

    /*
     * Set up the bulkregister page
     */
    public function setup_bulkregister_page() {
        global $PAGE;

        $data = $this->page->data;

        $PAGE->set_url($data->url);
        $PAGE->set_docs_path('');
        $PAGE->set_title($data->title);
    }

    /*
     * Display the bulkregister page
     */
    public function display_bulkregister_page() {
        $output = $this->output;

        //$this->filter->render();
        //$this->page->data->filter = $this->filter;
        $this->selector->render();
        $this->page->data->selector = $this->selector;

        echo $output->header();
        echo $output->render($this->page);
        echo $output->footer();
    }

    /**
     * Page getter
     */
    protected function get_page() {
        return $this->page;
    }

    /**
     * Selector getter
     */
    protected function get_selector() {
        return $this->selector;
    }

    /**
     * Table getter
     */
    protected function get_table() {
        return $this->table;
    }

    /**
     * Filter getter
     */
    protected function get_filter() {
        return $this->filter;
    }

    /**
     * Select getter
     */
    protected function get_select() {
        return $this->select;
    }

    /**
     * Output getter
     */
    protected function get_output() {
        return $this->output;
    }

    /**
     * Course getter
     */
    protected function get_course() {
        return $this->course;
    }

    /**
     * CM getter
     */
    protected function get_cm() {
        return $this->cm;
    }

    /**
     * Completion getter
     */
    protected function get_completion() {
        return $this->completion;
    }

    /**
     * Canregister getter
     */
    protected function get_canregister() {
        return $this->canregister;
    }

    /**
     * Magic property method
     *
     * Attempts to call a set_$key method if one exists otherwise falls back
     * to simply set the property
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value) {
        if (method_exists($this, 'set_' . $key)) {
            $this->{'set_' . $key}($value);
        }
        $this->{$key} = $value;
    }

    /**
     * Magic get method
     *
     * Attempts to call a get_$key method to return the property and ralls over
     * to return the raw property
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key) {
        if (method_exists($this, 'get_' . $key)) {
            return $this->{'get_' . $key}();
        }

        return $this->{$key};
    }

    /**
     * Stupid PHP needs an isset magic method if you use the get magic method and
     * still want empty calls to work.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key) {
        if (method_exists($this, 'get_' . $key)) {
            $val = $this->{'get_' . $key}();

            return !empty($val);
        }

        return !empty($this->{$key});
    }
}
