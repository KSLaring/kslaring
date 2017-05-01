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
 * Web Service functions for the category select setting.
 *
 * @package    local
 * @subpackage friadmin
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_friadmin\external;

require_once(__DIR__ . '/../../../../config.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use coursecat;

/**
 * Web Service functions for the category select setting.
 *
 * @package    local
 * @subpackage friadmin
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class categoryselect extends external_api {
    /**
     * Get the category sub categories.
     *
     * @param int $catid The category id for qhic the subcategories shall be returned.
     *
     * @return array Feedback
     */
    public static function get_subcategories($catid) {
        global $CFG, $PAGE, $OUTPUT;
        $result = array();
        require_once($CFG->libdir . '/coursecatlib.php');
        $PAGE->set_context(\context_system::instance());
        $categoryid = $catid;
        $coursecategory = coursecat::get($categoryid);
        $context = (object)array(
            'catparent' => $coursecategory->id,
            'catlistdepth' => $coursecategory->depth,
            'categorylist' => array()
        );


        // Set up the data.
        $coursecategies = coursecat::get($categoryid)->get_children();

        foreach ($coursecategies as $cat) {
            $listitem = array(
                'catid' => $cat->id,
                'catname' => $cat->name,
                'catdepth' => $cat->depth,
                'catpath' => $cat->path,
                'withchildren' => coursecat::get($cat->id)->has_children() ? ' with-children not-loaded' : null
            );
            $context->categorylist[] = (object)$listitem;
        }

        $subcategories = $OUTPUT->render_from_template('local_friadmin/friadmin_categoryselect_category_list', $context);

        $result['subcategorieshtml'] = json_encode($subcategories);

        return $result;
    }

    /**
     * The parameters for get_subcategories.
     *
     * @return external_function_parameters
     */
    public static function get_subcategories_parameters() {
        return new external_function_parameters([
            'catid' => new external_value(PARAM_INT, 'Category id'),
        ]);
    }

    /**
     * The return configuration for get_subcategories.
     *
     * @return external_single_structure
     */
    public static function get_subcategories_returns() {
        return new external_single_structure([
            'subcategorieshtml' => new external_value(PARAM_RAW, 'The HTML structure with the requested subcategories',
                VALUE_OPTIONAL),
        ]);
    }
}
