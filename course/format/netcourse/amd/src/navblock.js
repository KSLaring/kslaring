/*global define: false */
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
 * Load the navigation tree javascript.
 *
 * @module     format_netcourse/navblock
 * @package    core
 * @copyright  2015 John Okely <john@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'theme_bootstrapbase/bootstrap', 'core/tree', 'core/log'], function ($, BS, Tree, log) {
// define(['jquery', 'core/tree', 'core/log'], function ($, Tree, log) {
    log.debug('format_netcourse/navblock AMD');
    var $courseNav = null,
        $cnavTree = null;

    // Close the other items.
    var handleExpand = function (item) {
        var $openEle = null;

        if ($cnavTree.length) {
            $openEle = $cnavTree.find('[aria-expanded="true"]');
            item.siblings('[role="group"]').collapse('show');

            $openEle.each(function () {
                var $one = $(this);
                if (!$one.is(item)) {
                    $one.attr('aria-expanded', 'false');
                    $one.siblings('[role="group"]').attr('aria-hidden', 'true');
                    $one.siblings('[role="group"]').collapse('hide');
                }
            });
        }
    };

    return {
        init: function (instanceid) {
            log.debug('format_netcourse/navblock init - instanceid: ' + instanceid);

            var navTree = new Tree('[data-instanceid="' + instanceid + '"] .block_tree');
            navTree.finishExpandingGroup = function (item) {
                handleExpand(item);
            };
            navTree.collapseGroup = function () {
                // Override to do nothing.
            };

            $courseNav = $('#instcnav');

            if ($courseNav.length) {
                $cnavTree = $courseNav.find('.block_tree');
            }
        }
    };
});
