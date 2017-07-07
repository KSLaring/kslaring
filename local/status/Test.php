<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 02/02/16
 * Time: 12:45
 * To change this template use File | Settings | File Templates.
 */

require( '../../config.php' );
require_once('../../local/fellesdata/lib/fellesdatalib.php');
require_once('cron/statuscron.php');
require_once('lib/statuslib.php');

require_login();

/* PARAMS */
$option = optional_param('op',0,PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/status/Test.php');

/* Print Header */
echo $OUTPUT->header();

try {
    echo " TESTING FELLESDATA STATUS CRON " . "</br>";
    echo "Start ... " . "</br>";

    // Plugin info
    $plugin = get_config('local_fellesdata');

    // Call cron
    //\STATUS_CRON::test($plugin);

    $sql = " SELECT	  fs.id
                         FROM	  {fs_imp_company}	fs 
                            -- FIND REPEAT
                            JOIN  {fs_imp_company}	fs_rep 	ON  fs_rep.ORG_NIVAA 	= fs.ORG_NIVAA
                                                            AND fs_rep.org_enhet_id = fs.org_enhet_id
                                                            AND	fs_rep.imported     = 1
                         WHERE	  fs.imported = 0
                         ORDER BY fs.ORG_NIVAA,fs.org_enhet_id ";
    // Execute
    global $DB;
    $rdo = $DB->get_records_sql($sql);

    if ($rdo) {
        echo implode(',',array_keys($rdo));
    }
}catch (Exception $ex) {
    throw $ex;
}//try_catch

echo "</br> Finish ... " . "</br>";

/* Print Footer */
echo $OUTPUT->footer();