<?php
// This file is part of ksl
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

function local_ksl_extend_settings_navigation($settingsnav, $context) {

    global $PAGE;

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('local/ksl:manage', context_system::instance())) {
        return;
    }

    // Sets the link to the navigation bar on the site.
    if ($settingnode = $settingsnav->find('root', navigation_node::TYPE_SITE_ADMIN)) {

        if ($settingnode) {
            // Index!
            $strtitle = get_string('pluginname', 'local_ksl');
            $url = new moodle_url('/local/ksl/index.php');
            $index = navigation_node::create($strtitle,
                $url,
                navigation_node::TYPE_SETTING, 'ksl_index',
                'ksl_index',
                new pix_icon('i/edit', $strtitle)
            );

            $settingnode->add_node($index);
        }
    }
}//ksl_extends_navigation