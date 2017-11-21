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
 * Extra Profile Field Competence - Delete Competence
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 */
global $CFG,$USER,$PAGE,$SITE,$OUTPUT;

require_once('../../../../../config.php');
require_once('../competencelib.php');
require_once($CFG->libdir . '/adminlib.php');

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}
// Params
$user_id            = optional_param('id',0,PARAM_INT);
$competence_data    = optional_param('icd',0,PARAM_INT);
$competence         = optional_param('ic',0,PARAM_INT);
$confirmed          = optional_param('confirm', false, PARAM_BOOL);
$my_competence  = null;
$my_hierarchy   = null;
$confirmed      = optional_param('confirm', false, PARAM_BOOL);
$url            = new moodle_url('/user/profile/field/competence/actions/delete_competence.php',array('id' =>$user_id,'icd' => $competence_data,'ic' => $competence));
$confirm_url    = new moodle_url('/user/profile/field/competence/actions/delete_competence.php',array('id' =>$user_id,'icd' => $competence_data,'ic' => $competence,'confirm' => true));
$return_url     = new moodle_url('/user/profile/field/competence/competence.php',array('id' =>$user_id));

// Page settings
$PAGE->https_required();
$PAGE->set_context(context_user::instance($user_id));
$PAGE->set_course($SITE);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('pluginname','profilefield_competence'),$return_url);
$PAGE->navbar->add(get_string('delete_competence','profilefield_competence'));
$PAGE->set_url($url);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

// Get my competnece
    $my_competence = Competence::get_competence_data($user_id,$competence_data,$competence);
    $my_hierarchy  = $my_competence[$competence_data];


// First confirm
if (!$confirmed) {
    // Header
    echo $OUTPUT->header();

        $a = new stdClass();
        $a->company = $my_hierarchy->path;
        $a->roles   = implode(',',$my_hierarchy->roles);

        echo $OUTPUT->confirm(get_string('delete_competence_are_sure','profilefield_competence',$a),$confirm_url,$return_url);

    // Footer
    echo $OUTPUT->footer();
}else {
    Competence::delete_competence($user_id,$competence_data,$competence);

    redirect($return_url);
}//if_confirm

