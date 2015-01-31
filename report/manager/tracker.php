<?php

/**
 * Report generator - Tracker report.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator/tracker
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  12/10/2012
 * @author      eFaktor     (fbv)
 *
 */

/* Class report */
class Tracker {
    private $report_data    = array();
    private $tracker_user   = array();

    private $user_id;
    private $data_root;
    private $pdf            = null;
    private $file_name      = null;
    private $path           = null;
    private $url;

    /* PDF Variables    */
    private $fs =           10;     // font size
    private $tfs =          12;     // title font size
    private $lhdist =       12;     // line hight distance
    private $hlhdist =      6;

    function __construct($report_data,$tracker_user = null) {
        global $CFG, $USER;

        mb_internal_encoding('UTF-8');

        $this->report_data  = $report_data;
            $this->tracker_user = $tracker_user;
            $this->url = new moodle_url('/report/manager/tracker/index.php');

        $this->user_id      = $USER->id;
        $this->data_root    = $CFG->dataroot;
    }//construct

    function get_Tracker_User() {
        return $this->tracker_user;
    }

    public function get_report_data($what) {
        if(!empty($this->report_data[$what])) {
            return $this->report_data[$what];
        }else{
            return '';
        }//_if_empty
    }//get_report_data

    public function prepare_and_send_pdf($send_pdf) {
        try {
                $this->pdf = $this->create_pdf();

            if ($send_pdf) {
                if ($this->save_pdf_to_send()) {
                    $result = $this->send_email_pdf();
                    $result = $this->create_send_pdf_dialog($result);
                    return $result;
                }else {
                    $result = get_string('send_pdf_file_error_dialog_text','local_tracker') . '<br/>';
                    $result .= '<a href="'.$this->url .'">'. get_string('return_to_selection','local_tracker') .'</a>';
                    return $result;
                }            }
        }catch(Exception $ex) {
            $result = get_string('error_creating_pdf','local_tracker') . '<br/>';
            $result .= '<a href="'.$this->url .'">'. get_string('return_to_selection','local_tracker') .'</a>';
            return $result;
        }//try_create_pdf error_download_pdf

        try {
            $this->download_pdf();
        }catch (Exception $ex_1) {
            $result = get_string('error_creating_pdf','local_tracker');
            $result .= '<a href="'.$this->url .'">'. get_string('return_to_selection','local_tracker') .'</a>';
            return $result;
        }
    }

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
            $str_name = get_string('pdf_filename','local_tracker',$this->file_date());
            $this->file_name = clean_filename($str_name);
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
            $subject = get_string('pdf_email_subject','local_tracker');
            $message = get_string('pdf_email_text', 'local_tracker', $info) . "\n";

            // Make the HTML version more XHTML happy  (&amp;)
            $message_html = text_to_html(get_string('pdf_email_text', 'local_tracker',$info));
            $USER->mailformat = 0;  // Always send HTML version as well
            $attachment = $this->path . '/'. $this->file_name;
            $attach_name = $this->file_name;

