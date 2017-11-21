<?php
/**
 * Users Admin - Category plugin - Main Page
 *
 * Description
 *
 * @package         local
 * @subpackage      myusers
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      27/01/2014
 * @author          eFaktor     (fbv)
 *
 */

global $PAGE,$SITE,$CFG,$USER,$COURSE,$OUTPUT;

require_once( '../../config.php');
require_once('myuserslib.php');
require_once($CFG->dirroot.'/local/myusers/filter/lib.php');
require_once($CFG->dirroot.'/user/lib.php');

/* PARAMS   */
$cat_id         = required_param('id',PARAM_INT);
$sort           = optional_param('sort', 'lastname', PARAM_ALPHANUM);
$dir            = optional_param('dir', 'ASC', PARAM_ALPHA);
$page           = optional_param('page', 0, PARAM_INT);
$per_page       = optional_param('perpage', 30, PARAM_INT);        // how many per page
$delete         = optional_param('delete', 0, PARAM_INT);
$confirm        = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
$suspend        = optional_param('suspend', 0, PARAM_INT);
$unsuspend      = optional_param('unsuspend', 0, PARAM_INT);

$site_context   = context_system::instance();
$site           = get_site();
$context_cat    = context_coursecat::instance($cat_id);

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}
require_login($COURSE);
require_capability('moodle/category:manage', $context_cat);

/* Labels   */
$str_edit       = get_string('edit');
$str_delete     = get_string('delete');
$str_suspend    = get_string('suspenduser', 'admin');
$str_unsuspend  = get_string('unsuspenduser', 'admin');
$str_unlock     = get_string('unlockaccount', 'admin');

if (empty($CFG->loginhttps)) {
    $secure_wwwroot = $CFG->wwwroot;
} else {
    $secure_wwwroot = str_replace('http:','https:',$CFG->wwwroot);
}//if_secure

/* Start Page */
$PAGE->set_context($context_cat);
$url = new moodle_url('/local/myusers/myusers.php',array('id' => $cat_id,'sort' => $sort,'dir' => $dir));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($SITE->fullname);

/* Suspend User */
if ($suspend and confirm_sesskey()) {
    require_capability('moodle/user:update', $context_cat);
    MyUsers::suspend_user($suspend);

    redirect($url);
}//if_suspend

/* Unsuspend User   */
if ($unsuspend and confirm_sesskey()) {
    require_capability('moodle/user:update', $context_cat);
    MyUsers::unsuspend_user($unsuspend);

    redirect($url);
}//if_unsuspend

/* Delete User */
if ($delete and confirm_sesskey()) {
    require_capability('moodle/user:delete', $context_cat);

    MyUsers::delete_user($delete,$confirm,$url);
}//if_delete

/* Create the user filter   */
$user_filter = new myusers_filtering(null,$url,null);

/* Print Header */
echo $OUTPUT->header();

/* Apply the filter */
list($extra_sql, $params) = $user_filter->get_sql_filter();

$extra_sql = str_replace('id  IN ','u.id IN ',$extra_sql);

/* Get the list of users connected with */
$lst_users      = MyUsers::get_users_cohort_category($context_cat->id,$USER->id,$sort,$dir,$page*$per_page,$per_page,$extra_sql,$params);
$total_users    = MyUsers::get_users_cohort_category($context_cat->id,$USER->id,$sort,$dir,0,0,$extra_sql,$params);
$total          = count($total_users);
$str_title      = get_string('title','local_myusers',$total);

echo $OUTPUT->heading($str_title);

/* Add the filters  */
$user_filter->display_add();
$user_filter->display_active();

flush();

if ($lst_users) {
    // Get Table table to display
    $out = MyUsers::display_myusers($lst_users,$context_cat,$url,$dir,$sort);


    // Display Users
    $base_url = new moodle_url('/local/myusers/myusers.php',array('id' => $cat_id,'sort' => $sort,'dir' => $dir,'perpage' => $per_page));
    echo $OUTPUT->paging_bar($total, $page, $per_page, $base_url);

    echo $out;

    echo $OUTPUT->paging_bar($total, $page, $per_page, $base_url);
}//if_total_users

/* Print Footer */
echo $OUTPUT->footer();