<?php
/**
 * Micro Learning - Index Main Page
 *
 * @package         local
 * @subpackage      microlearning
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      12/09/2014
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../config.php');
require_once('microlearninglib.php');
require_once('index_form.php');

/* PARAMS   */
$course_id      = required_param('id',PARAM_INT);
$sort           = optional_param('sort','ASC',PARAM_ALPHA);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 10, PARAM_INT);        // how many per page
$context        = context_system::instance();
$context_course = context_course::instance($course_id);
$course         = get_course($course_id);
$url            = new moodle_url('/local/microlearning/index.php',array('id'=>$course_id,'sort' => $sort,'page' => $page, 'perpage' => $perpage));
$return_url     = new moodle_url('/course/view.php',array('id'=>$course_id));
$campaign_id    = null;
$url_users      = null;

require_capability('local/microlearning:manage',$context);
require_login($course);

$PAGE->set_url($url);
$PAGE->set_context($context_course);
$PAGE->set_pagelayout('course');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

/* Add Form */
$form = new microlearning_form(null,array($course_id));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if ($data = $form->get_data()) {
    $campaign_id = Micro_Learning::Create_MicrolearningCampaign($data);

    /* Selector Users   */
    $url_users = new moodle_url('/local/microlearning/users/users.php',array('id' => $course_id,'mode' => $data->type,'cp' => $campaign_id));
    redirect($url_users);
}//if_form

echo $OUTPUT->header();

/* Existing Campaigns   */
$total_campaings = Micro_Learning::Get_TotalCampaings_Course($course_id);
echo Micro_Learning::Get_MicrolearningCampaigns_Table($course_id,$sort,$page*$perpage,$perpage);
echo "</br>";
echo $OUTPUT->paging_bar($total_campaings, $page, $perpage, $url);

$form->display();

echo $OUTPUT->footer();

