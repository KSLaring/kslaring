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
 * Report manager - Outcome report.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  24/09/2012
 * @author      eFaktor     (fbv)
 *
 */

/* Class report */
class Report {
    private $report_data  = array();
    private $page_content = array();
    private $data_root;
    private $user_id;
    private $pdf            = null;
    private $file_name      = null;
    private $path           = null;

    /* PDF Variables    */
    private $mtfs =      20;     // main title font size
    private $fs =         10;     // font size
    private $tfs =        12;     // title font size
    private $hlhdist =    6;      // half line hight distance

    function __construct($report_data) {
        global $CFG, $USER;

        mb_internal_encoding('UTF-8');

        $this->report_data = $report_data;
        $this->user_id     = $USER->id;
        $this->data_root   = $CFG->dataroot;
    }//construct

    function get_user_id(){
        return $this->user_id;
    }//get_user_id

    /**
     * @return      bool|string
     *
     * @updateDate  01/10/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Prepare the report to download and send it if it's necessary.
     */
    public function prepare_and_send_pdf() {
        try {
            $this->pdf = $this->create_pdf();
        }catch(Exception $ex) {
            $result = get_string('error_creating_pdf','report_manager');
            return $result;
        }//try_create_pdf error_download_pdf


        $report_format = $this->get_report_format();
        if( $report_format == REPORT_MANAGER_REP_FORMAT_PDF_MAIL ) {
            if ($this->save_pdf_to_send()) {
                $result = $this->send_email_pdf();
                $result = $this->create_send_pdf_dialog($result);
                return $result;
            }else {
                $result = get_string('send_pdf_file_error_dialog_text','report_manager');
                return $result;
            }
        }else {
            try {
                $this->download_pdf();
            }catch(Exception $ex) {
                $result = get_string('error_download_pdf','report_manager');
                return $result;
            }//try_download_pdf
        }//if_report_format
    }//prepare_and_send_pdf

    /**
     * @return bool
     *
     * @updateDate  02/10/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Save the report into temporal folder.
     */
    private function save_pdf_to_send() {
        global $CFG;

        try {
            $report_type        = $this->get_report_type();
            $this->file_name    = clean_filename(report::get_string($report_type . '_pdf_file_name', $this->file_date()));
            $this->path         = 'tracker_reports';
            $file_path = $CFG->dataroot . '/' . $this->path;

            if(file_exists($file_path)) {
                // delete ld reports
                $files = glob($file_path . '/' . '*.pdf' );
                foreach($files as $file) {
                    unlink( $file );
                }
            }else {
                mkdir($file_path);
            }//if_exits_file_path

            $this->pdf->Output($file_path . '/' . $this->file_name, 'F' );

            return true;
        }catch (Exception $ex) {
            return false;
        }
    }//send_pdf

    /**
     * @return bool|string
     *
     * @updateDate      02/10/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send the report by email
     */
    private function send_email_pdf () {
        global $CFG, $USER;

        $file_path = $CFG->dataroot . '/' . $this->path;
        if(file_exists($file_path . '/' . $this->file_name)) {
            $info = new stdClass();
            $info->username = fullname($USER);
            $subject = report::get_string('pdf_email_subject' );
            $message = report::get_string('pdf_email_text', $info) . "\n";

            // Make the HTML version more XHTML happy  (&amp;)
            $message_html = text_to_html(report::get_string('pdf_email_text',$info));
            $USER->mailformat = 0;  // Always send HTML version as well
            $attachment = $this->path . '/'. $this->file_name;
            $attach_name = $this->file_name;

            return email_to_user($USER, '', $subject, $message, $message_html, $attachment, $attach_name);
        }else {
            return get_string('send_pdf_file_error_dialog_text','report_manager');
        }
    }//send_email_pdf

    /**
     * @param       $send_ok
     * @return      string
     *
     * @updateDate  02/10/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Return the message to the user.
     */
    public function create_send_pdf_dialog($send_ok){
        if ('emailstop' === $send_ok) {
            $text = $this->get_string( 'send_pdf_email_stop_dialog_text' );
        } else if ('fileerror' === $send_ok) {
            $text = $this->get_string( 'send_pdf_file_error_dialog_text' );
        } else if (true === $send_ok) {
            $text = $this->get_string( 'send_pdf_ok_dialog_text' );
        } else {
            $text = $this->get_string( 'send_pdf_error_dialog_text' );
        }
        return $text;
    }//create_send_pdf_dialog


