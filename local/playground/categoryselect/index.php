<?php

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

// Set up the page.
$PAGE->set_url('/local/playground/categoryselect/index.php');
$PAGE->set_course($SITE);

$PAGE->set_pagetype('categoryselect');
$PAGE->set_pagelayout('base');
$PAGE->set_title('Categoryselect dummy');

$PAGE->requires->css('/local/playground/categoryselect/inc/style.css');

// Set up the varianbles.
global $CFG;
require_once($CFG->libdir . '/coursecatlib.php');
$categoryid = 0;
$context = (object) array(
    'catparent' => $categoryid,
    'catlistdepth' => 0,
    'categorylist' => array()
);


// Set up the data.
$coursecategies = coursecat::get($categoryid)->get_children();

foreach($coursecategies as $cat) {
    $listitem = array(
        'catid' => $cat->id,
        'catname' => $cat->name,
        'catdepth' => $cat->depth,
        'catpath' => $cat->path,
        'withchildren' => coursecat::get($cat->id)->has_children() ? ' with-children not-loaded' : null
    );
    $context->categorylist[] = (object) $listitem;
}


// Require needed JavaScript.
$PAGE->requires->js_call_amd('local_playground/categoryselect', 'init');


// Output the data.
/* @var core_renderer $OUTPUT The Moodle core renderer */
echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_playground/categoryselect_content', $context);

//echo '<pre>' . var_export($_POST, true) . '</pre>';
//echo '<pre>' . var_export($coursecategies, true) . '</pre>';

echo $OUTPUT->footer();
