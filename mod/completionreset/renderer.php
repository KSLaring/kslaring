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


/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package mod_completionreset
 * @copyright COPYRIGHTNOTICE
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_completionreset_renderer extends plugin_renderer_base {

     /**
     * Returns the header for the englishcentral module
     *
     * @param lesson $englishcentral a englishcentral Object.
     * @param string $currenttab current tab that is shown.
     * @param int    $question id of the question that needs to be displayed.
     * @param string $extrapagetitle String to append to the page title.
     * @return string
     */
    public function header_reset($activityname) {
        global $CFG;

        $title = get_string('headingreset','completionreset');

		/// Header setup
        $this->page->set_title($activityname);
        $this->page->set_heading($activityname);
       // lesson_add_header_buttons($cm, $context, $extraeditbuttons, $lessonpageid);
        $output = $this->output->header();
		$output .= $this->output->heading($title,3);
        return $output;
    }

	   /**
     * Returns the header for the englishcentral module
     *
     * @param lesson $englishcentral a englishcentral Object.
     * @param string $currenttab current tab that is shown.
     * @param int    $question id of the question that needs to be displayed.
     * @param string $extrapagetitle String to append to the page title.
     * @return string
     */
    public function header_choose() {
        global $CFG;

        $title = get_string('headingchoose', 'completionreset', $this->page->course->shortname);


        // Build the buttons
       // $context = context_module::instance($cm->id);

		/// Header setup
        $this->page->set_title($this->page->course->shortname);
        $this->page->set_heading($title);
       // lesson_add_header_buttons($cm, $context, $extraeditbuttons, $lessonpageid);
        $output = $this->output->header();
		$output .= $this->output->heading($title,4);
		$output .= get_string('chooseforminstructions','completionreset');
        return $output;
    }
	
	 public function show_reset_instructions() {
		return get_string('resetinstructions','completionreset'); 
	}
	
	public function show_reset_activities($cms,$course) {
		$items='';
		foreach($cms as $cm){
			$oneactivity= html_writer::tag('img','',array('src'=>$this->output->pix_url('icon',$cm->modname))) . '&nbsp;'. $cm->name;
			$items .= html_writer::div($oneactivity,'mod_completionreset_activitylistitem');
		} 
		return html_writer::div( $items,'mod_completionreset_activitylist');
    }
	
	public function show_reset_buttons($course,$cm){
		global $CFG;
		//convert formdata to array
		$formdata = array();
		$formdata['id']=$cm->id;
		$formdata['reset']=1;
		$reset = new single_button(
			new moodle_url('/mod/completionreset/view.php',$formdata), 
			get_string('resetbuttonlabel','completionreset'), 'get');
		$cancel = new single_button(
			new moodle_url('/course/view.php',array('id'=>$course->id)), 
			get_string('cancel'), 'get');
		return html_writer::div( $this->render($reset) . '&nbsp&nbsp' .  $this->render($cancel),'mod_completionreset_actionbuttons');
	}
	
	public function show_choose_button($course){
		global $CFG;
		//convert formdata to array
		$formdata = array();
		$formdata['course']=$course->id;
		$choose = new single_button(
			new moodle_url('/mod/completionreset/choose.php',$formdata), 
			get_string('choosemenulabel','completionreset'), 'get');
		return html_writer::div( $this->render($choose),'mod_completionreset_choosebutton');
	}
	
	public function show_no_activities(){
		return html_writer::div(get_string('noactivities','completionreset'),'mod_completionreset_noactivities');
	}
	
	
	public function fetch_chooser($chosen,$unchosen){
		//select lists
		$config= get_config('completionreset');
		$listheight=$config->listheight;
		if(!$listheight){$listheight=MOD_COMPLETIONRESET_LISTSIZE;}
		 $listboxopts = array('class'=>MOD_COMPLETIONRESET_SELECT, 'size'=>$listheight,'multiple'=>true);
		 $chosenbox =	html_writer::select($chosen,MOD_COMPLETIONRESET_CHOSEN,'',false,$listboxopts);
		 $unchosenbox =	html_writer::select($unchosen,MOD_COMPLETIONRESET_UNCHOSEN,'',false,$listboxopts);

		 
		 //buttons
		 $choosebutton = html_writer::tag('button',get_string('choose','completionreset'),  
					array('type'=>'button','class'=>'mod_completionreset_button yui3-button',
					'id'=>'mod_completionreset_choosebutton','onclick'=>'M.mod_completionreset.choose()'));
		$unchoosebutton = html_writer::tag('button',get_string('unchoose','completionreset'),  
					array('type'=>'button','class'=>'mod_completionreset_button yui3-button',
					'id'=>'mod_completionreset_unchoosebutton','onclick'=>'M.mod_completionreset.unchoose()'));
		$buttonbox = html_writer::tag('div', $choosebutton . '<br/>' . $unchoosebutton, array('class'=>'mod_completionreset_buttoncontainer','id'=>'mod_completionreset_buttoncontainer'));
		 
		 //filters
		 $chosenfilter = html_writer::tag('input','',  
					array('type'=>'text','class'=>'mod_completionreset_text',
					'id'=>'mod_completionreset_chosenfilter','onkeyup'=>'M.mod_completionreset.filter_chosen()'));
		 $unchosenfilter = html_writer::tag('input','',  
					array('type'=>'text','class'=>'mod_completionreset_text',
					'id'=>'mod_completionreset_unchosenfilter','onkeyup'=>'M.mod_completionreset.filter_unchosen()'));
		
		//the field to update for form submission
		$chosenkeys = array_keys($chosen);
		$usekeys='';
		if(!empty($chosenkeys)){
			$usekeys = implode(',',$chosenkeys);
		}
		
		//choose component container
		$htmltable = new html_table();
		$htmltable->attributes = array('class'=>'generaltable mod_completionreset_choosertable');
		
		//heading row
		$htr = new html_table_row();
		$htr->cells[] = get_string('chosenlabel','completionreset');
		$htr->cells[] = '';
		$htr->cells[] = get_string('unchosenlabel','completionreset');
		$htmltable->data[]=$htr;
		
		
		//chooser components
		$listcellattributes = array('class'=>'listcontainer');
		$buttoncellattributes = array('class'=>'buttoncontainer');
		
		$ftr = new html_table_row();
		$cell = new html_table_cell($chosenbox . '<br/>' . $chosenfilter);
		$cell->attributes =$listcellattributes;
		$ftr->cells[] = $cell;
		$cell = new html_table_cell($buttonbox);
		$cell->attributes =$buttoncellattributes;
		$ftr->cells[] = $cell;
		$cell = new html_table_cell($unchosenbox . '<br/>' . $unchosenfilter);
		$cell->attributes =$listcellattributes;
		$ftr->cells[] = $cell;
		$htmltable->data[]=$ftr;
		$chooser = html_writer::table($htmltable);
		
		return $chooser;
	}
  
}//end of class
