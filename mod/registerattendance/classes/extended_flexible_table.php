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

require_once($CFG->libdir . '/tablelib.php');

/**
 * Class extending the Moodle flexible tabel class
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_registerattendance_extended_flexible_table extends flexible_table {
    /**
     * This function is not part of the public api.
     * This method is overridden.
     *
     * Place the download menu below the paging bar.
     */
    public function finish_html() {
        global $OUTPUT;
        if (!$this->started_output) {
            // No data has been added to the table.
            $this->print_nothing_to_display();

        } else {
            // Print empty rows to fill the table to the current pagesize.
            // This is done so the header aria-controls attributes do not point to
            // non existant elements.
            $emptyrow = array_fill(0, count($this->columns), '');
            while ($this->currentrow < $this->pagesize) {
                $this->print_row($emptyrow, 'emptyrow');
            }

            echo html_writer::end_tag('tbody');
            echo html_writer::end_tag('table');
            echo html_writer::end_tag('div');
            $this->wrap_html_finish();

            // Paging bar.
            if ($this->use_pages) {
                $pagingbar = new paging_bar($this->totalrows, $this->currpage, $this->pagesize, $this->baseurl);
                $pagingbar->pagevar = $this->request[TABLE_VAR_PAGE];
                echo $OUTPUT->render($pagingbar);
            }

            echo $this->register_buttons();

            if (in_array(TABLE_P_BOTTOM, $this->showdownloadbuttonsat)) {
                echo $this->download_buttons();
            }

        }
    }

    /**
     * Add the buttons for bulk registering.
     * This is a new method.
     */
    protected function register_buttons() {
        $out = '';

        if ($this->is_downloading()) {
            return $out;
        }

        $out .= html_writer::start_tag('div', array('id' => 'bulkregister', 'class' => 'buttons clearfix'));
        $out .= html_writer::tag('button', get_string('registerlisted', 'mod_registerattendance'),
            array('id' => 'registerlisted'));
        $out .= html_writer::tag('button', get_string('unregisterlisted', 'mod_registerattendance'),
            array('id' => 'unregisterlisted'));
        $out .= html_writer::end_tag('div');

        return $out;
    }
}