    public static function get_string( $name, $a=null ) {
        return get_string( $name, 'report_manager', $a );
    }//get_string

    public function set_report_data($name,$what) {
        $this->report_data[ $name ] = $what;
    }//set_report_data

    public function get_report_data($what) {
        if(!empty($this->report_data[$what])) {
            return $this->report_data[$what];
        }else{
            return '';
        }//_if_empty
    }//get_report_data

    public function get_report_type() {
        if(!empty($this->report_data['report_type'])) {
            return $this->report_data['report_type'];
        }else {
            return '';
        }//if_empty
    }//get_report_type

    public function get_data_root() {
        return $this->data_root;
    }//get_data_root

    public function get_page_content() {
        return $this->page_content;
    }//get_page_content

    protected function get_report_format() {
        if(!empty($this->report_data['report_format'])) {
            return $this->report_data['report_format'];
        }else {
            return '';
        }//if_Empty
    }//get_report_format

    /* *******************************  */
    /* Internal Functions               */
    /* *******************************  */

    /**
     * @return REPORT_PDF
     *
     * @updateDate  27/09/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Create a pdf
     */
    private function create_pdf() {
        $pdf = new REPORT_PDF('P', 'mm', 'A4');

        $pdf->SetMargins(10,10,10);
        $pdf->SetLineWidth(0.2);
        $pdf->AddPage();

        /* Add Title    */
        $this->create_title($pdf);
        /* Add Level    */
        $this->create_level_block($pdf);
        /* Create Tables    */
        $this->create_report_tables($pdf);
        return $pdf;
    }//create_pdf

    /**
     * @param $pdf
     *
     * @updateDate  27/09/2012
     * @author      eFaktor (fbv)
     *
     * Description
     * Add title to the pdf.
     */
    private function create_title(&$pdf) {
        $pdf->SetFont('FreeSerif', 'B', $this->fs);
        $pdf->Cell(0, 10, $this->get_report_data('report_date'));
        $pdf->Ln(5);
        $pdf->SetFont('FreeSerif', 'B', $this->tfs);
        $pdf->Cell(0, 10, $this->get_report_data('report_name'));
        $pdf->Ln(10);
        $pdf->SetFont('FreeSerif', 'N', $this->fs);
        $pdf->Cell(0, 5, $this->get_report_data('summary'));
        $pdf->Ln(10);
    }//create_title

    /**
     * create the level info block with selected units and companies
     *
     * @param object pdf reference to pdf
     */
    private function create_level_block(&$pdf) {
        $h =  5;
        $w =  0;
        $lh = 2;

        $pdf->SetFont('FreeSerif', 'B', $this->tfs);
        $pdf->Cell($w, $h, $this->get_report_data('level_1'));

        $level_data = $this->get_report_data('level_2');
        if(!empty($level_data)){
            $pdf->Ln(5);
            $pdf->SetFont('FreeSerif', 'B', $this->tfs);
            $pdf->Cell($w, $h, $level_data );
        }//empty_level_2
        $pdf->Ln($lh);
    }//create_level_block


    /**
     * @param $pdf
     *
     * @updateDate  27/09/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Create report content
     */
    private function create_report_tables(&$pdf) {
        $report_type    = $this->get_report_data('report_type');
        /* Tables   */
        $main_tables    = $this->get_report_data('tables');

        foreach ($main_tables as $table) {
            switch ($report_type) {
                case 'course':
                    /* Print tables to course report */
                    $this->print_pdf_tables_course($pdf,$table);
                    break;
                case 'outcome':
                    /* Print tables to outcome report */
                    $this->print_pdf_tables_outcome($pdf,$table);
                    break;
            }//$report_type
        }//for_main_tables
    }//create_report_tables

