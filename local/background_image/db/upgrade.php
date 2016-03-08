<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_format_vertical_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();

	return true;
}
