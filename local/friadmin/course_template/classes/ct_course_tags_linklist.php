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
 * Class containing data for the local_friadmin course_tags linklist area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2017 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ct_course_tags_linklist extends local_friadmin_widget implements renderable {
    /**
     * Create the linklist.
     *
     * @param int $courseid
     */
    public function __construct($courseid) {
        parent::__construct();

        // Set up strings.
        $strcontinueurl = get_string('continue');

        // Set up url.
        $continueurl = new moodle_url('/local/friadmin/course_template/course_template.php',
            array('id' => $courseid));

        $list = '<fieldset class="hidden">
            <div>
                <div id="fgroup_id_buttonar" class="fitem fitem_actionbuttons fitem_fgroup ">
                    <div class="felement fgroup">
                        <a class="btn" href="' . $continueurl . '">' . $strcontinueurl . '</a>
                    </div>
                </div>
            </div>
        </fieldset>';

        $this->data->content = $list;
    }//create_linklist

    /**
     * Buttons to show.
     *
     * @return mixed
     */
    public function getlinklistcontent() {
        return $this->data->content;
    }
}
