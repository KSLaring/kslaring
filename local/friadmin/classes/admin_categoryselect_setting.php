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
 * Admin settings class for extended category selection.
 *
 * @package    local_friadmin
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Admin settings class for extended category selection.
 *
 * @package    local_friadmin
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_categoryselect_setting extends admin_setting_configselect {
    //public function get_setting() {
    //    return $this->config_read($this->name);
    //}
    //
    //public function write_setting($data) {
    //    $this->config_write($this->name, $data);
    //
    //    return '';
    //}

    /**
     * Returns XHTML select field
     *
     * Ensure the options are loaded, and generate the XHTML for the select
     * element and any warning message. Separating this out from output_html
     * makes it easier to subclass this class.
     *
     * @param string $data    the option to show as selected.
     * @param string $current the currently selected option in the database, null if none.
     * @param string $default the default selected option.
     *
     * @return array the HTML for the select element, and a warning message.
     */
    public function output_select_html($data, $current, $default, $extraname = '') {
        if (!$this->load_choices() or empty($this->choices)) {
            return array('', '');
        }

        $warning = '';
        if (is_null($current)) {
            // First run.
        } else if (empty($current) and (array_key_exists('', $this->choices) or array_key_exists(0, $this->choices))) {
            // No warning.
        } else if (!array_key_exists($current, $this->choices)) {
            $warning = get_string('warningcurrentsetting', 'admin', s($current));
            if (!is_null($default) and $data == $current) {
                $data = $default; // Use default instead of first value when showing the form.
            }
        }

        $selecthtml = '<select id="' . $this->get_id() . '" name="' . $this->get_full_name() . $extraname . '">';
        foreach ($this->choices as $key => $value) {
            // The string cast is needed because key may be integer - 0 is equal to most strings!
            $selecthtml .= '<option value="' . $key . '"' . ((string)$key == $data ? ' selected="selected"' : '') . '>' . $value . '</option>';
        }
        $selecthtml .= '</select>';

        return array($selecthtml, $warning);
    }

    public function output_html($data, $query = '') {
        //$out = $this->group_tag_list($data);
        //
        //return format_admin_setting($this, $this->visiblename, $out, $this->description,
        //        true, '', get_string('none'), $query);

        $default = $this->get_defaultsetting();
        $current = $this->get_setting();

        list($selecthtml, $warning) = $this->output_select_html($data, $current, $default);
        if (!$selecthtml) {
            return '';
        }

        if (!is_null($default) and array_key_exists($default, $this->choices)) {
            $defaultinfo = $this->choices[$default];
        } else {
            $defaultinfo = NULL;
        }

        $return = '<div class="form-select defaultsnext">' . $selecthtml . '</div>';

        $return .= '';

        return format_admin_setting($this, $this->visiblename, $return, $this->description, true, $warning, $defaultinfo, $query);

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
