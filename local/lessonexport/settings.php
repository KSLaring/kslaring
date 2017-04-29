<?php
// This file is part of Moodle http://moodle.org/
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
 * Global settings
 *
 * @package   local_lessonexport
 * @copyright 2017 Adam King, SHEilds eLearning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('modules', new admin_category('lessonexport', get_string('pluginname', 'local_lessonexport')));
    $page = new admin_settingpage('lessonexportpage', get_string('pluginname', 'local_lessonexport'));

    $page->add(new admin_setting_configtext('local_lessonexport/customfont',
                                            get_string('customfont', 'local_lessonexport'),
                                            get_string('customfont_desc', 'local_lessonexport'), 'helvetica', PARAM_RAW));

    $page->add(new admin_setting_configpasswordunmask('local_lessonexport/pdfUserPassword',
                                            get_string('pdfuserpassword', 'local_lessonexport'),
                                            get_string('pdfuserpassword_desc', 'local_lessonexport'), ''));

    $page->add(new admin_setting_configpasswordunmask('local_lessonexport/pdfOwnerPassword',
                                            get_string('pdfownerpassword', 'local_lessonexport'),
                                            get_string('pdfownerpassword_desc', 'local_lessonexport'), ''));

    // Footer text areas.
    $page->add(new admin_setting_confightmleditor('local_lessonexport/pdfFooterTopLeft',
                                            get_string('pdffootertopleft', 'local_lessonexport'),
                                            get_string('pdffootertopleft_desc', 'local_lessonexport'), ''));

    $page->add(new admin_setting_confightmleditor('local_lessonexport/pdfFooterTopMiddle',
                                            get_string('pdffootertopmiddle', 'local_lessonexport'),
                                            get_string('pdffootertopmiddle_desc', 'local_lessonexport'), ''));

    $page->add(new admin_setting_confightmleditor('local_lessonexport/pdfFooterTopRight',
                                            get_string('pdffootertopright', 'local_lessonexport'),
                                            get_string('pdffootertopright_desc', 'local_lessonexport'), ''));

    $page->add(new admin_setting_confightmleditor('local_lessonexport/pdfFooterBottomLeft',
                                            get_string('pdffooterbottomleft', 'local_lessonexport'),
                                            get_string('pdffooterbottomleft_desc', 'local_lessonexport'), ''));

    $page->add(new admin_setting_confightmleditor('local_lessonexport/pdfFooterBottomMiddle',
                                            get_string('pdffooterbottommiddle', 'local_lessonexport'),
                                            get_string('pdffooterbottommiddle_desc', 'local_lessonexport'), ''));

    $page->add(new admin_setting_confightmleditor('local_lessonexport/pdfFooterBottomRight',
                                            get_string('pdffooterbottomright', 'local_lessonexport'),
                                            get_string('pdffooterbottomright_desc', 'local_lessonexport'), ''));

    $page->add(new admin_setting_configcheckbox('local_lessonexport/pdfFrontCoverPageNumbers',
                                            get_string('pdffrontcoverpagenumbers', 'local_lessonexport'),
                                            get_string('pdffrontcoverpagenumbers_desc', 'local_lessonexport'), 1));

    // PDF permission settings.
    $choices = array(
        get_string('printpermission', 'local_lessonexport') => get_string('printpermission_desc', 'local_lessonexport'),
        get_string('modifypermission', 'local_lessonexport') => get_string('modifypermission_desc', 'local_lessonexport'),
        get_string('copypermission', 'local_lessonexport') => get_string('copypermission_desc', 'local_lessonexport'),
        get_string('annotatepermission', 'local_lessonexport') => get_string('annotatepermission_desc', 'local_lessonexport'),
        get_string('formfillpermission', 'local_lessonexport') => get_string('formfillpermission_desc', 'local_lessonexport'),
        get_string('extractpermission', 'local_lessonexport') => get_string('extractpermission_desc', 'local_lessonexport'),
        get_string('assemblepermission', 'local_lessonexport') => get_string('assemblepermission_desc', 'local_lessonexport'),
        get_string('highdefpermission', 'local_lessonexport') => get_string('highdefpermission_desc', 'local_lessonexport')
    );
    $defaults = array(
        // get_string('printpermission', 'local_lessonexport')     => 'enabled',   // print
        // get_string('modifypermission', 'local_lessonexport')    => 'enabled',   // modify
        // get_string('copypermission', 'local_lessonexport')      => 'enabled',   // copy
        // get_string('annotatepermission', 'local_lessonexport')  => 'enabled',   // annotate
        // get_string('formfillpermission', 'local_lessonexport')  => 'enabled',   // forms
        // get_string('extractpermission', 'local_lessonexport')   => 'enabled',   // extract
        // get_string('assemblepermission', 'local_lessonexport')  => 'enabled',   // assemble
        // get_string('highdefpermission', 'local_lessonexport')   => 'enabled'    // high-def
    );
    $page->add(new admin_setting_configmulticheckbox('local_lessonexport/pdfProtection', get_string('pdfprotection','local_lessonexport'),
                                            get_string('pdfprotection_desc', 'local_lessonexport'), $defaults, $choices));

    $page->add(new admin_setting_configcheckbox('local_lessonexport/exportstrict', get_string('exportstrict', 'local_lessonexport'),
    get_string('exportstrict_desc', 'local_lessonexport'), 0));

    $page->add(new admin_setting_configcolourpicker('local_lessonexport/coverColour', get_string('covercolour', 'local_lessonexport'),
                                            get_string('covercolour_desc', 'local_lessonexport'), '#12A053'));

    $ADMIN->add('lessonexport', $page);
}