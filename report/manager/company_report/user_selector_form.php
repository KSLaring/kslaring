<?php
/**
 * // This file is part of Moodle - http://moodle.org/
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
 * Company Manager - Company Report - Users Selector Form .
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/company_report/
 * @copyright   2014 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  19/03/2014
 * @author      eFaktor     (fbv)
 *
 */

require_once($CFG->libdir.'/formslib.php');

class manager_company_user_selector_form extends moodleform {
    function definition() {
        $m_form = $this->_form;

        $acount = $this->_customdata['acount'];
        $scount = $this->_customdata['scount'];
        $ausers = $this->_customdata['ausers'];
        $susers = $this->_customdata['susers'];
        $total  = $this->_customdata['total'];

        $achoices = array();
        $schoices = array();

        if (is_array($ausers)) {
            if ($total == $acount) {
                $achoices[0] = get_string('allusers', 'bulkusers', $total);
            } else {
                $a = new stdClass();
                $a->total  = $total;
                $a->count = $acount;
                $achoices[0] = get_string('allfilteredusers', 'bulkusers', $a);
            }
            $achoices = $achoices + $ausers;

            if ($acount > MAX_BULK_USERS) {
                $achoices[-1] = '...';
            }

        } else {
            $achoices[-1] = get_string('nofilteredusers', 'bulkusers', $total);
        }

        if (is_array($susers)) {
            $a = new stdClass();
            $a->total  = $total;
            $a->count = $scount;
            $schoices[0] = get_string('allselectedusers', 'bulkusers', $a);
            $schoices = $schoices + $susers;

            if ($scount > MAX_BULK_USERS) {
                $schoices[-1] = '...';
            }

        } else {
            $schoices[-1] = get_string('noselectedusers', 'bulkusers');
        }

        $m_form->addElement('header', 'users', get_string('usersinlist', 'bulkusers'));

        $objs = array();
        $objs[0] = $m_form->createElement('select', 'ausers', get_string('available', 'bulkusers'), $achoices, 'size="15" style="margin-right: 50px;"');
        $objs[0]->setMultiple(true);
        $objs[2] = $m_form->createElement('select', 'susers', get_string('selected', 'bulkusers'), $schoices, 'size="15"');
        $objs[2]->setMultiple(true);

        $grp = $m_form->addElement('group', 'usersgrp', get_string('users', 'bulkusers'), $objs, ' ', false);

        $buttons = array();
        $buttons[] = $m_form->createElement('submit', 'addsel', get_string('addsel', 'bulkusers'),'style="margin-right: 5px;"');
        $buttons[] = $m_form->createElement('submit', 'removesel', get_string('removesel', 'bulkusers'));
        $buttons[] = $m_form->createElement('submit', 'addall', get_string('addall', 'bulkusers'));
        $buttons[] = $m_form->createElement('submit', 'removeall', get_string('removeall', 'bulkusers'));
        $grp = $m_form->addElement('group', 'buttonsgrp', get_string('users', 'bulkusers'), $buttons, array(' ', '<br />'), false);

        $m_form->addElement('hidden','advanced');
        $m_form->setType('advanced',PARAM_INT);
        $m_form->setDefault('advanced',1);
    }//definition
}//manager_company_user_selector_form