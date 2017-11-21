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
 * Extra Profile Field Competence - Add Competence
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
 * @updateDate      27/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * New js to load the company structure
 *
 */

global $CFG, $PAGE, $SESSION, $SITE, $OUTPUT,$USER;
require_once('../../../../../config.php');
require_once('../competencelib.php');
require_once('add_competence_form.php');
require_once($CFG->libdir . '/adminlib.php');

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

// PARAMS
$user_id        = optional_param('id',0,PARAM_INT);
$my_companies   = null;

if ($user_id) {
    $SESSION->user_id = $user_id;
}else {
    $user_id = $SESSION->user_id;
}//If_user_id

$url            = new moodle_url('/user/profile/field/competence/actions/add_competence.php',array('id' =>$user_id));
$return_url     = new moodle_url('/user/profile/field/competence/competence.php',array('id' =>$user_id));

// Page settings
$PAGE->https_required();
$PAGE->set_context(context_user::instance($user_id));
$PAGE->set_course($SITE);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('pluginname','profilefield_competence'),$return_url);
$PAGE->navbar->add(get_string('add_competence','profilefield_competence'));
$PAGE->set_url($url);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

// Companies connected with the user
$my_companies = Competence::get_mycompanies($user_id);

// Form
$form = new competence_add_competence_form(null,array($user_id,$my_companies));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    // Add competence
    Competence::add_competence($data);

    unset($SESSION->user_id);
    $_POST = array();
    redirect($return_url);
}//if_else

// Header
echo $OUTPUT->header();

$form->display();

// Initialize Organization structure
Competence::init_organization_structure('level_','job_roles',$user_id);

// Footer
echo $OUTPUT->footer();