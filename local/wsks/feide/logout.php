<?php
/**
 * KS Læring Integration - Logout
 *
 * @package         local
 * @subpackage      wsks/feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/09/2015
 * @author          eFaktor     (fbv)
 */

require_once('../../../config.php');

$PAGE->set_url('/local/wsks/feide/logout.php');
$PAGE->set_context(context_system::instance());

$PAGE->set_pagetype('site-index');
$PAGE->set_pagelayout('frontpage');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);


echo $OUTPUT->header();

echo "HOLA";

echo $OUTPUT->footer();
//redirect($CFG->wwwroot);
//unset($SESSION->ksSource);

//if (isguestuser($USER)) {
//   require_logout();

//}
