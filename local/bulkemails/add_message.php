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

 * Change password page.

 *

 * @package    core

 * @subpackage auth

 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

 */



require('../../config.php');

require_once('add_message_form.php');

require_once('lib.php');

global $SESSION;

require_login();

$id  = optional_param('id', 0, PARAM_INT);

$action = optional_param('action', 0, PARAM_ALPHANUM);

$systemcontext = context_system::instance();

$PAGE->set_url('/local/bulkemails/add_message.php', array('id'=>$id));

$PAGE->set_pagelayout('frontpage');

global $DB;

//echo"<pre>";print_r($SESSION->bulk_users); die;



$data=array('editdata'=>$editdata);

$mform = new add_message_form(NULL,$data);

if ($data =$mform->get_data()) {

    $record=new stdClass();

    $record->subject=$data->Subject;

    $record->message=$data->message['text'];

    $record->created=strtotime('now');

    $res=$DB->insert_record('local_bulkemails_messages',$record,true);

    insert_users_sentmails_table($SESSION->bulk_users,$res);

    //echo"<pre>";print_r($res); die; 

    

}

$mform->set_data();

$str = 'Manage bulkemails';

$PAGE->set_title($str);

$PAGE->set_heading($str);

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();

