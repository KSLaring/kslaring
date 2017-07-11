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
 * Fellesdata Integration - Javascript
 *
 * @package         local/fellesdata
 * @subpackage      js
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    07/02/2016
 * @author          eFaktor     (fbv)
 *
 */


YUI().use('node', function(Y) {
    /* Mark Mapping Companies    */
    if (Y.one('#id_type').get('value') == 'ge') {
        Y.one('#id_jr_no_generic_ge').set('checked',1);
        window.onbeforeunload = null;
    }else if (Y.one('#id_type').get('value') == 'no') {
        Y.one('#id_jr_generic_no').set('checked',1);
        window.onbeforeunload = null;
    }else {
        Y.one('#id_jr_no_generic_ge').set('checked',1);
        window.onbeforeunload = null;
    }

    /* Mapping Companies Option */
    Y.one('#id_jr_no_generic_ge').on('click', function (e) {
        Y.one('#id_jr_generic_no').set('checked',0);
        window.onbeforeunload = null;
    });

    Y.one('#id_jr_generic_no').on('click', function (e) {
        Y.one('#id_jr_no_generic_ge').set('checked',0);
        window.onbeforeunload = null;
    });


    window.onbeforeunload = null;

});