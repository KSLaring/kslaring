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
//$dir = $CFG->dataroot . '/fellesdata';
// File
//$path = $dir . '/wsFSCompanies.txt';

// Process content
//if (file_exists($path)) {
    // Get content
    //$data = file($path);
    //$data = file_get_contents($path);

    //$mydata = json_decode($data);
    // Synchronization
    //foreach ($mydata as $key => $line) {
    //    echo "Line: " . $key . " - " . $line->key . " - " . $line->personalnumber . "</br>";
    //}
//}

//$managers = WS_FELLESDATA::get_managers_reporters_ks('1201','manager');

//echo "</br>" . $managers . "</br>";

global $DB;


$sql = "DROP VIEW `v_user_rpt`";
$DB->execute($sql);

$sql = "CREATE VIEW `v_user_rpt` AS 
            (
                SELECT 	c.id 			AS 'courseid',
                        c.fullname 		AS 'coursename',
                        c.format 		AS 'format',
                        c.startdate 	AS 'startdate',
                        c.visible 		AS 'visible',
                        cf_pb.value 	AS 'producedby',
                        cf_t.value 		AS 'time',
                        lo.name			AS 'location'
                FROM			{course}					c
                    -- Produced by
                    LEFT JOIN 	{course_format_options}	cf_pb  	ON 	cf_pb.courseid 	= c.id
                                                                    AND cf_pb.name 		= 'producedby'
                    -- Time
                    LEFT JOIN 	{course_format_options}	cf_t	ON 	cf_t.courseid   = c.id
                                                                    AND cf_t.name 		= 'time'
                    -- Location
                    LEFT JOIN	{course_format_options}	cf_lo	ON 	cf_lo.courseid	= c.id
                                                                    AND cf_lo.name		like '%location%'
                            
                    LEFT JOIN	{course_locations}		lo		ON	lo.id			= cf_lo.value
                ORDER by c.fullname
            )";



$DB->execute($sql);

echo "FINISH ..." ."</br>";

/* Print Footer */
echo $OUTPUT->footer();