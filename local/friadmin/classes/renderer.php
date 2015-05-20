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

//use plugin_renderer_base;

/**
 * Renderer class for local_friadmin
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_renderer extends plugin_renderer_base {

    /**
     * Render the page.
     *
     * @param local_friadmin_courselist_page $page The page renderable
     *
     * @return string html for the page
     */
    public function render_local_friadmin_courselist_page(
        local_friadmin_courselist_page $page) {
        global $OUTPUT;

        $out = '';

        $out .= $OUTPUT->heading($page->data->title, 2);
        $out .= $this->render($page->data->filter);
        $out .= $this->render($page->data->table);

        return $out;
    }

    /**
     * Render the page.
     *
     * @param local_friadmin_coursedetail_page $page The page renderable
     *
     * @return string html for the page
     */
    public function render_local_friadmin_coursedetail_page(
        local_friadmin_coursedetail_page $page) {
        global $OUTPUT;

        $out = '';

        $out .= $OUTPUT->heading($page->data->title, 2);
        $out .= $this->render($page->data->table);
        $out .= $this->render($page->data->linklist);

        return $out;
    }

    /**
     * Render the course table.
     *
     * @param local_friadmin_courselist_table $table The table renderable
     *
     * @return string html for the page
     */
    public function render_local_friadmin_courselist_filter(
        local_friadmin_courselist_filter $filter) {

        return $filter->data->content;
    }

    /**
     * Render the course table.
     *
     * @param local_friadmin_courselist_table $table The table renderable
     *
     * @return string html for the page
     */
    public function render_local_friadmin_courselist_table(
        local_friadmin_courselist_table $table) {

        return $table->get_table_html();
    }

    /**
     * Render the course detail table.
     *
     * @param local_friadmin_coursedeatil_table $table The table renderable
     *
     * @return string html for the page
     */
    public function render_local_friadmin_coursedetail_table(
        local_friadmin_coursedetail_table $table) {

        return $table->get_table_html();
    }

    /**
     * Render the coursedetail linklist.
     *
     * @param local_friadmin_coursedetail_linklist $linklist The linklist renderable
     *
     * @return string html for the page
     */
    public function render_local_friadmin_coursedetail_linklist(
        local_friadmin_coursedetail_linklist $linklist) {

        return $linklist->data->content;
    }
}
