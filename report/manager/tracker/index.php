<?php

/**
 * Report Competence Manager - Tracker Module
 *
 * Description
 *
 * @package         report/manager
 * @subpackage      tracker
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    01/04/2015
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once('trackerlib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS */
$edit           = optional_param('edit', -1, PARAM_BOOL);
$block_action   = optional_param('blockaction', '', PARAM_ALPHA);
$pdf            = optional_param('pdf', '', PARAM_ALPHA);

$url = new moodle_url('/report/manager/tracker/index.php');

$site_context = context_system::instance();
$PAGE->set_context($site_context);
$PAGE->set_url($url);

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->requires->js(new moodle_url('/report/manager/js/tracker.js'));

/* Get Tracker User */
$trackerUser = TrackerManager::get_user_tracker($USER->id);

switch ($pdf) {
    case TRACKER_PDF_DOWNLOAD:
        TrackerManager::download_tracker_report($trackerUser);
        break;
    default:
        /* Print Tracker User   */
        $out = TrackerManager::print_tracker_info($trackerUser);
        break;
}//switch_pdf

echo $OUTPUT->header();
echo $OUTPUT->heading($out);

/* Print Footer */
echo $OUTPUT->footer();
