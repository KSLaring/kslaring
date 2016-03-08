<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

namespace enrol_waitinglist\task;

/**
 * Waiting List QueueManager
 *
 * @package    enrol_waitinglist
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 onwards Justin Hunt  http://poodll.com
 */

class waitinglisttask extends \core\task\scheduled_task {    
		
	public function get_name() {
        // Shown in admin screens
        return get_string('waitinglisttask', 'enrol_waitinglist');
    }
	
	 /**
     *  Run all the tasks
     */
	 public function execute(){
		$trace = new \text_progress_trace();
		$waitinglist = enrol_get_plugin('waitinglist');
        $waitinglist->sync($trace, null);
        $waitinglist->send_expiry_notifications($trace);
		$waitinglist->check_and_enrol($trace);

        /**
         * @updateDate  29/10/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Check if there are invoices to activate
         */
        if (enrol_get_plugin('invoice')) {
            $waitinglist->check_invoices($trace);
        }//if_enrolInvocie

        /* Check Approval   */
         $waitinglist->check_approval($trace);
	}
}
