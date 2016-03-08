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
 * Form for editing frikomport navigation instances.
 *
 * @since     Moodle 2.0
 * @package   block_frikomport
 * @copyright 2014 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for setting navigation instances.
 *
 * @package   block_frikomport
 * @copyright 2014 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_frikomport_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $yesnooptions = array('yes' => get_string('yes'), 'no' => get_string('no'));

        $mform->addElement('select', 'config_enabledock', get_string('enabledock', $this->block->blockname), $yesnooptions);
        if (empty($this->block->config->enabledock) || $this->block->config->enabledock == 'yes') {
            $mform->getElement('config_enabledock')->setSelected('yes');
        } else {
            $mform->getElement('config_enabledock')->setSelected('no');
        }
    }
}
