<?php

global $CFG,$USER,$PAGE,$OUTPUT,$DB,$SITE;

/* Imports */
require_once("../../config.php");
require_once($CFG->dirroot.'/repository/lib.php');
require_once('./editimage_form.php');

/* Script settings */
define('IMAGE_WIDTH', 1400);
define('IMAGE_HEIGHT', 420);

$context = context_course::instance(1);
/* Page parameters */
$contextid = $context->id;
$sectionid = 1;
$id = 0;

$formdata = new stdClass();
$formdata->userid = required_param('userid', PARAM_INT);
$formdata->offset = optional_param('offset', null, PARAM_INT);
$formdata->forcerefresh = optional_param('forcerefresh', null, PARAM_INT);
$formdata->mode = optional_param('mode', null, PARAM_ALPHA);

$url = new moodle_url('/local/background_image/editimage.php', array(
    'contextid' => $contextid,
    'id' => $id,
    'offset' => $formdata->offset,
    'forcerefresh' => $formdata->forcerefresh,
    'userid' => $formdata->userid,
    'mode' => $formdata->mode));

require_login();

// Checking access
if (isguestuser($USER)) {
    require_logout();
}

$PAGE->set_url($url);
$PAGE->set_context($context);


/* Functional part. Create the form and display it, handle results, etc */
$options = array(
    'subdirs' => 0,
    'maxfiles' => 1,
    'accepted_types' => array('web_image'),
    'return_types' => FILE_INTERNAL);

$mform = new image_form(null, array(
    'contextid' => $contextid,
    'userid' => $formdata->userid,
    'sectionid' => $sectionid,
    'options' => $options));

if ($mform->is_cancelled()) {
    //Someone has hit the 'cancel' button
    redirect(new moodle_url($CFG->wwwroot . '/'));
} else if ($formdata = $mform->get_data()) { //Form has been submitted
    /* Delete old images associated with this course section id */
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'local/background_image', 'picture', $sectionid);

    if ($newfilename = $mform->get_new_filename('img_file')) {
        /* Resize the new image and save it */

        $created = time();
        $storedfile_record = array(
            'contextid' => $context->id,
            'component' => 'local_background_image',
            'filearea' => 'picture',
            'itemid' => $sectionid,
            'filepath' => '/',
            'filename' => $newfilename,
            'timecreated' => $created,
            'timemodified' => $created);

        $temp_file = $mform->save_stored_file(
            'img_file',
            $storedfile_record['contextid'],
            $storedfile_record['component'],
            $storedfile_record['filearea'],
            $storedfile_record['itemid'],
            $storedfile_record['filepath'],
            'temp.' . $storedfile_record['filename'], true);

        try {
            $returnedimg = $fs->convert_image($storedfile_record, $temp_file,
                IMAGE_WIDTH,
//                IMAGE_HEIGHT,
                null,
                true);
            $imageinfo = $returnedimg->get_imageinfo();

            $temp_file->delete();
            unset($temp_file);

            $dataobject = new stdClass();
            $dataobject->imagepath = $newfilename;
            $dataobject->imagewidth = $imageinfo['width'];
            $dataobject->imageheight = $imageinfo['height'];
            $dataobject->sectionid = 1;
            $dataobject->courseid = 1;

            $dataid = $DB->get_field('background_image', 'id', array('sectionid' => $sectionid));
            if ($dataid) {
                $dataobject->id = $dataid;
                $DB->update_record('background_image', $dataobject);
            } else {
                $DB->insert_record('background_image', $dataobject);
            }
        } catch (Exception $e) {
            if (isset($temp_file)) {
                $temp_file->delete();
                unset($temp_file);
            }
            debugging($e->getMessage());
        }
        redirect($CFG->wwwroot . '/');
    }
}

/* Draw the form */
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
