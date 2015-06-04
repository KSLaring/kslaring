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

namespace local_friadmin;

defined('MOODLE_INTERNAL') || die;

use moodle_url;
use context_system;
use stdClass;

/**
 * The Friadmin class
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class friadmin {

    // The table renderable
    protected $table = null;

    // The filter renderable
    protected $filter = null;

    // The linklist renderable
    protected $linklist = null;

    // The create course renderable
    protected $create = null;

    // The page renderable
    protected $page = null;

    /**
     * The renderer
     * @var \core_renderer|\local_friadmin_renderer $output
     */
    protected $output;

    // The page context
    protected $context = null;

//    public function __construct() {
//    }

    /*
     * Inititialize the page
     *
     * Set the required information: context and pagelayout
     */
    public function init_page() {
        global $PAGE;

        $this->context = context_system::instance();
        $PAGE->set_context($this->context);
        $PAGE->set_pagelayout('standard');

        require_capability('block/frikomport:view', $this->context);
    }

    /*
     *
     */
    public function set_courselist_references($page, $filter, $table,
        \local_friadmin_renderer $output) {
        $this->page = $page;
        $this->filter = $filter;
        $this->table = $table;
        $this->output = $output;
    }

    /*
     *
     */
    public function set_coursedetail_references($page, $table, $linklist,
        \local_friadmin_renderer $output) {
        $this->page = $page;
        $this->table = $table;
        $this->linklist = $linklist;
        $this->output = $output;
    }

    /*
     *
     */
    public function set_coursetemplate_references($page, $create,
        \local_friadmin_renderer $output) {
        $this->page = $page;
        $this->create = $create;
        $this->output = $output;
    }

    /*
     * Set up the page
     */
    public function setup_courselist_page() {
        global $PAGE;

        $data = $this->page->data;

        $PAGE->set_url($data->url);
        $PAGE->set_docs_path('');
        $PAGE->set_title($data->title);

        $PAGE->navbar->add(get_string('pluginname', 'local_friadmin'));
        $PAGE->navbar->add($data->title);
    }

    /*
     * Set up the page
     */
    public function setup_coursedetail_page() {
        global $PAGE;

        $data = $this->page->data;

        $PAGE->set_url($data->url);
        $PAGE->set_docs_path('');
        $PAGE->set_title($data->title);

        $PAGE->navbar->add(get_string('pluginname', 'local_friadmin'));
        $name = get_string('courselist_title', 'local_friadmin');
        $url = new moodle_url('/local/friadmin/courselist.php');
        $PAGE->navbar->add($name, $url);
        $PAGE->navbar->add($data->title);
    }

    /*
     * Set up the page
     */
    public function setup_coursetemplate_page() {
        global $PAGE;

        $data = $this->page->data;

        $PAGE->set_url($data->url);
        $PAGE->set_docs_path('');
        $PAGE->set_title($data->title);

        $PAGE->navbar->add(get_string('pluginname', 'local_friadmin'));
        $PAGE->navbar->add($data->title);
    }

    /*
     * Display the course list
     */
    public function display_courselist_page() {
        $output = $this->output;

        $this->filter->render();
        $this->page->data->filter = $this->filter;
        $this->page->data->table = $this->table;

        echo $output->header();
        echo $output->render($this->page);
        echo $output->footer();
    }

    /*
     * Display the coursedetail page
     */
    public function display_coursedetail_page() {
        $output = $this->output;

        $this->page->data->table = $this->table;
        $this->page->data->linklist = $this->linklist;

        echo $output->header();
        echo $output->render($this->page);
        echo $output->footer();
    }

    /*
     * Display the coursetemplate page
     */
    public function display_coursetemplate_page() {
        $output = $this->output;

        $this->create->render();
        $this->page->data->create = $this->create;

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
     * Table getter
     */
    protected function get_table() {
        return $this->table;
    }

    /**
     * Output getter
     */
    protected function get_output() {
        return $this->output;
    }

    /**
     * Magic property method
     *
     * Attempts to call a set_$key method if one exists otherwise falls back
     * to simply set the property
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value) {
        if (method_exists($this, 'set_'.$key)) {
            $this->{'set_'.$key}($value);
        }
        $this->properties->{$key} = $value;
    }

    /**
     * Magic get method
     *
     * Attempts to call a get_$key method to return the property and ralls over
     * to return the raw property
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key) {
        if (method_exists($this, 'get_'.$key)) {
            return $this->{'get_'.$key}();
        }
        return $this->properties->{$key};
    }

    /**
     * Stupid PHP needs an isset magic method if you use the get magic method and
     * still want empty calls to work.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key) {
        if (method_exists($this, 'get_'.$key)) {
            $val = $this->{'get_'.$key}();
            return !empty($val);
        }
        return !empty($this->properties->{$key});
    }
}
