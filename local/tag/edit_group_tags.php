<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');

// Set up the page.
$pageparams = array();
admin_externalpage_setup('local_tag/edittags', '', $pageparams);

$strtitle = get_string('editgrouptags_title', 'local_tag');
$strwaitdragdrop = get_string('waitdragdrop', 'local_tag');

$PAGE->set_pagelayout('admin');
$PAGE->set_title($strtitle);

\core\notification::info($strwaitdragdrop);
$PAGE->requires->string_for_js('readydragdrop', 'local_tag');
$PAGE->requires->js_call_amd('local_tag/edit_group_tags', 'init');

$output = $PAGE->get_renderer('core');

// Prepare the page.
$editpage = new \local_tag\output\edit_group_tags_page(get_string('editgrouptags_title', 'local_tag'));

// Prepare the wrapper data with all grouped tags and add the wrapper data to the page data.
$accordiontaggroupwrapper = new \local_tag\output\accordiontaggroup_wrapper(
        get_string('editgrouptags_taggroups', 'local_tag'));
$editpage->grouptags = $accordiontaggroupwrapper->export_for_template($output);

// Prepare the source list data with the not grouped tags and add the tag data to the page data.
$tagsourcelist = new \local_tag\output\tagsourcelist(get_string('editgrouptags_tagnogroups', 'local_tag'));
$editpage->tagsourcelist = $tagsourcelist->export_for_template($output);

// Prepare the interactive page sections and add the sections data.
$tageditsection = new \local_tag\output\tageditsection();
$editpage->sections = $tageditsection->export_for_template($output);

// Output the data.
/* @var $OUTPUT \theme_bootstrapbase_core_renderer The core renderer */
echo $OUTPUT->header();

// Render the page with the template and the saved data.
echo $OUTPUT->render_from_template('local_tag/edit_group_tags_page', $editpage->export_for_template($output));

echo $OUTPUT->footer();