            return email_to_user($USER, '', $subject, $message, $message_html, $attachment, $attach_name);
        }else {
            $result = get_string('send_pdf_file_error_dialog_text','local_tracker') . '<br/>';
            $result .= '<a href="'.$this->url .'">'. get_string('return_to_selection','local_tracker') .'</a>';
            return $result;
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
            $text = get_string('send_pdf_email_stop_dialog_text','local_tracker');
        } else if ('fileerror' === $send_ok) {
            $text = get_string('send_pdf_file_error_dialog_text','local_tracker');
        } else if (true === $send_ok) {
            $text = get_string('send_pdf_ok_dialog_text','local_tracker');
        } else {
            $text = get_string('send_pdf_error_dialog_text', 'local_tracker') . '<br/>';
            $text .= '<a href="'.$this->url .'">'. get_string('return_to_selection', 'local_tracker') .'</a>';
        }
        return $text;
    }//create_send_pdf_dialog

    private function download_pdf() {
        $str_name = get_string('pdf_filename','local_tracker',$this->file_date());
        $filename = clean_filename($str_name);
        $this->pdf->Output($filename, 'D');
    }//download_pdf

    private function create_pdf() {
        $pdf = new TRACKER_PDF('P', 'mm', 'A4');

        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(10,10,10);
        $pdf->SetLineWidth(0.2);
        $pdf->AddPage();

        /* Add Title        */
        $this->create_title($pdf);
        /* Add User Info    */
        $this->create_user_info($pdf);
        /* Add Tables       */
        $this->create_report_tables($pdf);

        return $pdf;
    }//create_pdf


    private function create_title(&$pdf) {
        $pdf->SetFont('FreeSerif', 'N', $this->fs);
        $pdf->Cell(0,10,get_string('pdf_title','local_tracker'));
        $pdf->Ln($this->lhdist);
    }//create_title

    private function create_user_info(&$pdf) {
        $h =  5;
        $w =  28;
        $lh = 8;

        $user = $this->get_Tracker_User();

        $pdf->SetFont('FreeSerif','B',$this->fs);
        $pdf->Cell($w,$h,get_string('pdf_date','local_tracker'));

        $pdf->SetFont('FreeSerif','',$this->fs);
        $pdf->Cell(0,$h,trim(userdate(time(),'%Y-%m-%d', 99, true)));
        $pdf->Ln($lh);

        $pdf->SetFont('FreeSerif','B',$this->fs);
        $pdf->Cell($w,$h,get_string('pdf_user','local_tracker'));
        $pdf->SetFont('FreeSerif','',$this->fs);
        $pdf->Cell(0,$h, $user->fullname);
        $pdf->Ln( $lh );


        $pdf->SetFont('FreeSerif','B',$this->fs);
        $pdf->Cell($w,$h,get_string('company','local_tracker'));
        $pdf->SetFont('FreeSerif', '', $this->fs);
        $pdf->Cell(0,$h,$user->company_name);
        $pdf->Ln(2*$h);
    }//create_user_info

    private function create_report_tables(&$pdf) {
        /* Tables   */
        $main_tables    = $this->get_report_data('tables');

        if ($main_tables) {
            foreach ($main_tables as $table) {
                $intro      = $table['job_role'];
                $intro_out  = $table['outcome'];
                $tracker    = $table['table'];

                $this->create_table_intro($pdf,$intro,$intro_out);
                /* Header   */
                $this->create_table_header($pdf,$tracker->head);
                /* ROWS     */
                $this->create_table_add_row($pdf,$tracker);
            }//for_tables
        }//if_main_tables

        $pdf->checkPageBreak(30);
        $not_connected = $this->get_report_data('not_connected');
        $this->create_table_intro($pdf,get_string('individual_courses','local_tracker'),'');
        /* Header   */
        $this->create_table_header($pdf,$not_connected->head);
        /* ROWS     */
        $this->create_table_add_row($pdf,$not_connected);
    }//create_report_tables

    private function create_table_intro(&$pdf,$intro,$intro_out){
        $h =  5;
        $w =  28;
        $lh = 8;

        $pdf->SetFont('FreeSerif','B',$this->tfs);
        $pdf->Cell($w,$h,$intro_out);
        $pdf->Ln($lh);

        $pdf->SetFont('FreeSerif','',$this->tfs);
        $pdf->Cell(0,$h,$intro);
        $pdf->Ln($lh);
    }//create_table_intro

    private function create_table_header(&$pdf,$header) {
        $hg = 1.5;      // horizontal gap

        $pdf->SetFont('FreeSerif','N',$this->fs);
        $pdf->SetHorizontalgap($hg);
        $pdf->SetTextColor(0,0,0);

        $pdf->SetFillColor(147,188,215);
        $pdf->SetDrawColor(147,188,215);

        $pdf->SetWidths(array(100,20,35,25));
        $pdf->SetAligns(array('L','C','C','C'));
        $pdf->SetFills(array('DF','DF','DF','DF'));
        $pdf->SetStyles(array('','','',''));

        $table_row = array($header[0],
                           $header[1],
                           $header[2],
                           $header[3]);

        $pdf->Row($table_row);

        $pdf->Ln($hg);
    }

    private function create_table_add_row(&$pdf,$table) {
        $pdf->SetFillColor(246,246,246);
        $pdf->SetDrawColor(246,246,246);

        /* Add rows */
        if ($table->data) {
        foreach ($table->data as $row){
            $table_row = array();
            foreach ($row as $cell) {
                $table_row[] = $cell;
            }//for_row

                array_pop($table_row);

                if ($pdf->checkPageBreak(30)) {
                    $this->create_table_header($pdf,$table->head);
                    $pdf->SetFillColor(246,246,246);
                    $pdf->SetDrawColor(246,246,246);
            }

            $pdf->Row($table_row);
        }//for_table_data
        $pdf->Ln($this->hlhdist);
        }//if_Data
    }//create_table_add_row

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
}//class_Report