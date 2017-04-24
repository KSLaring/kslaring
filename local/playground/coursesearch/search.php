<?php

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

// Set up the page.
$PAGE->set_url('/local/playground/dd.php');
$PAGE->set_course($SITE);

$PAGE->set_pagetype('course-search');
$PAGE->set_pagelayout('base');
$PAGE->set_title('Course search click dummy');

//\core\notification::info(get_string('waitdragdrop', 'block_course_tags'));.
//$PAGE->requires->string_for_js('readydragdrop', 'block_course_tags');
$PAGE->requires->css('/local/playground/coursesearch/inc/style.css');
$PAGE->requires->css('/local/playground/coursesearch/inc/bootstrap-datepicker.min.css');
//$PAGE->requires->css('/lib/jquery/ui-1.11.4/jquery-ui.min.css');

// Set up the varianbles.
$o = '';

// Set up the data.
$o .= '';

// Require needed JavaScript.
$PAGE->requires->js_call_amd('local_playground/coursesearch', 'init');

// Output the data.
echo $OUTPUT->header();

include(__DIR__ . '/inc/search_content.html');

echo $OUTPUT->footer();
