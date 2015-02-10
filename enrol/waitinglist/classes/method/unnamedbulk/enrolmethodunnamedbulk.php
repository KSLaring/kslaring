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


/**
 * Waiting List Enrol Method Unnamed Bulk enrolment Plugin
 *
 * @package    enrol_waitinglist
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 onwards Justin Hunt  http://poodll.com
 */
 
namespace enrol_waitinglist\method\unnamedbulk;

class enrolmethodunnamedbulk extends \enrol_waitinglist\method\enrolmethodbase {

	const METHODTYPE='unnamedbulk';
	const TABLE='enrol_waitinglist_method';
	protected $active = false;
	
	public $course = 0;
    public $waitlist = 0;
	public $maxseats = 0;
    public $activeseats = 0;
	public $notificationtypes = 0;
	

	 /**
     *  Constructor
     */
    public function __construct()
    {
      $this->_cache = array();
    }
	
	 /**
     *  Construct instance from DB record
     */
	 public static function from_record($record){
		$wlm = new self();
		foreach(get_object_vars($record) as $propname=>$propvalue){
			$wlm->{$propname}=$propvalue;
		}
		return $wlm;
	 }
	 
	 
	 /**
     *  Exists in Couse
     */
	  public static function exists_in_course($courseid){
		global $DB;	
        $count = $DB->count_records(self::TABLE, array('courseid' => $courseid,'type'=>self::METHODTYPE));
        return $count ? true : false;
	 }

	
	//other public functions
	public function has_enrolme_link() {return false;}
	public function has_notifications() {return false;}
	public function has_settings() {return false;}
	public  function can_enrol(){return false;}
	 
	 
	 //other functions
	 public function show_enrolme_link(){return false;}
	 public  function show_notifications_settings_link(){return false;}
	 public  function show_settings(){return false;}
	 

}
