<?php
/**
 * Participants List - Export PDF File
 *
 * @package         local
 * @subpackage      participants/pdf
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    03/04/2017
 * @author          eFaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir.'/pdflib.php');
require_once($CFG->dirroot.'/lib/excellib.class.php');

class participant_export {
    protected $course;
    protected $location;
    protected $instructors;
    protected $participanttable;

    /**
     * Description
     * participant_export constructor.
     *
     * @param       Object $course
     * @param       Object $location
     * @param       array  $instructors
     * @param       array  $participants
     *
     * @creationDate    03/04/2017
     * @author          eFaktor     (fbv)
     */
    public function __construct($course,$location,$instructors,$participants) {
        // Set course
        $this->course       = $course;
        // Set location
        $this->location = $location;
        // Set instructors
        $this->instructors = $instructors;
        // Set participants list
        $this->participanttable = $participants;
    }//constructor

    /**
     * Description
     * Export to PDF file
     *
     * @return  string
     * @throws  Exception
     *
     * @creationDate    03/04/2017
     * @author          eFaktor     (fbv)
     */
    public function export() {
        try {
            // Set timeout
            @set_time_limit(300);

            //Create pdf
            $exp = new participant_pdf('L', 'mm', 'A4');
            $exp->SetAutoPageBreak(true, 5);
            $exp->SetMargins(10,10,10);
            $exp->addPage();

            // Get participants list
            $keys = array_keys($this->participanttable);
            $total = count($keys);

            // First Page
            $index = 0;
            // Create title
            $this->create_title($exp);

            // Course info
            $this->add_course_info($exp);

            // Add header
            $this->add_header_table($exp);
            // Add content
            $this->add_content_table($exp,$index,23,$keys);
            $index += 23;

            // Rest of the pages
            while ($index < $total) {
                $exp->addPage();
                $exp->Ln(4);
                // Add header
                $this->add_header_table($exp);
                // Add content
                $this->add_content_table($exp,$index,$index+37,$keys);
                $index += 37;
            }//while_index

            // Download pdf
            return $this->end_export($exp);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//export_pdf

    /**
     * Description
     * Add title to the report
     *
     * @param           $pdf
     *
     * @creationDate    03/04/2017
     * @author          eFaktor     (fbv)
     */
    private function create_title(&$pdf) {
        $title = get_string('pluginname','local_participants');
        $pdf->writeHTML('<h2>' . $title . '</h2>');
        $pdf->Ln(4);
    }//create_title

    /**
     * Description
     * Add course info
     *
     * @param           $pdf
     *
     * @throws          Exception
     *
     * @creationDate    03/04/2017
     * @author          eFaktor     (fbv)
     */
    private function add_course_info(&$pdf) {
        // Variables
        $date        = null;
        $instructors = array();

        try {
            // Get instructors
            foreach ($this->instructors as $key => $info) {
                $instructors[$key] = $info;
            }

            // Set font
            $pdf->SetFont('FreeSerif', '', 12);

            // Course name
            $header  = '<table style="width:25%;margin-bottom:0mm;">';
                // Course
                $header .= '<tr style="background-color: #f2f2f2;font-weight: bold;margin: 20mm" colspan="2">';
                    $header .= '<td>' . strtoupper(get_string('course'))   . '</td>';
                $header .= '</tr>';
                $header .= '<tr style="background-color: white" colspan="2">';
                    $header .= '<td>' . $this->course->fullname  . '</td>';
                $header .= '</tr>';
                // Date
                $date = userdate($this->course->startdate,'%d.%m.%Y', 99, false);
                $header .= '<tr style="background-color: #f2f2f2;font-weight: bold;" colspan="2">';
                    $header .= '<td>' . strtoupper(get_string('date'))   . '</td>';
                $header .= '</tr>';
                $header .= '<tr style="background-color: white" colspan="2">';
                    $header .= '<td>' . $date  . '</td>';
                $header .= '</tr>';
                // Location
                $header .= '<tr style="background-color: #f2f2f2;font-weight: bold;" colspan="2">';
                    $header .= '<td>' . strtoupper(get_string('header_lo','local_participants'))   . '</td>';
                $header .= '</tr>';
                $header .= '<tr style="background-color: white" colspan="2">';
                    $header .= '<td>' . $this->location->name  . '</td>';
                $header .= '</tr>';
                // Instructors
                $header .= '<tr style="background-color: #f2f2f2;font-weight: bold;" colspan="2">';
                $header .= '<td>' . strtoupper(get_string('str_instructors','local_participants'))   . '</td>';
                $header .= '</tr>';
                $header .= '<tr style="background-color: white" colspan="2">';
                    if ($instructors) {
                        $header .= '<td>' . implode(',',$instructors) . '</td>';
                    }else {
                        $header .= '<td> - </td>';
                    }
                $header .= '</tr>';
            $header .= '</table>';

            $pdf->writeHTML($header);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_course_info

    /**
     * Description
     * Add header to the table
     *
     * @param           $pdf
     * @throws          Exception
     *
     * @creationDate    04/04/2017
     * @author          eFaktor     (fbv)
     */
    private function add_header_table(&$pdf) {
        // Variables
        $strFirstname   = null;
        $strLastname    = null;
        $strMail        = null;
        $strcompletion  = null;
        $strMuni        = null;
        $strSector      = null;
        $strWorkplace   = null;
        $header         = null;

        try {
            // Set font
            $pdf->SetFont('FreeSerif', 'B', 14);

            // Set headers
            $strFirstname   = get_string('firstname');
            $strLastname    = get_string('lastname');
            $strMail        = get_string('email','local_participants');
            $strcompletion  = get_string('header_completed','local_participants');
            $strMuni        = get_string('header_mu','local_participants');
            $strSector      = get_string('header_se','local_participants');
            $strWorkplace   = get_string('header_wk','local_participants');

            // Create header
            $header  = '<table style="width:100%;margin-bottom:0mm;margin-bottom:0mm;">';
                $header .= '<tr style="background-color: #f2f2f2;">';
                    $header .= '<th>' . $strFirstname   . '</th>';
                    $header .= '<th>' . $strLastname    . '</th>';
                    $header .= '<th>' . $strMail        . '</th>';
                    $header .= '<th>' . $strMuni        . '</th>';
                    $header .= '<th>' . $strSector      . '</th>';
                    $header .= '<th>' . $strWorkplace   . '</th>';
                    $header .= '<th>' . $strcompletion  . '</th>';
                $header .= '</tr>';
            $header .= '</table>';

            $pdf->writeHTML($header);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_table

    /**
     * Description
     * Add content of the table
     *
     * @param       object  $pdf
     * @param       integer $start
     * @param       integer $end
     * @param       array   $keys
     *
     * @throws              Exception
     *
     * @creationDate    04/04/2017
     * @author          eFaktor     (fbv)
     */
    private function add_content_table(&$pdf,$start,$end,$keys) {
        // Variables
        $content        = null;
        $participants   = null;
        $info           = null;

        try {
            // Set font
            $pdf->SetFont('FreeSerif', '', 10);

            // Get participants list
            $participants = $this->participanttable;
            // Add Participants
            if ($participants) {
                $content = '<table style="width:100%;margin:0mm">';
                    // Add content table
                    for($i=$start;$i<$end;$i++) {
                        if (isset($keys[$i])) {
                            $info = $participants[$keys[$i]];

                            $content .= '<tr>';
                            // Firstname
                            $content .= '<td style="border-bottom-color:#f2f2f2;">';
                            $content .= $info->firstname;
                            $content .= '</td>';
                            // Lastname
                            $content .= '<td style="border-bottom-color:#f2f2f2;">';
                            $content .= $info->lastname;
                            $content .= '</td>';
                            // eMail
                            $content .= '<td style="border-bottom-color:#f2f2f2;">';
                            $content .= $info->email;
                            $content .= '</td>';
                            // Municipality
                            $content .= '<td style="border-bottom-color:#f2f2f2;">';
                            $content .= ($info->municipality ? $info->municipality: ' ');
                            $content .= '</td>';
                            // Sector
                            $content .= '<td style="border-bottom-color:#f2f2f2;">';
                            $content .= ($info->sector ? $info->sector: ' ');
                            $content .= '</td>';
                            // workplace
                            $content .= '<td style="border-bottom-color:#f2f2f2;">';
                            $content .= ($info->workplace ? $info->workplace: ' ');
                            $content .= '</td>';
                            // Time completed
                            $content .= '<td style="border-bottom-color:#f2f2f2;">';
                            $content .= ($info->timecompleted ? userdate($info->timecompleted ,'%d.%m.%Y', 99, false): ' ');
                            $content .= '</td>';
                            $content .= '</tr>';
                        }
                    }//for_participants

                // End table
                $content .= '</table>';
                $pdf->writeHTML($content);
            }//if_participants_list
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_content_table

    /**
     * Description
     * Download the PDF file
     *
     * @param       $export
     *
     * @return      string
     * @throws      Exception
     *
     * @creationDate    04/04/2017
     * @author          eFaktor     (fbv)
     */
    private function end_export($export) {
        global $CFG;
        $time = null;

        try {
            // localtime
            $time = time();
            $time = userdate($time ,'%d.%m.%Y', 99, false);
            // file name
            $filename = $CFG->dataroot.'/participants/participants_' . $this->course->fullname . "_" . $time . '.pdf';

            $export->Output($filename, 'D');

            return $filename;
        }catch (Exception $ex) {
            throw $ex;
        }
    }//end_export
}//participant_export

class participant_pdf extends pdf {
    private $fs = 10;
    private $widths;
    private $aligns;
    private $fills;
    private $styles;
    private $horizontalgap;


    public function Header()
    {

    }

    public function Footer()
    {
        //Go to 1.5 cm from bottom
        $this->SetY( -15 );
        //Select FreeSerif normal 8
        $this->SetFont( 'FreeSerif' , '', $this->fs );
        //Print left aligned page number
        $number = get_string('page_number','local_participants') . $this->PageNo();
        $this->Cell( 0, 10, $number, 0, 0, 'L' );
    }

    public function Output($name='doc.pdf', $dest='I') {
        //Output PDF to some destination
        //Finish document if necessary
        if ($this->state < 3) {
            $this->Close_doc();
        }
        //Normalize parameters
        if (is_bool($dest)) {
            $dest = $dest ? 'D' : 'F';
        }
        $dest = strtoupper($dest);
        if ($dest[0] != 'F') {
            $name = preg_replace('/[\s]+/', '_', $name);
            $name = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $name);
        }
        if ($this->sign) {
            // *** apply digital signature to the document ***
            // get the document content
            $pdfdoc = $this->getBuffer();
            // remove last newline
            $pdfdoc = substr($pdfdoc, 0, -1);
            // remove filler space
            $byterange_string_len = strlen(TCPDF_STATIC::$byterange_string);
            // define the ByteRange
            $byte_range = array();
            $byte_range[0] = 0;
            $byte_range[1] = strpos($pdfdoc, TCPDF_STATIC::$byterange_string) + $byterange_string_len + 10;
            $byte_range[2] = $byte_range[1] + $this->signature_max_length + 2;
            $byte_range[3] = strlen($pdfdoc) - $byte_range[2];
            $pdfdoc = substr($pdfdoc, 0, $byte_range[1]).substr($pdfdoc, $byte_range[2]);
            // replace the ByteRange
            $byterange = sprintf('/ByteRange[0 %u %u %u]', $byte_range[1], $byte_range[2], $byte_range[3]);
            $byterange .= str_repeat(' ', ($byterange_string_len - strlen($byterange)));
            $pdfdoc = str_replace(TCPDF_STATIC::$byterange_string, $byterange, $pdfdoc);
            // write the document to a temporary folder
            $tempdoc = TCPDF_STATIC::getObjFilename('doc', $this->file_id);
            $f = TCPDF_STATIC::fopenLocal($tempdoc, 'wb');
            if (!$f) {
                $this->Error('Unable to create temporary file: '.$tempdoc);
            }
            $pdfdoc_length = strlen($pdfdoc);
            fwrite($f, $pdfdoc, $pdfdoc_length);
            fclose($f);
            // get digital signature via openssl library
            $tempsign = TCPDF_STATIC::getObjFilename('sig', $this->file_id);
            if (empty($this->signature_data['extracerts'])) {
                openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED);
            } else {
                openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED, $this->signature_data['extracerts']);
            }
            // read signature
            $signature = file_get_contents($tempsign);
            // extract signature
            $signature = substr($signature, $pdfdoc_length);
            $signature = substr($signature, (strpos($signature, "%%EOF\n\n------") + 13));
            $tmparr = explode("\n\n", $signature);
            $signature = $tmparr[1];
            // decode signature
            $signature = base64_decode(trim($signature));
            // add TSA timestamp to signature
            $signature = $this->applyTSA($signature);
            // convert signature to hex
            $signature = current(unpack('H*', $signature));
            $signature = str_pad($signature, $this->signature_max_length, '0');
            // Add signature to the document
            $this->buffer = substr($pdfdoc, 0, $byte_range[1]).'<'.$signature.'>'.substr($pdfdoc, $byte_range[1]);
            $this->bufferlen = strlen($this->buffer);
        }
        switch($dest) {
            case 'I': {
                // Send PDF to the standard output
                if (ob_get_contents()) {
                    $this->Error('Some data has already been output, can\'t send PDF file');
                }
                if (php_sapi_name() != 'cli') {
                    // send output to a browser
                    header('Content-Type: application/pdf');
                    if (headers_sent()) {
                        $this->Error('Some data has already been output to browser, can\'t send PDF file');
                    }
                    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
                    //header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
                    header('Pragma: public');
                    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                    header('Content-Disposition: inline; filename="'.basename($name).'"');
                    TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
                } else {
                    echo $this->getBuffer();
                }
                break;
            }
            case 'D': {
                // download PDF as file
                if (ob_get_contents()) {
                    $this->Error('Some data has already been output, can\'t send PDF file');
                }
                header('Content-Description: File Transfer');
                if (headers_sent()) {
                    $this->Error('Some data has already been output to browser, can\'t send PDF file');
                }
                header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
                //header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
                header('Pragma: public');
                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                // force download dialog
                if (strpos(php_sapi_name(), 'cgi') === false) {
                    header('Content-Type: application/force-download');
                    header('Content-Type: application/octet-stream', false);
                    header('Content-Type: application/download', false);
                    header('Content-Type: application/pdf', false);
                } else {
                    header('Content-Type: application/pdf');
                }
                // use the Content-Disposition header to supply a recommended filename
                header('Content-Disposition: attachment; filename="'.basename($name).'"');
                header('Content-Transfer-Encoding: binary');
                TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
                break;
            }
            case 'F':
            case 'FI':
            case 'FD': {
                // save PDF to a local file
                $f = TCPDF_STATIC::fopenLocal($name, 'wb');
                if (!$f) {
                    $this->Error('Unable to create output file: '.$name);
                }
                fwrite($f, $this->getBuffer(), $this->bufferlen);
                fclose($f);
                if ($dest == 'FI') {
                    // send headers to browser
                    header('Content-Type: application/pdf');
                    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
                    //header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
                    header('Pragma: public');
                    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                    header('Content-Disposition: inline; filename="'.basename($name).'"');
                    TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
                } elseif ($dest == 'FD') {
                    // send headers to browser
                    if (ob_get_contents()) {
                        $this->Error('Some data has already been output, can\'t send PDF file');
                    }
                    header('Content-Description: File Transfer');
                    if (headers_sent()) {
                        $this->Error('Some data has already been output to browser, can\'t send PDF file');
                    }
                    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
                    header('Pragma: public');
                    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                    // force download dialog
                    if (strpos(php_sapi_name(), 'cgi') === false) {
                        header('Content-Type: application/force-download');
                        header('Content-Type: application/octet-stream', false);
                        header('Content-Type: application/download', false);
                        header('Content-Type: application/pdf', false);
                    } else {
                        header('Content-Type: application/pdf');
                    }
                    // use the Content-Disposition header to supply a recommended filename
                    header('Content-Disposition: attachment; filename="'.basename($name).'"');
                    header('Content-Transfer-Encoding: binary');
                    TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
                }
                break;
            }
            case 'E': {
                // return PDF as base64 mime multi-part email attachment (RFC 2045)
                $retval = 'Content-Type: application/pdf;'."\r\n";
                $retval .= ' name="'.$name.'"'."\r\n";
                $retval .= 'Content-Transfer-Encoding: base64'."\r\n";
                $retval .= 'Content-Disposition: attachment;'."\r\n";
                $retval .= ' filename="'.$name.'"'."\r\n\r\n";
                $retval .= chunk_split(base64_encode($this->getBuffer()), 76, "\r\n");
                return $retval;
            }
            case 'S': {
                // returns PDF as a string
                return $this->getBuffer();
            }
            default: {
                $this->Error('Incorrect output destination: '.$dest);
            }
        }
        return '';
    }

    public function Close_doc() {
        if ($this->state == 3) {
            return;
        }
        if ($this->page == 0) {
            $this->AddPage();
        }
        $this->endLayer();
        if ($this->tcpdflink) {
            // save current graphic settings
            $gvars = $this->getGraphicVars();
            $this->setEqualColumns();
            $this->lastpage(true);
            $this->SetAutoPageBreak(false);
            $this->x = 0;
            $this->y = $this->h - (1 / $this->k);
            $this->lMargin = 0;
            $this->_outSaveGraphicsState();
            $font = defined('PDF_FONT_NAME_MAIN')?PDF_FONT_NAME_MAIN:'helvetica';
            $this->SetFont($font, '', 1);
            $this->setTextRenderingMode(0, false, false);
            $msg = "\x50\x6f\x77\x65\x72\x65\x64\x20\x62\x79\x20\x54\x43\x50\x44\x46\x20\x28\x77\x77\x77\x2e\x74\x63\x70\x64\x66\x2e\x6f\x72\x67\x29";
            $lnk = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x74\x63\x70\x64\x66\x2e\x6f\x72\x67";
            //$this->Cell(0, 0, $msg, 0, 0, 'L', 0, $lnk, 0, false, 'D', 'B');
            $this->_outRestoreGraphicsState();
            // restore graphic settings
            $this->setGraphicVars($gvars);
        }
        // close page
        $this->endPage();
        // close document
        $this->_enddoc();
        // unset all class variables (except critical ones)
        $this->_destroy(false);
    }
}//participant_pdf


