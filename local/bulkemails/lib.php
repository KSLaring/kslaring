<?php


function local_bulkemails_cron() {
$bulk_no_emails_cron=get_config('local_bulkemails','bulk_no_emails_cron');
//echo $bulk_cron_time=get_config('local_bulkemails','bulk_cron_time'); die;
global $CFG,$DB,$USER;
    $con=array('status'=>'pending');
    $res=$DB->get_records('local_bulkmail_sent_queue',$con,'','*',0,$bulk_no_emails_cron);
	 //echo"<pre>";print_r($res); die;
    foreach($res as $k=>$v){
    $user=$DB->get_record('user',array('id'=>$v->to_userid));
    $from=$DB->get_record('user',array('id'=>$v->from_userid));
   //echo"<pre>";print_r($user); die;
    $subject=$DB->get_field('local_bulkemails_messages','subject',array('id'=>$v->messageid)); 
    $messagetext=$DB->get_field('local_bulkemails_messages','message',array('id'=>$v->messageid));
    $messagehtml='';
    $attachment='';
    $attachname='';
    $replayto='veeranki.nagesh@hmail.com';
    //$update=new stdClass();
    //Cron start sending email in process

    $record = new stdClass();
    $record->id=$v->id;
    $record->status= 'process';
    $record->mail_cron_start_time =strtotime('now');
    $record->email_time_delay_next_mail=2;
    $lastinsertid = $DB->update_record('local_bulkmail_sent_queue', $record, false);
    $email_sent_status=email_to_user($user,$from,$subject,$messagetext,$messagehtml,'','',false,$replayto,'');
    //Cron End sending email in completed
    $record1 = new stdClass();
    $record1->id=$v->id;
    $record1->status= 'completed';
    $record1->mail_cron_end_time =strtotime('now');
    $record1->email_responce=$email_sent_status;
    $lastinsertid = $DB->update_record('local_bulkmail_sent_queue', $record1, false);

    }

    

}



function insert_users_sentmails_table($users=array(),$messageid=-1){

 global $SESSION,$DB,$USER;
 //echo"<pre>";print_r($users); die;
 $record=new stdClass();
 foreach($users as $k=>$v){
   $record->from_userid=$v;
   $record->to_userid=$USER->id;
   $record->email=$DB->get_field('user','email',array('id'=>$v));
   $record->messageid=$messageid;
   $record->status='pending';
   $record->createdtime=strtotime('now');
   $record->mail_cron_start_time='';
   $record->mail_cron_end_time='';
   $record->email_time_delay_next_mail='';
   $record->email_responce='';
   //echo"<pre>";print_r($record);
   $res=$DB->insert_record('local_bulkmail_sent_queue',$record);

   

  

 }

 

unset($SESSION->bulk_users);

}