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

/**
 * Step table class.
 *
 * @package    local_usertours
 * @copyright  2016 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_usertours;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * Step table class.
 *
 * @copyright  2016 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class step_table extends \flexible_table {
    /**
     * @var     int     $tourid     The id of the tour.
     */
    protected $tourid;

    /**
     * Construct the table for the specified tour ID.
     *
     * @param   int     $tourid     The id of the tour.
     */
    public function __construct($tourid) {
        parent::__construct('steps');
        $this->tourid = $tourid;

        $baseurl = new \moodle_url('/tool/usertours/configure.php', array(
                'id' => $tourid,
            ));
        $this->define_baseurl($baseurl);

        // Column definition.
        $this->define_columns(array(
            'title',
            'content',
            'target',
            'actions',
        ));

        $this->define_headers(array(
            get_string('title',   'local_usertours'),
            get_string('content', 'local_usertours'),
            get_string('target',  'local_usertours'),
            get_string('actions', 'local_usertours'),
        ));

        $this->set_attribute('class', 'admintable generaltable steptable');
        $this->setup();
    }

    /**
     * Format the current row's title column.
     *
     * @param   step    $step       The step for this row.
     * @return  string
     */
    protected function col_title(step $step) {
        global $OUTPUT;
        return $OUTPUT->render(helper::render_stepname_inplace_editable($step));
    }

    /**
     * Format the current row's content column.
     *
     * @param   step    $step       The step for this row.
     * @return  string
     */
    protected function col_content(step $step) {
        return $step->get_content(false);
    }

    /**
     * Format the current row's target column.
     *
     * @param   step    $step       The step for this row.
     * @return  string
     */
    protected function col_target(step $step) {
        return $step->get_target()->get_displayname();
    }

    /**
     * Format the current row's actions column.
     *
     * @param   step    $step       The step for this row.
     * @return  string
     */
    protected function col_actions(step $step) {
        $actions = [];

        if ($step->is_first_step()) {
            $actions[] = helper::get_filler_icon();
        } else {
            $actions[] = helper::format_icon_link($step->get_moveup_link(), 't/up', get_string('movestepup', 'local_usertours'));
        }

        if ($step->is_last_step()) {
            $actions[] = helper::get_filler_icon();
        } else {
            $actions[] = helper::format_icon_link($step->get_movedown_link(), 't/down',
                    get_string('movestepdown', 'local_usertours'));
        }

        $actions[] = helper::format_icon_link($step->get_edit_link(), 't/edit', get_string('edit'));

        $actions[] = helper::format_icon_link($step->get_delete_link(), 't/delete', get_string('delete'), 'moodle', [
            'data-action'   => 'delete',
            'data-id'       => $step->get_id(),
        ]);

        return implode('&nbsp;', $actions);
    }
}
