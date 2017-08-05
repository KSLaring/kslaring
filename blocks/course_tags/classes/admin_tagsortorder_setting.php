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
 * Admin settings class for the block course tags sort order options.
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Admin settings class for the block course tags sort order options.
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_tags_admin_tagsortorder_setting extends admin_setting {
    public function get_setting() {
        return $this->config_read($this->name);
    }

    public function write_setting($data) {
        $this->config_write($this->name, $data);

        return '';
    }

    public function output_html($data, $query = '') {
        $out = $this->group_tag_list($data);

        return format_admin_setting($this, $this->visiblename, $out, $this->description,
                true, '', get_string('none'), $query);
    }

    protected function group_tag_list($value) {
        global $OUTPUT, $PAGE;
        $data = new stdClass();
        $out = '';
        $grouptagssorted = array();

        $data->settingid = $this->get_id();
        $data->settingname = $this->get_full_name();
        $data->settingvalue = $value;
        $data->tagset = array();

        // Get the defined course tag collection.
        $tagcollid = core_tag_area::get_collection('core', 'course');
        $order = explode(' ', $value);
        $grouptags = \block_course_tags\util::get_meta_tags($tagcollid, \local_tag\tag::get_meta_group_prefix(), $order);

        // Get all tags related to the group tags and list them.
        foreach ($grouptags as $id => $grouptag) {
            $tagdata = new stdClass();
            $tagdata->id = $id;
            $tagdata->name = \local_tag\tag::get_meta_tag_stripped_name($grouptag->name,
                            \local_tag\tag::get_meta_group_prefix());
            $data->tagset[] = $tagdata;
        }

        $out .= $OUTPUT->render_from_template('block_course_tags/tagsortorder', $data);

        \core\notification::info(get_string('waitdragdrop', 'block_course_tags'));
        $PAGE->requires->string_for_js('readydragdrop', 'block_course_tags');
        $PAGE->requires->js_call_amd('block_course_tags/dd', 'init');

        return $out;
    }
}
