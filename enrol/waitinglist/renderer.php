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


defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot.'/mod/tquiz/forms.php');
//require_once($CFG->dirroot.'/mod/tquiz/locallib.php');

/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package enrol_waitinglist
 * @copyright COPYRIGHTNOTICE
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_waitinglist_renderer extends plugin_renderer_base {

	
    
    /**
     * Return HTML to display limited header
     */
    public function header(){
      	return $this->output->header();
      }
	
	public function render_reporttitle_html($course,$username) {
		$ret = $this->output->heading(format_string($course->fullname),2);
		$ret .= $this->output->heading(get_string('reporttitle','enrol_waitinglist',$username),3);
		return $ret;
	}

	public function render_empty_table_html($tabletitle) {
		global $CFG;
		return $this->output->heading(get_string('nodataavailable','enrol_waitinglist'),3);
	}
	
	public function render_exportbuttons_html($courseid,$reportname){
		global $USER;
		//convert formdata to array
		$formdata = array();
		$formdata['id']=$courseid;
		$formdata['report']=$reportname;
		
		
		//inline print
		/*
		$formdata['format']='print';
		$print = new single_button(
			new moodle_url('/enrol/waitinglist/' . $reportname . '.php',$formdata),
			get_string('exportprint','enrol_waitinglist'), 'get');
		*/
		
		//popup print
		$formdata['format']='print';
		$formdata['sesskey']=$USER->sesskey;
		$link = new moodle_url('/enrol/waitinglist/' . $reportname . '.php',$formdata);
		$popupparams = array('height'=>800,'width'=>1050);
		$popupaction = new popup_action('click', $link,'popup',$popupparams);
		$button = html_writer::tag('button',get_string('exportprint','enrol_waitinglist'));
		$printpopup = $this->output->action_link($link,$button  , 
			$popupaction, array('class'=>'enrol_waitinglist_actionbutton'));
		
		
		//CSV export
		$formdata['format']='csv';
		$excel = new single_button(
			new moodle_url('/enrol/waitinglist/' . $reportname . '.php',$formdata), 
			get_string('exportexcel','enrol_waitinglist'), 'get');

		return html_writer::div( $this->render($excel)  . $printpopup ,'enrol_waitinglist_actionbuttons');
	}
	
	public function render_continuebuttons_html($courseid){
		$backtocourse = new single_button(
			new moodle_url('/course/view.php',array('id'=>$courseid)), 
			get_string('backtocourse','enrol_waitinglist'), 'get');
		/*
		$selectanother = new single_button(
			new moodle_url('/enrol/waitinglist/index.php',array('id'=>$course->id)), 
			get_string('selectanother','enrol_waitinglist'), 'get');
			*/
			
		return html_writer::div($this->render($backtocourse) ,'enrol_waitinglist_listbuttons');
	}
	
	public function render_table_csv($tabletitle, $reportname, $head, $rows) {

        // Use the tabletitle as the file name. Clean it and change any non-filename characters to '_'.
        $name = clean_param($tabletitle, PARAM_FILE);
        $name = preg_replace("/[^A-Z0-9]+/i", "_", trim($name));
		$quote = '"';
		$delim= ",";//"\t";
		$newline = "\r\n";

		header("Content-Disposition: attachment; filename=$name.csv");
		header("Content-Type: text/comma-separated-values");

		//echo header
		$heading="";	
		foreach($head as $headfield){
			$heading .= $quote . $headfield . $quote . $delim ;
		}
		echo $heading. $newline;
		
		//echo data rows
        foreach ($rows as $row) {
			$datarow = "";
			foreach($row as $key=>$value){
				$datarow .= $quote . $value . $quote . $delim ;
			}
			 echo $datarow . $newline;
		}
        exit();
	}


	public function render_table_html($tabletitle,$reportname,$headrow,$lastrow,$rows) {
		global $CFG;
		if(empty($rows)){
			return $this->render_empty_table_html($tabletitle);
		}
		
		//set up our table and head attributes
		$tableattributes = array('class'=>'generaltable enrol_waitinglist_table');
		$headrow_attributes = array('class'=>'enrol_waitinglist_headrow');
		$lastrow_attributes = array('class'=>'enrol_waitinglist_lastrow');
		
		$htmltable = new html_table();
		$htmltable->attributes = $tableattributes;
		
		//headrow
		$htr = new html_table_row();
		$htr->attributes = $headrow_attributes;
		foreach($headrow as $headcell){
			$htr->cells[]=new html_table_cell($headcell);
		}
		$htmltable->data[]=$htr;
		
		//datarows
		foreach($rows as $row){
			$htr = new html_table_row();
			//set up descrption cell
			$cells = array();
			foreach($row as $key=>$value){
				$cell = new html_table_cell($value);
				$cell->attributes= array('class'=>'enrol_waitinglist_cell_' . $reportname . '_' . $key);
				$htr->cells[] = $cell;
			}

			$htmltable->data[]=$htr;
		}
		
		//lastrow
		$htr = new html_table_row();
		$htr->attributes = $lastrow_attributes;
		foreach($lastrow as $acell){
			$htr->cells[]=new html_table_cell($acell);
		}
		$htmltable->data[]=$htr;
		
		
		$html = $this->output->heading($tabletitle, 4);
		$html .= html_writer::table($htmltable);
		return $html;
		
	}
	
	function render_report_footer(){
		//get current day, month and year for current user
		$date = usergetdate(time());
		list($min,$h,$d, $mon, $y) = array($date['minutes'],$date['hours'],$date['mday'], $date['mon'], $date['year']);
		//Print formatted date in user time
		$datestring = userdate(make_timestamp($y,$mon,$d,$h,$min));
		$fulltext = get_string('printdate','enrol_waitinglist',$datestring);
		return html_writer::div($fulltext,'enrol_waitinglist_printdate');
		
	}
	
	function show_reports_options($courseid,$reportname){
		// print's a popup link to your custom page
		//$link = new moodle_url('/enrol/waitinglist/' . $reportname . '.php',array('id'=>$courseid));
		//$ret =  html_writer::link($link, get_string('returntoreports','enrol_waitinglist'));
		$ret = $this->render_exportbuttons_html($courseid,$reportname);
		return $ret;
	}

}


