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

require_once( '../../config.php');
require_once('myuserslib.php');
require_once($CFG->dirroot.'/local/myusers/filter/lib.php');
require_once($CFG->dirroot.'/user/lib.php');

require_login($COURSE);

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
    MyUsers::SuspendUser($suspend);

    redirect($url);
}//if_suspend

/* Unsuspend User   */
if ($unsuspend and confirm_sesskey()) {
    require_capability('moodle/user:update', $context_cat);
    MyUsers::UnsuspendUser($unsuspend);

    redirect($url);
}//if_unsuspend

/* Delete User */
if ($delete and confirm_sesskey()) {
    require_capability('moodle/user:delete', $context_cat);

    MyUsers::DeleteUser($delete,$confirm,$url);
}//if_delete

/* Create the user filter   */
$user_filter = new myusers_filtering(null,$url,null);

/* Print Header */
echo $OUTPUT->header();

/* Apply the filter */
list($extra_sql, $params) = $user_filter->get_sql_filter();

$extra_sql = str_replace('id  IN ','u.id IN ',$extra_sql);

/* Get the list of users connected with */
$lst_users      = MyUsers::GetUsersCohortCategory($context_cat->id,$USER->id,$sort,$dir,$page*$per_page,$per_page,$extra_sql,$params);
$total_users    = MyUsers::GetUsersCohortCategory($context_cat->id,$USER->id,$sort,$dir,0,0,$extra_sql,$params);
$total          = count($total_users);
$str_title      = get_string('title','local_myusers',$total);

echo $OUTPUT->heading($str_title);

/* Add the filters  */
$user_filter->display_add();
$user_filter->display_active();

flush();