    /**
     * @param           $pdf
     * @param           $table
     *
     * @updateDate      27/09/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the tables to report course
     */
    private function print_pdf_tables_course(&$pdf,$table) {
        $h =  5;        // height
        $pdf->checkPageBreak(4*$h);

        /* Completed*/
        if ($table[REPORT_MANAGER_COMPLETED]) {
            $completed          = $table[REPORT_MANAGER_COMPLETED];
            $intro_completed    = $completed['intro'];
            $tables_completed   = $completed['tables'];

            $this->create_table_intro($pdf,$intro_completed);
            /* HEADER   */
            $this->create_table_header_course($pdf,$tables_completed->head);
            /* ROWS     */
            $this->create_table_add_row($pdf,$tables_completed);
        }//if_completed

        /* Progress */
        if ($table[REPORT_MANAGER_IN_PROGRESS]) {
            $progress          = $table[REPORT_MANAGER_IN_PROGRESS];
            $intro_progress    = $progress['intro'];
            $tables_progress   = $progress['tables'];

            $this->create_table_intro($pdf,$intro_progress);
            /* HEADER   */
            $this->create_table_header_course($pdf,$tables_progress->head);
            /* ROWS     */
            $this->create_table_add_row($pdf,$tables_progress);
        }//progress

        /* Before */
        if ($table[REPORT_MANAGER_COMPLETED_BEFORE]) {
            $before            = $table[REPORT_MANAGER_COMPLETED_BEFORE];
            $intro_before      = $before['intro'];
            $tables_before     = $before['tables'];

            $this->create_table_intro($pdf,$intro_before);
            /* HEADER   */
            $this->create_table_header_course($pdf,$tables_before->head);
            /* ROWS     */
            $this->create_table_add_row($pdf,$tables_before);
        }//if_before
    }//print_pdf_tables_course

    /**
     * @param           $pdf
     * @param           $table
     *
     * @updateDate      27/09/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the tables to the outcome report
     */
    private function print_pdf_tables_outcome(&$pdf,$table) {
        $h =  5;        // height
        $pdf->checkPageBreak(4*$h);

        /* Before */
        if ($table[REPORT_MANAGER_COMPLETED_BEFORE]) {
            $before            = $table[REPORT_MANAGER_COMPLETED_BEFORE];
            $intro_before      = $before['intro'];
            $tables_before     = $before['tables'];

            $this->create_table_intro($pdf,$intro_before);
            /* HEADER   */
            $this->create_table_header_outcome($pdf,$tables_before->head);
            /* ROWS     */
            $this->create_table_add_row($pdf,$tables_before);
        }//if_before

        /* Progress */
        if ($table[REPORT_MANAGER_IN_PROGRESS]) {
            $progress          = $table[REPORT_MANAGER_IN_PROGRESS];
            $intro_progress    = $progress['intro'];
            $tables_progress   = $progress['tables'];

            $this->create_table_intro($pdf,$intro_progress);
            /* HEADER   */
            $this->create_table_header_outcome($pdf,$tables_progress->head);
            /* ROWS     */
            $this->create_table_add_row($pdf,$tables_progress);
        }//if_progress

        /* Completed*/
        if ($table[REPORT_MANAGER_COMPLETED]) {
            $completed          = $table[REPORT_MANAGER_COMPLETED];
            $intro_completed    = $completed['intro'];
            $tables_completed   = $completed['tables'];

            $this->create_table_intro($pdf,$intro_completed);
            /* HEADER   */
            $this->create_table_header_outcome($pdf,$tables_completed->head);
            /* ROWS     */
            $this->create_table_add_row($pdf,$tables_completed);
        }//if_completed
    }//print_pdf_tables_outcome

    /**
     * create report tables intro line
     *
     * @param object pdf reference to pdf
     * @param intro
     */
    private function create_table_intro(&$pdf,$intro){
        $pdf->Ln(4);
        $pdf->SetFont('FreeSerif','B',$this->fs);
        $pdf->Cell(0,0,$intro);
        $pdf->Ln(6);
    }//create_table_intro

