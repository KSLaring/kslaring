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
 * Renderer class for mod_registerattendance
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_registerattendance_renderer extends plugin_renderer_base {
    /**
     * Render the view page.
     *
     * @param mod_registerattendance_view_page $page The page renderable
     *
     * @return string html for the page
     */
    public function render_mod_registerattendance_view_page(mod_registerattendance_view_page $page) {
        $out = '';

        /* @var \mod_registerattendance\registerattendance $registerattendance The object */
        $registerattendance = $page->registerattendance;
        $out .= $registerattendance->output->heading($page->data->title, 2, array('class' => 'title'));

        if ($registerattendance->canregister) {
            // The table needs to be constructed and rendered here, we need the attended user information.
            $tablehtml = $this->render($page->data->table);
            $out .= $this->render_buldkregister_button($registerattendance);
        }

        if (!$registerattendance->canregister) {
            $out .= $page->construct_user_feedback($registerattendance);
        } else {
            $out .= $this->render($page->data->filter);
            $out .= $tablehtml;
        }

        $out .= $this->render_returntocoruse_button($registerattendance);

        return $out;
    }

    /**
     * Render the view table.
     *
     * @param mod_registerattendance_view_table $table The table renderable
     *
     * @return string html for the page
     */
    public function render_mod_registerattendance_view_table(
        mod_registerattendance_view_table $table) {

        return $table->render();
    }

    /**
     * Render the view filer.
     *
     * @param mod_registerattendance_view_filter $filter The filter renderable
     *
     * @return string html for the page
     */
    public function render_mod_registerattendance_view_filter(
        mod_registerattendance_view_filter $filter) {

        return $filter->data->content;
    }
    /**
     * Render the bulkregister page.
     *
     * @param mod_registerattendance_bulkregister_page $page The page renderable
     *
     * @return string html for the page
     */
    public function render_mod_registerattendance_bulkregister_page(mod_registerattendance_bulkregister_page $page) {
        $out = '';

        /* @var \mod_registerattendance\registerattendance $registerattendance The object */
        $registerattendance = $page->registerattendance;
        $out .= $registerattendance->output->heading($page->data->title, 2, array('class' => 'title'));

        $out .= $this->render($page->data->selector);

        $out .= $this->render_returntoregisterattendance_button($registerattendance);

        return $out;
    }

    /**
     * Render the view table.
     *
     * @param mod_registerattendance_view_table $table The table renderable
     *
     * @return string html for the page
     */
    public function render_mod_registerattendance_bulkregister_selector(
        mod_registerattendance_bulkregister_selector $selector) {

        return $selector->data->content;
    }

    /**
     * Build the HTML for the return button at the bottom of the page.
     *
     * @param \mod_registerattendance\registerattendance $registerattendance The module object
     *
     * @return string The return button as HTML
     */
    protected function render_returntocoruse_button($registerattendance) {
        $out = '';

        // Construct the "Return to course" button.
        $courseurl = new moodle_url("/course/view.php",
            array('id' => $registerattendance->course->id));
        $out .= html_writer::start_tag('div', array('class' => 'buttons'));
        $out .= $registerattendance->output->single_button($courseurl,
            get_string('returntocourse', 'mod_registerattendance'), 'get');
        $out .= html_writer::end_tag('div');

        return $out;
    }

    /**
     * Build the HTML for the bulkregister button and the info about attended/not attended
     * user numbers at the top of the page.
     *
     * @param \mod_registerattendance\registerattendance $registerattendance The module object
     *
     * @return string The button as HTML
     */
    protected function render_buldkregister_button($registerattendance) {
        $out = '';

        $a = new stdClass();
        $a->enrolled = count($registerattendance->table->sql_model->enrolledusers);
        $a->haveattended = count($registerattendance->table->sql_model->completedusers);

        // Construct the "bulkregister" button.
        $out .= html_writer::start_tag('div', array('class' => 'buttons bulkregister clearfix'));
        $out .= html_writer::start_tag('span', array('class' => 'haveatttended'));
        $out .= get_string('haveattended', 'mod_registerattendance', $a);
        $out .= html_writer::end_tag('span');
        $out .= html_writer::end_tag('div');

        return $out;
    }

    /**
     * Build the HTML for the return to register attendance button at the bottom of the page.
     *
     * @param \mod_registerattendance\registerattendance $registerattendance The module object
     *
     * @return string The return button as HTML
     */
    protected function render_returntoregisterattendance_button($registerattendance) {
        $out = '';

        // Construct the "Return to registerattendance" button.
        $url = new moodle_url("/mod/registerattendance/view.php",
            array('id' => $registerattendance->cm->id));
        $out .= html_writer::start_tag('div', array('class' => 'buttons'));
        $out .= $registerattendance->output->single_button($url,
            get_string('returntoregisterattendance', 'mod_registerattendance'), 'get');
        $out .= html_writer::end_tag('div');

        return $out;
    }
}