if ($lst_users) {
    /* Order Columns */
    $columns = array('cohort','firstname','lastname','email','company','lastaccess');
    foreach ($columns as $column) {
        if ($column == 'cohort') {
            $string[$column] = get_string($column,'cohort');
        }else if ($column == 'company') {
            $string[$column] = get_string('company','local_myusers');
        }else if ($column == 'roles') {
            //$string[$column] = get_string('job_roles','local_myusers');
        }else {
            $string[$column] = get_user_field_name($column);
        }

        if ($sort != $column) {
            $columnicon = "";
            if ($column == "lastaccess") {
                $columndir = "DESC";
            } else {
                $columndir = "ASC";
            }
        } else {
            $columndir = $dir == "ASC" ? "DESC":"ASC";
            if ($column == "lastaccess") {
                $columnicon = ($dir == "ASC") ? "sort_desc" : "sort_asc";
            } else {
                $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
            }
            $columnicon = "<img class='iconsort' src=\"" . $OUTPUT->pix_url('t/' . $columnicon) . "\" alt=\"\" />";

        }

        $$column = "<a href=\"myusers.php?id=$cat_id&amp;sort=$column&amp;dir=$columndir\">".$string[$column]."</a>$columnicon";
    }

    $override = new stdClass();
    $override->firstname = 'firstname';
    $override->lastname = 'lastname';
    $fullnamelanguage = get_string('fullnamedisplay', '', $override);
    if (($CFG->fullnamedisplay == 'firstname lastname') or
        ($CFG->fullnamedisplay == 'firstname') or
        ($CFG->fullnamedisplay == 'language' and $fullnamelanguage == 'firstname lastname' )) {
        $fullnamedisplay = "$firstname / $lastname";
        if ($sort == "name") { // If sort has already been set to something else then ignore.
            $sort = "firstname";
        }
    } else { // ($CFG->fullnamedisplay == 'language' and $fullnamelanguage == 'lastname firstname').
        $fullnamedisplay = "$lastname / $firstname";
        if ($sort == "name") { // This should give the desired sorting based on fullnamedisplay.
            $sort = "lastname";
        }
    }

    /* There are users -- Table */
    $countries = get_string_manager()->get_list_of_countries(false);
    foreach ($lst_users as $key => $user) {
        if (isset($countries[$user->country])) {
            $lst_users[$key]->country = $countries[$user->country];
        }
    }
    if ($sort == "country") {  // Need to resort by full country name, not code
        foreach ($lst_users as $user) {
            $lst_users[$user->id] = $lst_users->country;
        }
        asort($lst_users);
        foreach ($lst_users as $key => $value) {
            $nusers[] = $lst_users[$key];
        }
        $lst_users = $nusers;
    }

    $table = new html_table();
    $table->head = array ();
    $table->colclasses = array();

    $table->head[] = $fullnamedisplay;
    $table->attributes['class'] = ' generaltable';
    $table->colclasses[] = 'leftalign';

    $table->head[] = $cohort;
    $table->colclasses[] = 'leftalign';


    $table->head[] = $email;
    $table->colclasses[] = 'leftalign';

    $table->head[] = $company;
    $table->colclasses[] = 'leftalign';

    $table->head[] = get_string('job_roles','local_myusers');
    $table->colclasses[] = 'leftalign';

    $table->head[] = $lastaccess;
    $table->colclasses[] = 'leftalign';
    $table->head[] = get_string('edit');
    $table->colclasses[] = 'centeralign';
    $table->head[] = "";
    $table->colclasses[] = 'centeralign';

    $table->id = "users";
    foreach ($lst_users as $user) {
        $buttons = array();
        $lastcolumn = '';

        if (MyUsers::CanEditUser($USER->id,$user->id,$context_cat)) {
            // delete button
            if (has_capability('moodle/user:delete', $context_cat)) {
                if (is_mnet_remote_user($user) or $user->id == $USER->id or is_siteadmin($user)) {
                    // no deleting of self, mnet accounts or admins allowed
                } else {
                    $buttons[] = html_writer::link(new moodle_url($url, array('delete'=>$user->id, 'sesskey'=>sesskey())), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'), 'alt'=>$str_delete, 'class'=>'iconsmall')), array('title'=>$str_delete));
                }
            }

            // suspend button
            if (has_capability('moodle/user:update', $context_cat)) {
                if (is_mnet_remote_user($user)) {
                    // mnet users have special access control, they can not be deleted the standard way or suspended
                    $accessctrl = 'allow';
                    if ($acl = $DB->get_record('mnet_sso_access_control', array('username'=>$user->username, 'mnet_host_id'=>$user->mnethostid))) {
                        $accessctrl = $acl->accessctrl;
                    }
                    $changeaccessto = ($accessctrl == 'deny' ? 'allow' : 'deny');
                    $buttons[] = " (<a href=\"?acl={$user->id}&amp;accessctrl=$changeaccessto&amp;sesskey=".sesskey()."\">".get_string($changeaccessto, 'mnet') . " access</a>)";

                } else {
                    if ($user->suspended) {
                        $buttons[] = html_writer::link(new moodle_url($url, array('unsuspend'=>$user->id, 'sesskey'=>sesskey())), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/show'), 'alt'=>$str_unsuspend, 'class'=>'iconsmall')), array('title'=>$str_unsuspend));
                    } else {
                        if ($user->id == $USER->id or is_siteadmin($user)) {
                            // no suspending of admins or self!
                        } else {
                            $buttons[] = html_writer::link(new moodle_url($url, array('suspend'=>$user->id, 'sesskey'=>sesskey())), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/hide'), 'alt'=>$str_suspend, 'class'=>'iconsmall')), array('title'=>$str_suspend));
                        }
                    }

                    if (login_is_lockedout($user)) {
                        $buttons[] = html_writer::link(new moodle_url($url, array('unlock'=>$user->id, 'sesskey'=>sesskey())), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/unlock'), 'alt'=>$str_unlock, 'class'=>'iconsmall')), array('title'=>$str_unlock));
                    }
                }
            }

            // edit button
            if (has_capability('moodle/user:update', $context_cat)) {
                // prevent editing of admins by non-admins
                if (is_siteadmin($USER) or !is_siteadmin($user)) {
                    $SESSION->cat = $cat_id;
                    $buttons[] = html_writer::link(new moodle_url('/local/myusers/editmyusers.php', array('id'=>$user->id, 'course'=>$site->id)), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'), 'alt'=>$str_edit, 'class'=>'iconsmall')), array('title'=>$str_edit));
                }
            }
        }//if_canEdit


        if ($user->lastaccess) {
            $strlastaccess = format_time(time() - $user->lastaccess);
        } else {
            $strlastaccess = get_string('never');
        }

        $row = array ();
        $fullname = $user->firstname . ' ' . $user->lastname;
        $url_edit = new moodle_url('/local/myusers/editmyusers.php', array('id'=>$user->id, 'course'=>$site->id));
        $row[] = '<a href="' . $url_edit . '">' . $fullname . '</a>';
        $row[] = $user->cohort;
        $row[] = $user->email;
        $row[] = '<p class="col_rol">' . $user->company . '</p>';
        $row[] = '<p class="col_rol">' . $user->roles. '</p>';

        $row[] = $strlastaccess;
        if ($user->suspended) {
            foreach ($row as $k=>$v) {
                $row[$k] = html_writer::tag('span', $v, array('class'=>'usersuspended'));
            }
        }
        $row[] = implode(' ', $buttons);
        $row[] = $lastcolumn;
        $table->data[] = $row;
    }//for_users

    $base_url = new moodle_url('/local/myusers/myusers.php',array('id' => $cat_id,'sort' => $sort,'dir' => $dir,'perpage' => $per_page));
    echo $OUTPUT->paging_bar($total, $page, $per_page, $base_url);
    echo html_writer::start_tag('div', array('class'=>'no-overflow'));
    echo html_writer::table($table);
    echo html_writer::end_tag('div');
    echo $OUTPUT->paging_bar($total, $page, $per_page, $base_url);
}//if_total_users

/* Print Footer */
echo $OUTPUT->footer();