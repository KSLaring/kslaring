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
 * Logs the user out and sends them to the home page
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');

$PAGE->set_url('/login/logout.php');
$PAGE->set_context(context_system::instance());

$sesskey = optional_param('sesskey', '__notpresent__', PARAM_RAW); // we want not null default to prevent required sesskey warning
$login   = optional_param('loginpage', 0, PARAM_BOOL);

/* Log  */
/**
 * @updateDate  26/09/2016
 * @author      eFaktor     (fbv)
 *
 * Add LOG
 */
global $CFG;

/* Check if exists temporary directory */
$dir = $CFG->dataroot . '/login';
if (!file_exists($dir)) {
    mkdir($dir);
}

$pathFile = $dir . '/' . $USER->id . '.log';
$dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' KSLæring - Log OUT (). ' . "\n";
$dbLog .= "USER (global) : " . $USER->id . "\n";
error_log($dbLog, 3, $pathFile);
/* FIN ADD LOG (fbv) */

// can be overridden by auth plugins
if ($login) {
    $redirect = get_login_url();
} else {
    /**
     * @updateDate  12/10/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Problems with guest users
     */
    $redirect = $CFG->wwwroot.'/index.php';
}

if (!isloggedin()) {
    // no confirmation, user has already logged out
    require_logout();
    redirect($redirect);

} else if (!confirm_sesskey($sesskey)) {
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    /**
     * @updateDate  12/10/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Problems with guest users
     */
    echo $OUTPUT->confirm(get_string('logoutconfirm'), new moodle_url($PAGE->url, array('sesskey'=>sesskey())), $CFG->wwwroot.'/index.php');
    echo $OUTPUT->footer();
    die;
}

$authsequence = get_enabled_auth_plugins(); // auths, in sequence
foreach($authsequence as $authname) {
    $authplugin = get_auth_plugin($authname);
    $authplugin->logoutpage_hook();
}

require_logout();

redirect($redirect);