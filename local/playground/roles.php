<?php
/**
 * Created by PhpStorm.
 * User: urshunkler
 * Date: 2016-01-27
 */

require(dirname(dirname(dirname(__FILE__))) . '/config.php');

// Set up the page.
$PAGE->set_url('/local/playground/test.php');
$PAGE->set_course($SITE);

$PAGE->set_pagetype('default');
$PAGE->set_pagelayout('base');
$PAGE->set_title('Roles');


// Set up the varianbles.
$o = '';
$courseroles = array();


// Set up the data.
$roles = get_roles_for_contextlevels(CONTEXT_COURSE);
$roleids = array_values($roles);

$allroles = get_all_roles();
foreach ($allroles as $key => $value) {
    if (in_array($key, $roleids)) {
        $courseroles[$key] = $value->shortname;
    }
}

$coursecontext = context_course::instance(4);
//$coursecontext = context_course::instance(2);
$leader = get_role_users(9, $coursecontext);
$contact = get_role_users(10, $coursecontext);


// Output the data.
echo $OUTPUT->header();

echo var_export($roles);
echo var_export($roleids);
echo dump($courseroles);
echo dump($leader);
echo dump($contact);
//echo dump($allroles);

echo $OUTPUT->footer();

/**
 * Dump variable.
 *
 * @param $c
 *
 * @return string
 */
function dump($c) {
    $o = '<pre>';
    $o .= var_export($c, true);
    $o .= '</pre>';

    return $o;
};
