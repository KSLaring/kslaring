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
 * Course Home Page  - Renderer - Show Location Info - Lightbox Panel
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    23/02/2016
 * @author          eFaktor     (fbv)
 */

YUI.add('moodle-local_course_page-location', function(Y) {
    M.local_course_page = M.local_course_page || {};
    M.local_course_page.location = function (params) {
        var self = this,
            winHeight = Y.one("body").get("winHeight"),
            panelWidth = (winHeight < 900) ? '90%' : '70%';

        // Define the responsive dialogue, render the dialogue but don't show it.
        var panel = new M.core.dialogue({
            id: 'location',
            headerContent: params['header'],
            bodyContent: params['content'],
            draggable: true,
            visible: false,
            center: true,
            modal: true,
            render: true,
            responsive: true,
            width: panelWidth
        });

        // Set the click event handler to show the panel on click.
        Y.delegate('click', function (e) {
            panel.show();
        }, Y.one(document.body), '#show_location');

        // On window-resize change the width
        // and the center property depending on screen height.
        Y.use('event-resize', function(Y) {
            Y.on('windowresize', function () {
                var panelCenter = null;
                winHeight = Y.one("body").get("winHeight");

                if (winHeight < 900) {
                    panelWidth = '90%';
                    panelCenter = false;

                    var bb = panel.get('boundingBox');
                    bb.setStyles({'left' : '5%',
                      'top' : '10px'});
                } else {
                    panelWidth = '70%';
                    panelCenter = true;
                }

                panel.set('width', panelWidth);
                panel.set('center', panelCenter);

                // console.log(panel.get('width'));
            }, this);
        });
    };
});
