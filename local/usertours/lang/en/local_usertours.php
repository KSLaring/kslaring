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
 * Strings for local_usertours.
 *
 * @package   local_usertours
 * @copyright 2016 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['actions'] = 'Actions';
$string['appliesto'] = 'Applies to';
$string['block'] = 'Block';
$string['block_named'] = 'Block named \'{$a}\'';
$string['bottom'] = 'Bottom';
$string['description'] = 'Description';
$string['confirmstepremovalquestion'] = 'Are you sure that you wish to remove this step?';
$string['confirmstepremovaltitle'] = 'Confirm step removal';
$string['confirmtourremovalquestion'] = 'Are you sure that you wish to remove this tour?';
$string['confirmtourremovaltitle'] = 'Confirm tour removal';
$string['content'] = 'Content';
$string['content_help'] = 'This is the content of the step.
You can enter a content in the following formats:
<dl>
    <dt>Plain text</dt>
    <dd>A plain text description</dd>
    <dt>Moodle MultiLang</dt>
    <dd>A string which makes use of the Moodle MultiLang format</dd>
    <dt>Moodle Translated string</dt>
    <dd>A value found in a standard Moodle language file in the format identifier,component</dd>
</dl>';
$string['cssselector'] = 'CSS Selector';
$string['defaultvalue'] = 'Default ({$a})';
$string['delay'] = 'Delay before showing the step';
$string['done'] = 'Done';
$string['editstep'] = 'Editing "{$a}"';
$string['enabled'] = 'Enabled';
$string['exporttour'] = 'Export tour';
$string['importtour'] = 'Import tour';
$string['left'] = 'Left';
$string['movestepdown'] = 'Move step down';
$string['movestepup'] = 'Move step up';
$string['movetourdown'] = 'Move tour down';
$string['movetourup'] = 'Move tour up';
$string['name'] = 'Name';
$string['newstep'] = 'Create step';
$string['newstep'] = 'New step';
$string['newtour'] = 'Create a new tour';
$string['next'] = 'Next';
$string['pathmatch'] = 'Apply to URL match';
$string['pathmatch_help'] = 'Tours will be displayed on any page whose URL matches this value.

You can use the % character as a wildcard to mean anything.
Some example values include:

* /my/% - to match the Dashboard
* /course/view.php?id=2 - to match a specific course
* /mod/forum/view.php% - to match the forum discussion list
* /user/profile.php% - to match the user profile page';
$string['placement'] = 'Placement';
$string['pluginname'] = 'User Tours';
$string['resettouronpage'] = 'Reset user tour on this page';
$string['right'] = 'Right';
$string['select_block'] = 'Select a block';
$string['select_targettype'] = 'Every step is associated with a part of the page which you must choose. To make this easier there are several types of target for different types of page content.
<dl>
    <dt>Block</dt>
    <dd>Display the step next to the first matching block of the type on the page</dd>
    <dt>Selector</dt>
    <dd>CSS Selectors are a powerful way which allow you to select different parts of the page based on metadata built into the page.</dd>
    <dt>Display in middle of the page</dt>
    <dd>Instead of associating the step with a specific part of the page you can have it displayed in the middle of the page.</dd>
</dl>';
$string['selector_defaulttitle'] = 'Enter a descriptive title';
$string['selectordisplayname'] = 'A CSS selector matching \'{$a}\'';
$string['skip'] = 'Skip';
$string['target'] = 'Target';
$string['target_block'] = 'Block';
$string['target_selector'] = 'Selector';
$string['target_unattached'] = 'Display in middle of page';
$string['targettype'] = 'Target type';
$string['title'] = 'Title';
$string['title_help'] = 'This is the title shown at the top of the step.
You can enter a title in the following formats:
<dl>
    <dt>Plain text</dt>
    <dd>A plain text description</dd>
    <dt>Moodle MultiLang</dt>
    <dd>A string which makes use of the Moodle MultiLang format</dd>
    <dt>Moodle Translated string</dt>
    <dd>A value found in a standard Moodle language file in the format identifier,component</dd>
</dl>';
$string['top'] = 'Top';
$string['tourconfig'] = 'Tour configuration file to import';
$string['tourlist_explanation'] = 'You can create as many tours as you like and enable them for different parts of Moodle. Only one tour can be created per page.';
$string['tours'] = 'Tours';
$string['pausetour'] = 'Pause';
$string['resumetour'] = 'Resume';
$string['endtour'] = 'End tour';
$string['orphan'] = 'Show if target not found';
$string['orphan_help'] = 'Show the step if the target could not be found on the page.';
$string['backdrop'] = 'Show with backdrop';
$string['backdrop_help'] = 'You can use a backdrop to highlight the part of the page that you are pointing to.

Note: Backdrops are not compatible with some parts of the page such as the navigation bar.
';
$string['reflex'] = 'Move on click';
$string['reflex_help'] = 'Move on to the next step when the target is clicked on.';
$string['placement_help'] = 'You can place a step either above, below, to the left of, or to the right of the target.

The best options are top, or bottom as these adjust better for mobile display.';
$string['delay_help'] = 'You can optionally choose to add a delay before the step is displayed.

This delay is in milliseconds.';
$string['selecttype'] = 'Select step type';
$string['usertours'] = 'User tours';
$string['target_selector_targetvalue'] = 'CSS Selectors';
$string['target_selector_targetvalue_help'] = 'You can use a "CSS Selector" to target almost any element on the page.

CSS Selectors are very powerful and you can easily find parts of the page by building up the selector gradually.

Mozilla provide some [very good
documentation](https://developer.mozilla.org/en/docs/Web/Guide/CSS/Getting_started/Selectors)
for selectors which may help you to build your selectors.

You will also find your browser\'s developer tools to be extremely useful in creating these selectors:

* [Google Chrome](https://developer.chrome.com/devtools#dom-and-styles)
* [Mozilla Firefox](https://developer.mozilla.org/en-US/docs/Tools/DOM_Property_Viewer)
* [Microsoft Edge](https://developer.microsoft.com/en-us/microsoft-edge/platform/documentation/f12-devtools-guide/)
* [Apple Safari](https://developer.apple.com/library/iad/documentation/AppleApplications/Conceptual/Safari_Developer_Guide/ResourcesandtheDOM/ResourcesandtheDOM.html#//apple_ref/doc/uid/TP40007874-CH3-SW1)
';
$string['sharedtourslink'] = 'Tour repository';
$string['viewtour_info'] = 'This is the \'{$a->tourname}\' tour. It applies to the path \'{$a->path}\'.';
