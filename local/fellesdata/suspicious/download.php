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
 * Fellesdata Suspicious Integration - Download file
 *
 * @package         local/fellesdata
 * @subpackage      suspicious
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    18/01/2017
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('../lib/suspiciouslib.php');
require_once($CFG->libdir . '/csvlib.class.php');

/* PARAMS */
$suspicious     = optional_param('id',0,PARAM_INT);
$csv            = optional_param('csv',0,PARAM_INT);
$url            = new moodle_url('/local/fellesdata/suspicious/download.php');
$relative_path  = null;
$args           = null;
$strMessage     = null;
$error          = NONE_ERROR;

/* Guess USer -- Logout */
if (isguestuser($USER)) {
    require_logout();
}//if_guestuser

$relative_path = get_file_argument();
//extract relative path components
$args          = explode('/', ltrim($relative_path, '/'));

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('suspicious_header','local_fellesdata'));

// Download the file
if ($csv) {
    if ($suspicious) {
        if (!suspicious::download_suspicious_file($suspicious)) {
            // Header
            echo $OUTPUT->header();
            echo $OUTPUT->notification(get_string('err_process','local_fellesdata'), 'notifysuccess');
            echo $OUTPUT->continue_button($url);
            // Footer
            echo $OUTPUT->footer();
        }
    }else {
        // Header
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('err_params','local_fellesdata'), 'notifysuccess');
        echo $OUTPUT->continue_button($CFG->wwwroot);
        // Footer
        echo $OUTPUT->footer();
    }
}else {
    if (count($args) != 2) {
        $strMessage = get_string('err_params','local_fellesdata');
    }else {
        //Check link to download the file
        suspicious::check_download_link($args,$error);

        switch ($error) {
            case NONE_ERROR:
                $out = suspicious::display_download_link($args[1]);
                
                // Header
                echo $OUTPUT->header();
                echo $out;
                // Footer
                echo $OUTPUT->footer();

                break;
            case ERR_PARAMS:
                // Header
                echo $OUTPUT->header();
                echo $OUTPUT->notification(get_string('err_params','local_fellesdata'), 'notifysuccess');
                echo $OUTPUT->continue_button($CFG->wwwroot);
                // Footer
                echo $OUTPUT->footer();
                
                break;
            case ERR_FILE:
                // Header
                echo $OUTPUT->header();
                echo $OUTPUT->notification(get_string('err_file','local_fellesdata'), 'notifysuccess');
                echo $OUTPUT->continue_button($CFG->wwwroot);
                // Footer
                echo $OUTPUT->footer();

                break;
        }//switch
    }//if_args
}//if_csv


