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
 * Course Home Page  - Renderer - Show Reviews - Lightbox Panel
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    09/07/2014
 * @author          eFaktor     (fbv)
 */

YUI.add('moodle-local_course_page-ratings', function(Y) {

    M.local_course_page = M.local_course_page || {};
    M.local_course_page.ratings = function(params) {
        var self = this;
        Y.delegate('click', function(e){
            var panel = new M.core.dialogue({
                id : 'ratings',
                headerContent: params['header'],
                bodyContent:params['content'],
                draggable: true,
                visible: true,
                modal: true,
                render:true,
                width: '80%'
            });

            Y.one('#ratings').setStyle('left','25px');
            Y.one('#ratings').setStyle('bottom','-50px');
            Y.one('#ratings').setStyle('width','80%');
            Y.one('#location').setStyle('position','fixed');

            panel.show();

        },Y.one(document.body), '#show');
    }
});