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
 * Friadmin course category select element.
 *
 * @package    local_friadmin
 * @copyright  2018 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/form/select.php');

/**
 * Friadmin course category select element.
 *
 * @package    local_friadmin
 * @copyright  2018 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_category_select_form_element extends MoodleQuickForm_select {

    /* var object $context The template data. */
    protected $templatedata = null;

    /**
     * Constructor
     *
     * @param string $elementName  Element name
     * @param mixed  $elementLabel Label(s) for an element
     * @param array  $options      Options to control the element's display
     * @param mixed  $attributes   Either a typical HTML attribute string or an associative array.
     * @param mixed  $templatedata The template data.
     */
    public function __construct($elementName = null, $elementLabel = null, $options = array(), $attributes = null) {

        if ($elementName == null) {
            // This is broken quickforms messing with the constructors.
            return;
        }

        $this->templatedata = $attributes['context'];
        unset($attributes['context']);

        //$this->setValue($options['selectedcat']);

        //if (!empty($options['cmid'])) {
        //    $cmid = $options['cmid'];
        //
        //    $current = \core_competency\api::list_course_module_competencies_in_course_module($cmid);
        //
        //    // Note: We just pick the outcome set on the first course_module_competency - because in our UI are are
        //    // forcing them to be all the same for each activity.
        //    if (!empty($current)) {
        //        $one = array_pop($current);
        //        $this->setValue($one->get_ruleoutcome());
        //    }
        //}
        //$validoptions = course_module_competency::get_ruleoutcome_list();
        parent::__construct($elementName, $elementLabel, $options, $attributes);
    }

    /**
     * Returns HTML for select form element.
     *
     * @return string
     */
    public function toHtml() {
        global $OUTPUT;

        $this->templatedata->selectname = $this->getName();
        //$this->templatedata->selectid = 'id_' . $this->getName();
        $this->templatedata->selectid = $this->_attributes['id'];

        $html = '';
        if ($this->getMultiple()) {
            // Adding an hidden field forces the browser to send an empty data even though the user did not
            // select any element. This value will be cleaned up in self::exportValue() as it will not be part
            // of the select options.
            $html .= '<input type="hidden" name="' . $this->getName() . '" value="_qf__force_multiselect_submission">';
        }
        if ($this->_hiddenLabel) {
            $this->_generateId();
            $html .= '<label class="accesshide" for="' . $this->getAttribute('id') . '" >' . $this->getLabel() . '</label>';
        }

        //$html .= parent::toHtml(); // Original call to the parent method.

        $tabs = $this->_getTabs();

        //$strhtml = '';
        //
        //$attrstring = $this->_getAttrString($this->_attributes);
        //$strhtml .= $tabs . '<select' . $attrstring . ">\n";
        //
        //foreach ($this->_options as $option) {
        //    if (is_array($this->_values) && in_array((string)$option['attr']['value'], $this->_values)) {
        //        $this->_updateAttrArray($option['attr'], array('selected' => 'selected'));
        //    }
        //    $strhtml .= $tabs . "\t<option" . $this->_getAttrString($option['attr']) . '>' .
        //        $option['text'] . "</option>\n";
        //}
        //
        //$strhtml .= $strhtml . $tabs . '</select>';
        //$html .= $strhtml . '<br>';

        $html .= $tabs . $OUTPUT->render_from_template('local_friadmin/friadmin_categoryselect_content', $this->templatedata);

        return $html;
    }

    /**
     * We check the options and return only the values that _could_ have been
     * selected. We also return a scalar value if select is not "multiple"
     *
     * @param array $submitValues submitted values
     * @param bool  $assoc        if true the retured value is associated array
     *
     * @return mixed
     */
    public function exportValue(&$submitValues, $assoc = false) {
        if (empty($this->_options)) {
            return $this->_prepareValue(null, $assoc);
        }

        $value = $this->_findValue($submitValues);
        if (is_null($value)) {
            $value = $this->getValue();
        }

        // Don't filter not listed values as done in the Moodle select form element.
        $cleaned = array($value);

        if (empty($cleaned)) {
            return $this->_prepareValue(null, $assoc);
        }
        if ($this->getMultiple()) {
            return $this->_prepareValue($cleaned, $assoc);
        } else {
            return $this->_prepareValue($cleaned[0], $assoc);
        }
    }
}
