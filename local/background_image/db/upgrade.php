<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_background_image_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();

	return true;
}
