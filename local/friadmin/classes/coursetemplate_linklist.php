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

//use renderable;
//use renderer_base;
//use stdClass;

/**
 * Class containing data for the local_friadmin coursetemplate linklist area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_coursetemplate_linklist extends local_friadmin_widget implements renderable {

    /**
     * Construct the coursetemplate linklist renderable.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * @param           $courseId
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Create the linklist
     */
    public function create_linklist($courseId) {
        /* Variables    */
        $str_another    = null;
        $url_another    = null;
        $str_settings   = null;
        $url_settings   = null;
        $str_go         = null;
        $url_go         = null;
        $list1          = null;

        try {
            /* Set Up strings   */
            $str_settings   = get_string('coursetemplate_settings', 'local_friadmin');
            $str_go         = get_string('coursetemplate_go', 'local_friadmin');

            /* Set up Url       */
            $url_settings   = new moodle_url('/course/edit.php',array('id' => $courseId));
            $url_go         = new moodle_url('/course/view.php',array('id' => $courseId));

            /* Set Up List 1    */
            $list1 = '<ul class="unlist buttons-linklist">
                        <a class="btn" href="' . $url_go . '">' . $str_go . '</a>
                        <a class="btn" href="' . $url_settings . '">' . $str_settings . '</a>
                      </ul>';

            $this->data->content = $list1;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//create_linklist

    /**
     * @return          mixed
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Buttons to show
     */
    public function getContentListLink() {
        return $this->data->content;
    }
}
