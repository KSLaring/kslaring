<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 28/01/16
 * Time: 10:23
 * To change this template use File | Settings | File Templates.
 */

require( '../../config.php' );
require_once('fellesdata/wsfellesdatalib.php');

require_login();


$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/wsks/test.php');

/* Print Header */
echo $OUTPUT->header();

// Save file
$dir = $CFG->dataroot . '/fellesdata';
// File
$path = $dir . '/wsUserCompetence.txt';

// Process content
if (file_exists($path)) {
    // Get content
    $data = file($path);

    // Synchronization
    foreach ($data as $key => $line) {
        echo "Line: " . $line . "</br>";
    }
}

/* Print Footer */
echo $OUTPUT->footer();