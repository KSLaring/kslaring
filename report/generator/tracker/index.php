<?php

/**
 * Tracker - Module
 *
 * Description
 *
 * @package         local
 * @subpackage      tracker
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate      08/10/2012
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once('../trackerlib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS */
$edit           = optional_param('edit', -1, PARAM_BOOL);
$block_action   = optional_param('blockaction', '', PARAM_ALPHA);
$pdf            = optional_param('pdf', '', PARAM_ALPHA);

$url = new moodle_url('/report/generator/tracker/index.php');

$site_context = CONTEXT_SYSTEM::instance();
$PAGE->set_context($site_context);
$PAGE->set_url($url);

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->requires->js(new moodle_url('/report/generator/js/tracker.js'));

$tracker_user   = tracker_get_info_user_tracker($USER->id);
$tracker_info   = tracker_get_tracker_page_user_info($tracker_user);

$out = tracker_print_tables_tracker_info($tracker_info);

switch ($pdf) {
    case TRACKER_PDF_DOWNLOAD:
        $out = tracker_download_pdf_tracker($tracker_info,$tracker_user);
        break;
    case TRACKER_PDF_SEND:
        $out = tracker_download_pdf_tracker($tracker_info,$tracker_user,true);
        break;
    default:

        break;
}//switch_pdf

echo $OUTPUT->header();
echo $OUTPUT->heading($out);

/* Print Footer */
echo $OUTPUT->footer();