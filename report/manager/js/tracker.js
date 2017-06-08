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
 * Report Competence Manager - Java Script
 *
 * @package         report
 * @subpackage      manager/js
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    23/10/2015
 * @author          eFaktor     (fbv)
 */
YUI().use('node', function(Y) {

    Y.delegate('click', function(e) {
        var buttonID = e.currentTarget.get('id');

        /* Collapse/Expand  */
        var idNode = buttonID + '_div';
        node = Y.one('#' + idNode);
        node.toggleView();

        /* Change the image */
        var idImg = buttonID + '_img';
        imgNode = Y.one('#' + idImg);
        var src = imgNode.get('src');
        if (src.indexOf('expanded.png') != -1) {
            imgNode.set('src',src.replace('expanded.png','collapsed.png'));
        }else {
            imgNode.set('src',src.replace('collapsed.png','expanded.png'));
        }//if_else
    },document, 'button');
});
