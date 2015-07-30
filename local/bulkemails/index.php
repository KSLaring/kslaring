<?php


/**

 * *************************************************************************

 * *                  Apply	Enrol   				                      **

 * *************************************************************************

 * @copyright   emeneo.com                                                **

 * @link        emeneo.com                                                **

 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **

 * *************************************************************************

 * ************************************************************************

*/

require ('../../config.php');



require_login();

require_login();

 $page = optional_param('page', 0, PARAM_INT);
 $perpage = optional_param('perpage',2, PARAM_INT); 

$site = get_site ();

$systemcontext = get_context_instance ( CONTEXT_SYSTEM );



$PAGE->set_url ( '/local/bulkemails/index.php');

$PAGE->set_context($systemcontext);



///Grid Table Css Files Include Here by Nagesh

//$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/lib/jquery/grid/examples/resources/syntax/shCore.css'));
//$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/lib/jquery/grid/media/css/jquery.dataTables.css'));
//$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/lib/jquery/grid/examples/resources/demo.css'));
///Grid Table Js Files Include Here by Nagesh
//$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/lib/jquery/grid/media/js/jquery.dataTables.js'));
//$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/lib/jquery/grid/examples/resources/syntax/shCore.js'));
//$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/lib/jquery/grid/examples/resources/syntax/shCore.js'));
//$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/lib/jquery/grid/examples/resources/demo.js'));
//$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/bulkemails/grid.js'));

$PAGE->set_pagelayout ('frontpage' );
$str=get_string('view report', 'local_bulkemails');
$PAGE->navbar->add ($str);
$PAGE->set_title ($str);
echo $OUTPUT->header ();
global $DB,$CFG,$OUTPUT;
echo $OUTPUT->heading ($str);
$capabilities = array('local/bulkemails:create');

echo "<div align=right><h5><a href=$CFG->wwwroot/local/bulkemails/user_bulk.php>Send Bulk Emails</a></h5></div>";
/*
echo'<!--<table id="example"  class="table table-striped table-bordered" cellspacing="0" width="100%">

				<thead>

					<tr>

						
						<th>id</th>
						<th>email</th>

						<th>from</th>

						<th>to</th>

						<th>subject</th>

						<th>message</th>
						<th>status</th>
						<th>timestart</th>

						<th>timeend</th>
						<th>next_mail</th>
						<th>email_responce</th>
						

					</tr>

				</thead>



				<tfoot>

						<tr>

						
						<th>id</th>
						<th>email</th>

						<th>from</th>

						<th>to</th>

						<th>subject</th>

						<th>message</th>
						<th>status</th>
						<th>timestart</th>

						<th>timeend</th>
						<th>email_time_delay_next_mail</th>
						<th>email_responce</th>

						

					</tr>

				</tfoot>

			</table>-->';

*/
$sql="SELECT* from mdl_local_bulkmail_sent_queue limit $page,$perpage";

$sql1="SELECT* from mdl_local_bulkmail_sent_queue";
$res=$DB->get_records_sql($sql);
$res1=$DB->get_records_sql($sql1);
$totalcount=count($res1); 
//echo"<pre>";print_r($res); die;
 $table = new html_table();
$table->head = array('id', 'email', 'from','to','subject','message','status','timestart','timeend','next_mail','email_responce');

$data=array();
$i=0;

foreach($res as $k=>$v){
$email=array();
$email[]=$k;
$email[]=$v->email;
$email[]=$DB->get_field('user','username',array('id'=>$v->from_userid));
$email[]=$DB->get_field('user','username',array('id'=>$v->to_userid));

$email[]=$DB->get_field('local_bulkemails_messages','subject',array('id'=>$v->messageid));
$email[]=$DB->get_field('local_bulkemails_messages','message',array('id'=>$v->messageid));
$email[]=$v->status;
$email[]=date('d-m-Y h:i:s',$v->mail_cron_start_time);
$email[]=date('d-m-Y h:i:s',$v->mail_cron_end_time);
$email[]=$v->email_time_delay_next_mail;
$email[]=$v->email_responce;
//$v->timestart=date('d-m-y h:i:s',$v->timestart);
//echo"<pre>";print_r($email); die;
 $table->data[] = $email;
$i++;
}
$baseurl = new moodle_url('/local/bulkemails/index.php');
echo html_writer::table($table);
echo $OUTPUT->paging_bar($totalcount,$page,$perpage, $baseurl);
//echo "<pre>";print_r($data); die;

echo $OUTPUT->footer ();