<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Fellesdata Integration - Lib
 *
 * @package         local
 * @subpackage      fellesdata
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    01/02/2016
 * @author          eFaktor     (fbv)
 *
 */


function local_fellesdata_extend_navigation(global_navigation $navigation) {
    /* Variables    */
    global $USER;

    if (isloggedin()) {
        if (is_siteadmin($USER->id) || has_capability('local/fellesdata:manage', context_system::instance())) {
            $nodeTracker = $navigation->add(get_string('menu_title','local_fellesdata'));

            // Organization Mapping
            $nodBar = $nodeTracker->add(get_string('nav_map_org','local_fellesdata'),new moodle_url('/local/fellesdata/mapping/mapping_org.php'));
            // Organization Unmap
            $nodBar = $nodeTracker->add(get_string('nav_unmap_org','local_fellesdata'),new moodle_url('/local/fellesdata/unmap/unmap.php'));
            // Unconnected KS Organizations
            $nodBar = $nodeTracker->add(get_string('nav_unconnected','local_fellesdata'),new moodle_url('/local/fellesdata/unconnected/unconnected.php'));
            // Job roles mapping
            $nodBar = $nodeTracker->add(get_string('nav_map_jr','local_fellesdata'),new moodle_url('/local/fellesdata/mapping/jobroles.php'));
            // Suspicious data
            $nodBar = $nodeTracker->add(get_string('suspicious_header','local_fellesdata'),new moodle_url('/local/fellesdata/suspicious/index.php'));
        }//if_else
    }
}

function fellesdata_cron() {
    global $CFG;
    $plugin         = null;
    $now            = time();
    $fstExecution   = null;
    $laststatus     = null;
    $nextstatus     = null;

    try {
        // library
        require_once('cron/fellesdatacron.php');
        require_once('lib/fellesdatalib.php');
        require_once('lib/suspiciouslib.php');

        // Plugin info
        $plugin = get_config('local_fellesdata');
        
        // Activate
        if ($plugin->cron_active) {
            // Check if can be trigerred
            if (FS_CRON::can_run()) {
                // First execution
                if ($plugin->lastexecution) {
                    $fstExecution = false;
                }else {
                    $fstExecution = true;
                }

                \FELLESDATA_CRON::cron($plugin,$fstExecution);

                set_config('lastexecution', $now, 'local_fellesdata');                
            }
        }
    }catch (Exception $ex) {
        throw $ex;
    }
}//fellesdata_cron