<?php

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

// Set up the page.
$PAGE->set_url('/local/playground/dd.php');
$PAGE->set_course($SITE);

$PAGE->set_pagetype('course-search');
$PAGE->set_pagelayout('base');
$PAGE->set_title('Course search click dummy');

//\core\notification::info(get_string('waitdragdrop', 'block_course_tags'));
//$PAGE->requires->string_for_js('readydragdrop', 'block_course_tags');
//$PAGE->requires->js_call_amd('local_playground/dd2', 'init');

// Set up the varianbles.
$o = '';

// Set up the data.
$o .= '';

// Require needed JavaScript.
$PAGE->requires->js_call_amd('local_playground/coursesearch', 'init');

// Output the data.
echo $OUTPUT->header();

// Load the CSS file here for easier development.
echo '<style type="text/css">';
include(__DIR__ . '/inc/style.css');
echo '</style>';
echo '';

include(__DIR__ . '/inc/search_content.html');

echo $OUTPUT->footer();
