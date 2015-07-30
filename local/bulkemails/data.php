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





global $DB,$CFG,$USER;

//echo"<pre>";print_r($USER); die;

$sql="SELECT* from mdl_local_bulkmail_sent_queue";

$res=$DB->get_records_sql($sql);

//echo"<pre>";print_r($res); die;

$data=array();
$i=0;

foreach($res as $k=>$v){
$email=new stdClass();
$email->id=$k;
$email->email=$v->email;
$email->from=$DB->get_field('user','username',array('id'=>$v->from_userid));
$email->to=$DB->get_field('user','username',array('id'=>$v->to_userid));

$email->subject=$DB->get_field('local_bulkemails_messages','subject',array('id'=>$v->messageid));
$email->message=$DB->get_field('local_bulkemails_messages','message',array('id'=>$v->messageid));
$email->status=$v->status;
$email->timestart=date('d-m-Y h:i:s',$v->mail_cron_start_time);
$email->timeend=date('d-m-Y h:i:s',$v->mail_cron_end_time);
$email->next_mail=$v->email_time_delay_next_mail;
$email->email_responce=$v->email_responce;
//$v->timestart=date('d-m-y h:i:s',$v->timestart);
//echo"<pre>";print_r($email); die;
$data[$i]=$email;
$i++;
}
//echo"<pre>";print_r($data); die;



//echo"<pre>";print_r($data); die;

echo $json=json_format_data($data);











?>



			