    /**
     * @param           $pdf
     * @param           $table_header
     *
     * @updateDate      27/09/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the header to the course report
     */
    private function create_table_header_course($pdf,$table_header) {
        $hg = 1;      // horizontal gap
        $report_level = $this->get_report_data('report_level');

        $pdf->SetFont('FreeSerif','N',$this->fs);
        $pdf->SetHorizontalgap($hg);
        $pdf->SetTextColor(0,0,0);

        $pdf->SetFillColor(147,188,215);
        $pdf->SetDrawColor(147,188,215);

        switch($report_level) {
            case 1;case 2:
                $pdf->SetWidths(array(60,60,60));
                $pdf->SetAligns(array('L','L','C'));
                $pdf->SetFills(array('DF','DF','DF'));
                $pdf->SetStyles(array('','',''));

                $table_row = array($table_header[0],
                                   $table_header[1],
                                   $table_header[2]);
                $pdf->Row($table_row);

                break;
            case 3:
                $pdf->SetWidths(array(55,40,40,30,16));
                $pdf->SetAligns(array('L','L','L','C','C'));
                $pdf->SetFills(array('DF','DF','DF','DF','DF'));
                $pdf->SetStyles(array('','','','',''));

                $table_row = array($table_header[0],
                                   $table_header[1],
                                   $table_header[2],
                                   $table_header[3],
                                   $table_header[4]);
                $pdf->Row($table_row);

                break;
        }//switch_report_level

        $pdf->Ln($hg);
    }//create_table_header_course

    /**
     * @param           $pdf
     * @param           $table_header
     *
     * @updateDate      27/09/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the header to outcome report
     */
    private function create_table_header_outcome(&$pdf,$table_header) {
        $hg = 1;      // horizontal gap
        $report_level = $this->get_report_data('report_level');

        $pdf->SetFont('FreeSerif','N',$this->fs);
        $pdf->SetHorizontalgap($hg);
        $pdf->SetTextColor(0,0,0);

        $pdf->SetFillColor(147,188,215);
        $pdf->SetDrawColor(147,188,215);

        switch($report_level) {
            case 1;case 2:
                $pdf->SetWidths(array(35,40,95,16));
                $pdf->SetAligns(array('L','L','L','C'));
                $pdf->SetFills(array('DF','DF','DF','DF'));
                $pdf->SetStyles(array('','','',''));

                $table_row = array($table_header[0],
                                   $table_header[1],
                                   $table_header[2],
                                   $table_header[3]);
                $pdf->Row($table_row);

                break;
            case 3:
                $pdf->SetWidths(array(30,30,60,30,20,16));
                $pdf->SetAligns(array('L','L','L','L','C','C'));
                $pdf->SetFills(array('DF','DF','DF','DF','DF','DF'));
                $pdf->SetStyles(array('','','','','',''));

                $table_row = array($table_header[0],
                                   $table_header[1],
                                   $table_header[2],
                                   $table_header[3],
                                   $table_header[4],
                                   $table_header[5]);
                $pdf->Row($table_row);

                break;
        }//switch_report_level

        $pdf->Ln($hg);
    }//create_table_header_outcome

    /**
     * @param           $pdf
     * @param           $table
     *
     * @updateDate      27/09/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a row to the table report.
     */
    private function create_table_add_row(&$pdf,$table) {
        $pdf->SetFillColor(246,246,246);
        $pdf->SetDrawColor(246,246,246);

        /* Add rows */
        foreach ($table->data as $row){
            $table_row = array();
            foreach ($row as $cell) {
                $table_row[] = $cell;
            }//for_row
            $pdf->Row($table_row);
        }//for_table_data
        $pdf->Ln($this->hlhdist);
    }//create_table_add_row

    /**
     * Download the pdf
     *
     * @param object doc PDF document
     */
    private function download_pdf() {
        $report_type = $this->get_report_type();
        $filename = clean_filename(report::get_string( $report_type . '_pdf_file_name', $this->file_date()));
        $this->pdf->Output($filename,'D');
    }//download_pdf

    /**
     * Calculate the actual date/time to be integrated into the file name
     *
     * @return string date and time
     */
    private function file_date() {
        // get actual time, format user date
        $now = time();
        $date = userdate($now, '%Y%m%d_%H%M');

        // add leading 0 to day if neccessary
        $day = userdate($now, '%d');
        if ($day < 10 && strpos($day, '0') === false) {
            $day = '0' . $day;
        }
        if (strpos($day, ' ') !== false) {
            $day = str_replace(' ', '', $day);
        }

        // replace "DD" in formated date with the modified day and return
        return str_replace('DD', $day, $date);
    }//file_date
}//report