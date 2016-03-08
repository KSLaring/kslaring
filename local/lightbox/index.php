<?php
/**
 * Test the YUI lightbox
 * User: Urs Hunkler
 * Date: 2013-06-26
 *
 * Test page for the YUI lightbox
 */

require_once('../../config.php');

$PAGE->set_course($SITE);

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');

$PAGE->set_url('/local/lightbox/index.php');
$PAGE->set_title('Lightbox test page');
$PAGE->set_heading('Lighbox test page heading');

$PAGE->navbar->ignore_active();
$PAGE->navbar->add('Lightbox', new moodle_url('/local/lightbox/index.php'));

$PAGE->requires->yui_module(array(
        'moodle-local_lightbox-lightbox'
    ),
    'M.local_lightbox.lightbox.init_lightbox',
    array());

echo $OUTPUT->header();

echo $OUTPUT->heading('Lightbox heading', 2, 'main', 'pageheading');

echo html_writer::link('http://www.visualfractions.com/IdentifyLines/identifylines.html',
    'Identify Lines', array('rel' => 'lightbox', 'target' => '_blank'));
echo html_writer::empty_tag('br');
echo html_writer::link('http://www.visualfractions.com/FindGrampy/findgrampy.html',
    'Find Grampy', array('rel' => 'lightbox', 'target' => '_blank'));
echo html_writer::empty_tag('br');
echo html_writer::link('http://phet.colorado.edu/en/simulation/build-a-fraction',
    'build a fraction', array('rel' => 'lightbox', 'target' => '_blank'));

echo $OUTPUT->footer();
