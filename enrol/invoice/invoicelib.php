<?php
/**
 *  Invoices Enrolment Method - library
 *
 * @package         enrol
 * @subpackage      invoice
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      29/09/2014
 * @author          eFaktor     (fbv)
 *
 */

define('ENROL_COMPANY_NO_DEMANDED',3);

class Invoices {

    /**
     * @param           $levelTwo
     * @param           $levelThree
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    14/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get invoice data connected with the company
     */
    public static function GetInvoiceData($levelTwo,$levelThree) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $invoice    = null;

        try {
            /* First Level Three    */
            $params = array();
            $params['id']               = $levelThree;
            $params['hierarchylevel']   = 3;

            /* Execute */
            $rdo = $DB->get_record('report_gen_companydata',$params,'id,tjeneste,ansvar');
            if ($rdo) {
                if ($rdo->tjeneste && $rdo->ansvar) {
                    $invoice = $rdo;
                }
            }//if_rdo

            if (!$invoice) {
                /* Level Two */
                $params['id']               = $levelTwo;
                $params['hierarchylevel']   = 2;

                /* Execute */
                $rdo = $DB->get_record('report_gen_companydata',$params,'id,tjeneste,ansvar');
                if ($rdo) {
                    if (!empty($rdo->tjeneste) && !empty($rdo->ansvar)) {
                        $invoice = $rdo;
                    }
                }//if_two
            }
            
            return $invoice;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInvoiceData
    
    /**
     * @param           $form
     *
     * @throws          Exception
     *
     * @creationDate    28/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the elements to the form
     */
    public static function AddElements_ToForm(&$form) {
        /* Variables    */
        global $COURSE,$SESSION;
        $invoice    = null;
        $grp        = null;
        $resource_number = '';

        try {
            /* Invoice Type */

            $form->addElement('html','<label class="invoice_info">' . get_string('invoice_info','enrol_invoice') . '</label>');

            /* Account  */
            $invoice = array();
            $invoice[0] = $form->createElement('radio', 'invoice_type','',get_string('account_invoice','enrol_invoice'),ACCOUNT_INVOICE);
            $invoice[0]->setValue(ACCOUNT_INVOICE);
            //$invoice[1] = $mform->createElement('text','account',null,'class="account" disabled');
            //$mform->setType('account',PARAM_INT);
            /* Responsibility Number */
            $invoice[1] = $form->createElement('text','resp_number',get_string('invoice_resp','enrol_invoice'),'class="address" disabled');
            $form->setType('resp_number',PARAM_TEXT);
            /* Service Number   */
            $invoice[2] = $form->createElement('text','service_number',get_string('invoice_service','enrol_invoice'),'class="address" disabled');
            $form->setType('service_number',PARAM_TEXT);
            /* Project Number   */
            $invoice[3] = $form->createElement('text','project_number',get_string('invoice_project','enrol_invoice'),'class="address" disabled');
            $form->setType('project_number',PARAM_TEXT);
            /* Activity Number  */
            $invoice[4] = $form->createElement('text','act_number',get_string('invoice_act','enrol_invoice'),'class="address" disabled');
            $form->setType('act_number',PARAM_TEXT);
            /* Invoice Approval */
            $invoice[5] = $form->createElement('text','resource_number',get_string('invoice_approval','enrol_invoice'),'class="address" disabled');
            $form->setType('resource_number',PARAM_TEXT);

            $grp = $form->addElement('group', 'grp_InvoiceType', null, $invoice,'</br>' , false);
            $form->addRule('grp_InvoiceType',get_string('required'),'required', null, 'server');

            $urlResource = new moodle_url('/enrol/waitinglist/invoice/invoiceusers.php',array('id' => $COURSE->id));
            $lnkResource = '<a href="' . $urlResource . '" class="link_search" id="id_lnk_search">' . get_string('search_approval','enrol_invoice'). '</a></br>';
            $form->addElement('html',$lnkResource);
            /* Address  */
            $invoice = array();
            $invoice[0] = $form->createElement('radio', 'invoice_type','',get_string('address_invoice','enrol_invoice'),ADDRESS_INVOICE);
            $invoice[0]->setValue(ADDRESS_INVOICE);
            /* Street       */
            $invoice[1] = $form->createElement('text','street',get_string('invoice_street','enrol_invoice'),'class="address" disabled');
            $form->setType('street',PARAM_TEXT);
            /* Post Code    */
            $invoice[2] = $form->createElement('text','post_code',get_string('invoice_post_code','enrol_invoice'),'class="address" disabled');
            $form->setType('post_code',PARAM_TEXT);
            /* City         */
            $invoice[3] = $form->createElement('text','city',get_string('invoice_city','enrol_invoice'),'class="address" disabled');
            $form->setType('city',PARAM_TEXT);
            /* Bil To /Marked With         */
            $invoice[4] = $form->createElement('text','bil_to',get_string('invoice_bil','enrol_invoice'),'class="address" disabled');
            $form->setType('bil_to',PARAM_TEXT);

            $grp = $form->addElement('group', 'grp_InvoiceType', null, $invoice,'</br>' , false);
            $form->addRule('grp_InvoiceType',get_string('required'),'required', null, 'server');
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddElements_ToForm

    /**
     * @param           $data
     * @param           $errors
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    30/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate the invoice data
     */
    public static function Validate_InvoiceData($data,&$errors) {
        /* Variables    */
        $msg_error = '';

        try {
            if (isset($data['invoice_type']) && $data['invoice_type']) {
                switch ($data['invoice_type']) {
                    case ACCOUNT_INVOICE:
                        /* Responsibility Number    */
                        if (!$data['resp_number']) {
                            $errors['grp_InvoiceType'] = get_string('resp_required','enrol_invoice');
                        }//resp_number
                        /* Service Number           */
                        if (!$data['service_number']) {
                            $errors['grp_InvoiceType'] = get_string('service_required','enrol_invoice');
                        }//resp_number

                        break;
                    case ADDRESS_INVOICE:
                        if (!$data['street']) {
                            $msg_error = get_string('street_required','enrol_invoice');
                        }//data_street

                        if (!$data['post_code']) {
                            if ($msg_error) {
                                $msg_error .= '</br>';
                            }//msg_error
                            $msg_error .= get_string('post_code_required','enrol_invoice');
                        }//data_post_code

                        if (!$data['city']) {
                            if ($msg_error) {
                                $msg_error .= '</br>';
                            }//msg_error
                            $msg_error .= get_string('city_required','enrol_invoice');
                        }//data_city

                        if ($msg_error) {
                            $errors['grp_InvoiceType'] = $msg_error;
                        }

                        break;
                    default:
                        break;
                }//_invoice_type
            }

            return $errors;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Validate_InvoiceData

    /**
     * @param               $data
     * @param               $user_id
     * @param               $course_id
     * @param               $waitingId
     *
     * @throws              Exception
     *
     * @creationDate        29/04/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Save information about the invoice
     *
     * @updateDate          14/09/2016
     * @author              eFaktor     (fbv)
     *
     * Description
     * Add company id
     */
    public static function Add_InvoiceInto($data,$user_id,$course_id,$waitingId=0) {
        /* Variables    */
        global $DB;

        try {
            /* Invoice Detail   */
            $invoice_info = new stdClass();
            $invoice_info->userid           = $user_id;
            $invoice_info->companyid        = $data->level_3;
            $invoice_info->courseid         = $course_id;
            $invoice_info->type             = $data->invoice_type;
            $invoice_info->invoiced         = 0;
            $invoice_info->unenrol          = 0;
            $invoice_info->userenrolid      = 0;
            $invoice_info->waitinglistid    = null;
            $invoice_info->timecreated      = time();

            switch ($data->invoice_type) {
                case ACCOUNT_INVOICE:
                    $invoice_info->responumber      = $data->resp_number;
                    $invoice_info->servicenumber    = $data->service_number;
                    $invoice_info->projectnumber    = $data->project_number;
                    $invoice_info->actnumber        = $data->act_number;
                    $invoice_info->ressursnr        = $data->resource_number;

                    break;
                case ADDRESS_INVOICE:
                    $invoice_info->street           = $data->street;
                    $invoice_info->postcode         = $data->post_code;
                    $invoice_info->city             = $data->city;
                    $invoice_info->bilto            = $data->bil_to;

                    break;
                default:
                    break;
            }//invoice_type

            /* Waiting List */
            if ($waitingId) {
                $invoice_info->waitinglistid    = $waitingId;
                $invoice_info->id               = $DB->insert_record('enrol_invoice',$invoice_info);
            }else {

                /* First Check if the user has been enrolled    */
                $rdo = $DB->get_record('user_enrolments',array('userid' => $user_id,'enrolid' => $data->instance),'id');
                if ($rdo) {
                    $invoice_info->userenrolid      = $rdo->id;
                    /* Insert   */
                    $invoice_info->id               = $DB->insert_record('enrol_invoice',$invoice_info);
                }//if_rdo
            }//if_waitinglist
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_InvoiceInto

    /**
     * @param           $userId
     * @param           $courseId
     * @param           $waitingId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    29/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Activate the invoice entry
     */
    public static function activate_enrol_invoice($userId,$courseId,$waitingId) {
        /* Variables    */
        global $DB;
        $rdo        = null;
        $params     = null;
        $sql        = null;
        $time       = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['courseid'] = $courseId;
            $params['userid']   = $userId;
            $params['enrolid']  = 0;
            $params['waiting']  = $waitingId;
            $params['unenrol']  = 0;
            /* Check if the invoice connected with the user has to be activated */
            /* SQL Instruction  */
            $sql = " SELECT		ei.id,
                                    ei.userid,
                                    ei.courseid,
                                    -- ei.userenrolid,
                                    ue.id as 'userenrolid',
                                    ei.waitinglistid,
                                    ei.timemodified
                         FROM		{enrol_invoice}	  ei
                            JOIN	{user_enrolments} ue	ON 	ue.userid 	= ei.userid
                                                            AND	ue.enrolid	= ei.waitinglistid
                         WHERE		ei.userenrolid		= :enrolid
                            AND		ei.userid			= :userid
                            AND		ei.courseid			= :courseid
                            AND		ei.waitinglistid	= :waiting ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                /* Local Time   */
                $time = time();

                foreach ($rdo as $instance) {
                    $instance->timemodified = $time;

                /* Execute  */
                    $DB->update_record('enrol_invoice',$instance);
                }//for_rdo
            }//if_Rdo

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//activate_enrol_invoice

    /**
     * @static
     * @param           $course_id
     * @param           $enrol_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the invoices connected with the course and their details
     *
     * @updateDate      31/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the seats confirmed.
     */

    /**
     * @param           $courseId
     * @param           $enrolId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    29/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the invoices connected with the course and their details
     *
     * @updateDate      31/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the seats confirmed
     *
     * @updateDate      26/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the company is demanded or not to know where thw workplace has to be taken
     */
    public static function Get_InvoicesUsers($courseId,$enrolId) {
        /* Variables */
        $lstInvoices        = array();
        $isCompanyDemanded  = null;

        try {
            // If company is demanded
            $isCompanyDemanded = self::IsCompanyDemanded($enrolId);

            // Get invoices list
            $lstInvoices = self::GetInvoices($courseId,$enrolId,$isCompanyDemanded);
            // Manaul invoices
            self::get_manual_invoices($courseId,$enrolId,$isCompanyDemanded,$lstInvoices);
            return $lstInvoices;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_InvoicesUsers

    /**
     * @static
     * @param           $course_id
     * @param           $enrol_id
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get info about the course
     */
    public static function Get_InfoCourse($course_id,$enrol_id) {
        /* Variables    */
        global $DB;
        $course_info    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course_id']    = $course_id;
            $params['enrol_id']     = $enrol_id;

            /* SQL Instruction  */
            $sql = " SELECT		c.id,
                                c.fullname,
                                ca.name			as 'category',
                                e.name			as 'enrol',
                                e.customint3	as 'max_enrolled',
                                e.customtext3   as 'price'
                     FROM		{course}				c
                        JOIN	{course_categories}	    ca		ON 	ca.id 		= c.category
                        JOIN	{enrol}				    e		ON	e.courseid	= c.id
                                                                AND	e.id		= :enrol_id
                     WHERE		c.id	= :course_id ";

            /* Execute      */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $course_info = new stdClass();
                $course_info->id            = $rdo->id;
                $course_info->name          = $rdo->fullname;
                $course_info->category      = $rdo->category;
                $course_info->enrol         = $rdo->enrol;
                $course_info->max_enrolled  = $rdo->max_enrolled;
                $course_info->price         = $rdo->price;
            }//if_rdo

            return $course_info;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_InfoCourse

    /**
     * @static
     * @param           $invoices_lst
     * @param           $course_info
     * @param           $enrol_id
     * @return          string
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the invoices to display on the screen
     */
    public static function Display_InvoicesCourse($invoices_lst,$course_info,$enrol_id) {
        /* Variables    */
        global $OUTPUT;
        $out_report = '';
        $return_url         = new moodle_url('/course/view.php',array('id' => $course_info->id));

        try {
            $out_report .= html_writer::start_div('block_enrol_invoices');
                /* Back To the Course   */
                $out_report .= $OUTPUT->action_link($return_url,get_string('return_course','enrol_invoice'));
                $csv_url    = new moodle_url('/enrol/invoice/report/report_invoice.php',array('id' => $enrol_id,'courseid' => $course_info->id,'format' => 'csv'));
                $out_report .= '<a href="'.$csv_url->out().'" class="label_download">'.get_string('csvdownload','enrol_invoice').'</a>';

                /* Add Course Info - Header     */
                $out_report .= html_writer::start_div('block_invoices');
                    $out_report .= self::Add_CourseInfo_Header($course_info);
                $out_report .= html_writer::end_div();//block_invoices

                /* Add Invoices List        */
                if ($invoices_lst) {
                    $out_report .= "</br>";
                    $out_report .= html_writer::start_div('block_invoices');
                        /* Add Invoices Users   */
                        $out_report .= self::Add_InvoiceUser_Table($invoices_lst);
                    $out_report .= html_writer::end_div();//block_invoices
                }else {
                    $out_report .= html_writer::start_div('block_invoices');
                    $out_report .= $OUTPUT->notification(strtoupper(get_string('not_invoices','enrol_invoice')), 'notifysuccess');
                    $out_report .= html_writer::end_div();//block_invoices
                }//if_invoices_lst

                /* Return to the Course page  */
                $out_report .= "</br>";
                $out_report .= $OUTPUT->action_link($return_url,get_string('return_course','enrol_invoice'));
                $out_report .= '<a href="'.$csv_url->out().'" class="label_download">'.get_string('csvdownload','enrol_invoice').'</a>';
            $out_report .= html_writer::end_div();//block_enrol_invoices

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Display_InvoicesCourse

    /**
     * @static
     * @param           $course_info
     * @return          string
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the information of the course to the screen report
     */
    public static function Add_CourseInfo_Header($course_info) {
        /* Variables    */
        $price       = null;
        $header_info = " ";

        /* Course Name      */
        $header_info .= html_writer::start_div('block_invoices_course_one');
            $header_info .= '<label class="label_info_course">' . get_string('course') . '</label>';
        $header_info .= html_writer::end_div();
        $header_info .= html_writer::start_div('block_invoices_course_two');
            $header_info .= '<p class="info_course_value">' . $course_info->name . '</p>';
        $header_info .= html_writer::end_div();//right

        /* Category         */
        $header_info .= html_writer::start_div('block_invoices_course_one');
            $header_info .= '<label class="label_info_course">' . get_string('category') . '</label>';
        $header_info .= html_writer::end_div();
        $header_info .= html_writer::start_div('block_invoices_course_two');
            $header_info .= '<p class="info_course_value">' . $course_info->category . '</p>';
        $header_info .= html_writer::end_div();//right

        /* Participants */
        $header_info .= html_writer::start_div('block_invoices_course_one');
            $header_info .= '<label class="label_info_course">' . get_string('participants','enrol_invoice') . '</label>';
        $header_info .= html_writer::end_div();//left
        $header_info .= html_writer::start_div('block_invoices_course_two');
            $header_info .= '<p class="info_course_value">' . $course_info->max_enrolled . '</p>';
        $header_info .= html_writer::end_div();//right

        /* Price Course */
        $header_info .= html_writer::start_div('block_invoices_course_one');
            $header_info .= '<label class="label_info_course">' . get_string('rpt_price','enrol_invoice') . '</label>';
        $header_info .= html_writer::end_div();//left
        $header_info .= html_writer::start_div('block_invoices_course_two');
            if (is_string($course_info->price)) {
                $price = $course_info->price;
            }else if (is_double($course_info->price)) {
                $price = number_format($course_info->price,2,',','.');
            }else {
                $price = $course_info->price;
            }
            $header_info .= '<p class="info_course_value">' . $price . '</p>';
        $header_info .= html_writer::end_div();//right

        return $header_info;
    }//Add_CourseInfo_Header

    /**
     * @static
     * @param           $invoices_lst
     * @return          string
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the table with all the invoices connected to the course.
     */
    public static function Add_InvoiceUser_Table($invoices_lst) {
        /* Variables    */
        $table = '';

        $table .= html_writer::start_tag('table',array('class' => 'generaltable'));
            /* Add the Header       */
            $table .= self::Add_InvoiceUser_HeaderTable();
            /* Add Users Invoices       */
            if ($invoices_lst) {
                $table .= self::Add_InvoiceUser_Content($invoices_lst);
            }//if_invoices_lst
        $table .= html_writer::end_tag('table');

        return $table;
    }//Add_InvoiceUser_Table

    /**
     * @static
     * @return          string
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the invoices table
     *
     * @updateDate      31/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the seats confirmed.
     */
    public static function Add_InvoiceUser_HeaderTable() {
        /* Variables    */
        $header     = '';
        $str_name       = get_string('rpt_name','enrol_invoice');
        $str_place      = get_string('rpt_work','enrol_invoice');
        $str_mail       = get_string('rpt_mail','enrol_invoice');
        $str_detail     = get_string('rpt_details','enrol_invoice');
        $str_seats      = get_string('rpt_seats','enrol_invoice');
        $str_resource   = get_string('rpt_resource','enrol_invoice');
        
        $header .=  html_writer::start_tag('thead');
        $header .=  html_writer::start_tag('tr',array('class' => 'header_invoice'));
            /* User Name    */
            $header .= html_writer::start_tag('th',array('class' => 'user'));
                $header .= $str_name;
            $header .= html_writer::end_tag('th');
            /* Work Place   */
            $header .= html_writer::start_tag('th',array('class' => 'info'));
                $header .= $str_place;
            $header .= html_writer::end_tag('th');
            /* Mail         */
            $header .= html_writer::start_tag('th',array('class' => 'info'));
                $header .= $str_mail;
            $header .= html_writer::end_tag('th');
            /* Seats Confirmed  */
            $header .= html_writer::start_tag('th',array('class' => 'seats'));
                $header .= $str_seats;
            $header .= html_writer::end_tag('th');
            /* Details      */
            $header .= html_writer::start_tag('th',array('class' => 'type'));
                $header .= $str_detail;
            $header .= html_writer::end_tag('th');
            /* Resource Number  */
            $header .= html_writer::start_tag('th',array('class' => 'type'));
                $header .= $str_resource;
            $header .= html_writer::end_tag('th');
        $header .= html_writer::end_tag('tr');
        $header .= html_writer::end_tag('thead');

        return $header;
    }//Add_InvoiceUser_HeaderTable

    /**
     * @static
     * @param           $invoices_lst
     * @return          string
     *
     * @creationDate    29/09/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Add the content of the invoices table
     *
     * @updateDate      31/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the seats confirmed.
     */
    public static function Add_InvoiceUser_Content($invoices_lst) {
        /* Variables    */
        $body = ' ';

        foreach($invoices_lst as $id=>$invoice) {
            $body .= html_writer::start_tag('tr');
                /* User Name    */
                $body .= html_writer::start_tag('td',array('class' => 'user'));
                    $url_user = new moodle_url('/user/profile.php',array('id' => $id));
                    $body .= '<a href="' . $url_user . '">' . $invoice->name . '</a>';
                $body .= html_writer::end_tag('td');
                /* Work Place   */
                $body .= html_writer::start_tag('td',array('class' => 'info'));
                    $body .= $invoice->arbeidssted;
                $body .= html_writer::end_tag('td');
                /* Mail         */
                $body .= html_writer::start_tag('td',array('class' => 'info'));
                    $body .= $invoice->email;
                $body .= html_writer::end_tag('td');
                /* Seats Confirmed  */
                $body .= html_writer::start_tag('td',array('class' => 'seats'));
                    $body .= $invoice->seats;
                $body .= html_writer::end_tag('td');
                /* Details      */
                $body .= html_writer::start_tag('td',array('class' => 'type'));
                    switch ($invoice->type) {
                        case 'ACCOUNT':
                            if ($invoice->respo && $invoice->service) {
                                $body .= $invoice->respo . "/" . $invoice->service;
                            }

                            /* Project Field    */
                            if ($invoice->project) {
                                $body .= "/" . $invoice->project;
                            }

                            /* ACT Field    */
                            if ($invoice->act) {
                                $body .= "/" . $invoice->act;
                            }

                            break;
                        case 'ADDRESS':
                            $body .= $invoice->street . "</br>" . $invoice->post_code . " " . $invoice->city . "</br>" . $invoice->bil_to;
                            break;
                    }//switch_type
                $body .= html_writer::end_tag('td');
                /* Resource Number  */
                $body .= html_writer::start_tag('td',array('class' => 'type'));
                    $body .= $invoice->resource_number;
                $body .= html_writer::end_tag('td');
            $body .= html_writer::end_tag('tr');
        }//for_invoices_lst

        return $body;
    }//Add_InvoiceUser_Content

    /**
     * @static
     * @param           $invoices_lst
     * @param           $course_info
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download all the invoices connected to the course in excel file
     */
    public static function Download_RequestCourses($invoices_lst,$course_info) {
        /* Variables    */
        $row = 0;

        try {
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $name = clean_filename('Invoices_' . $course_info->name . '_' . $time . ".xls");
            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($name);

            /* Course Info                  */
            self::AddExcel_CourseInfoSheet($course_info,$export);
            /* Invoices Info                */
            self::AddExcel_InvoicesInfoSheet($invoices_lst,$export);

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }////Download_RequestCourses

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $enrolId
     *
     * @return          bool|null
     * @throws          Exception
     *
     * @creationDate    26/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check id the company is demanded or not
     */
    private static function IsCompanyDemanded($enrolId) {
        /* Variables */
        global $DB;
        $rdo = null;
        $isCompanyDemanded = null;

        try {
            $rdo = $DB->get_record('enrol',array('id' => $enrolId),'customint7');
            if ($rdo) {
                if ($rdo->customint7 != ENROL_COMPANY_NO_DEMANDED) {
                    $isCompanyDemanded = true;
                }//if_custom
            }//if_rdo

            return $isCompanyDemanded;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsCompanyDemanded

    /**
     * @param           $courseId
     * @param           $enrolId
     * @param           $isCompanyDemanded
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get invoices connected with when company is demanded
     */
    private static function GetInvoices($courseId,$enrolId,$isCompanyDemanded) {
        /* Variables */
        global  $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $info           = null;
        $lstInvoices    = array();

        try {
            /* Search Criteria      */
            $params = array();
            $params['course_id']    = $courseId;
            $params['enrol_id']     = $enrolId;

            /* SQL Instruction  */
            $sql = " SELECT	  DISTINCT	u.id,
                                        u.firstname,
                                        u.lastname,
                                        u.email,
                                        ei.type,
                                        ei.responumber,
                                        ei.servicenumber,
                                        ei.projectnumber,
                                        ei.actnumber,
                                        ei.ressursnr,
                                        ei.street,
                                        ei.postcode,
                                        ei.city,
                                        ei.bilto,
                                        ei.waitinglistid,
                                        ei.companyid,
                                        co.industrycode,
                                        co.name
                     FROM		    {user}					  u
                        JOIN	    {user_enrolments}		  ue	ON 		ue.userid 		= 	u.id
                                                                    AND		ue.enrolid		=	:enrol_id
                        JOIN	    {enrol_invoice}			  ei	ON		ei.userenrolid	=	ue.id
                                                                    AND		ei.courseid		=	:course_id
                                                                    AND		ei.userid		= 	ue.userid
                                                                    AND		ei.unenrol		= 	0
                        LEFT JOIN	{report_gen_companydata}  co	ON		co.id			= 	ei.companyid
                    WHERE		u.deleted = 0
                     ORDER BY 	u.firstname, u.lastname ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $invoice) {
                    /* Invoice Info */
                    $info = new stdClass();
                    $info->name             = $invoice->firstname . ', ' . $invoice->lastname;
                    $info->email            = $invoice->email;
                    $info->type             = $invoice->type;
                    $info->respo            = $invoice->responumber;
                    $info->service          = $invoice->servicenumber;
                    $info->project          = $invoice->projectnumber;
                    $info->act              = $invoice->actnumber;
                    $info->resource_number  = $invoice->ressursnr;
                    $info->street           = $invoice->street;
                    $info->post_code        = $invoice->postcode;
                    $info->city             = $invoice->city;
                    $info->bil_to           = $invoice->bilto;
                    $info->arbeidssted      = null;
                    if ($isCompanyDemanded) {
                        $info->arbeidssted      = $invoice->industrycode . ' - ' . $invoice->name;
                    }else {
                        if ($invoice->companyid) {
                            $info->arbeidssted      = $invoice->industrycode . ' - ' . $invoice->name;
                        }else {
                            $info->arbeidssted = self::GetWorkplaceConnected($invoice->id);
                        }
                    }//if_comapnyDemanded

                    if ($invoice->waitinglistid) {
                        $info->seats        = self::GetConfirmedSeats($invoice->id,$courseId,$invoice->waitinglistid);
                    }//if_waitinglist

                    /* Add Invoice */
                    $lstInvoices[$invoice->id] = $info;
                }//for_rdo
            }//if_rdo

            return $lstInvoices;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInvoices

    /**
     * Description
     * Get users invoices when the user has been enrolled manually
     *
     * @creationDate    12/12/2016
     * @author          eFaktor     (fbv)
     * 
     * @param       int     $courseId               Course id
     * @param       int     $instanceId             Enrol instance id
     * @param       bool    $isCompanyDemanded      Company demanded or not
     * @param       array   $lstInvoices            list of invoices users
     *
     * @throws      Exception
     */
    private static function get_manual_invoices($courseId,$instanceId,$isCompanyDemanded,&$lstInvoices) {
        /* Variables */
        global $DB;
        $sql    = null;
        $rdo    = null;
        $params = null;
        $info   = null;

        try {
            // Search criteria
            $params = array();
            $params['course']   = $courseId;
            $params['instance'] = $instanceId;

            // SQL Isntruction
            $sql = " SELECT	  DISTINCT	
                                  u.id,
                                  u.firstname,
                                  u.lastname,
                                  u.email,
                                  ewq.companyid,
                                  co.industrycode,
                                  co.name,
                                  co.tjeneste,
                                  co.ansvar
                     FROM		  {user}					u
                        JOIN	  {enrol_waitinglist_queue}	ewq	  ON  ewq.userid 		= u.id
                                                                  AND ewq.courseid 		= :course
                                                                  AND ewq.waitinglistid	= :instance
                                                                  AND ewq.methodtype like 'manual'
                        JOIN	  {user_enrolments}			ue	  ON  ue.userid 		= ewq.userid
                                                                  AND ue.enrolid 		= ewq.waitinglistid
                        LEFT JOIN {report_gen_companydata}  co	  ON  co.id			    = ewq.companyid
                     WHERE		u.deleted = 0
                     ORDER BY 	u.firstname, u.lastname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $invoice) {
                    /* Invoice Info */
                    $info = new stdClass();
                    $info->name             = $invoice->firstname . ', ' . $invoice->lastname;
                    $info->email            = $invoice->email;
                    $info->type             = 'ACCOUNT';
                    $info->respo            = $invoice->ansvar;
                    $info->service          = $invoice->tjeneste;
                    $info->project          = null;
                    $info->act              = null;
                    $info->resource_number  = null;
                    $info->street           = null;
                    $info->post_code        = null;
                    $info->city             = null;
                    $info->bil_to           = null;
                    $info->arbeidssted      = null;
                    if ($isCompanyDemanded) {
                        $info->arbeidssted      = $invoice->industrycode . ' - ' . $invoice->name;
                    }else {
                        if ($invoice->companyid) {
                            $info->arbeidssted      = $invoice->industrycode . ' - ' . $invoice->name;
                        }else {
                            $info->arbeidssted = self::GetWorkplaceConnected($invoice->id);
                        }
                    }//if_comapnyDemanded

                    // Seats -> Manual invoice --> 1
                    $info->seats        = 1;

                    /* Add Invoice */
                    $lstInvoices[$invoice->id] = $info;
                }//for_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_manual_invoice_users

    /**
     * @param           $userId
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    26/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get workplace connected with user
     */
    private static function GetWorkplaceConnected($userId) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        $workplace  = null;

        try {
            /* Search criteria  */
            $params =array();
            $params['user_id']  = $userId;
            $params['level']    = 3;

            /* SQL Instruction */
            $sql = " SELECT 	uic.userid,
                                GROUP_CONCAT(DISTINCT CONCAT(co.industrycode, ' - ',co.name) ORDER BY co.industrycode,co.name SEPARATOR '#SE#') 	as 'workplace'
                     FROM		{user_info_competence_data}	uic
                        JOIN	{report_gen_companydata}	co	ON co.id = uic.companyid
                     WHERE	uic.userid = :user_id
                        AND uic.level  = :level ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->workplace) {
                    $workplace =     str_replace('#SE#','</br>',$rdo->workplace);
                }
            }//if_Rdo

            return $workplace;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetWorkplaceConnected

    /**
     * @static
     * @param           $course_info
     * @param           $export
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the information of the course in the excel report. New Sheet
     */
    private static function AddExcel_CourseInfoSheet($course_info,&$export) {
        /* Variables    */
        $row = 0;
        $col = 0;
        $str_course         = get_string('course');
        $str_category       = get_string('category');
        $str_participants   = get_string('participants','enrol_invoice');
        $strPrice           = get_string('rpt_price','enrol_invoice');

        try {
            $my_xls = $export->add_worksheet(get_string('rpt_course_info','enrol_invoice'));

            /* Course       */
            $my_xls->write($row, $col, $str_course,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $col = $col + 6;
            $my_xls->write($row, $col,$course_info->name ,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'left','align' => 'right'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Category     */
            $row ++;
            $col = 0;
            $my_xls->write($row, $col, $str_category,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $col = $col + 6;
            $my_xls->write($row, $col,$course_info->category ,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'left','align' => 'right'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Participants */
            $row ++;
            $col = 0;
            $my_xls->write($row, $col, $str_participants,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $col = $col + 6;
            $my_xls->write($row, $col,$course_info->max_enrolled ,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'left','align' => 'right'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Price    */
            $row ++;
            $col = 0;
            $my_xls->write($row, $col, $strPrice,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $col = $col + 6;
            $my_xls->write($row, $col,$course_info->price ,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'left','align' => 'right'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Merge Cells  */
            $col = 0;
            $row++;
            $my_xls->merge_cells($row,$col,$row,$col+11);
            $row++;
            $my_xls->merge_cells($row,$col,$row,$col+11);
            $row++;
            $my_xls->merge_cells($row,$col,$row,$col+11);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddExcel_CourseInfoSheet

    /**
     * @static
     * @param           $invoices_lst
     * @param           $export
     * @throws          Exception
     *
     * @creationDate    03/11/2014
     * @author          eFaktor     (fbV)
     *
     * Description
     * Add the Invoices sheet to the excel report.
     */
    private static function AddExcel_InvoicesInfoSheet($invoices_lst,&$export) {
        /* Variables    */
        $row = 0;

        try {
            // Adding the worksheet
            $my_xls = $export->add_worksheet(get_string('rpt_invoices_info','enrol_invoice'));

            /* Add Header Table Users   */
            self::Add_HeaderExcel_CourseTable($my_xls,$row);
            /* Add Content              */
            $row ++;
            self::Add_ContentExcel_CourseTable($invoices_lst,$my_xls,$row);
        }catch (Exception $ex) {
            throw $ex;
        }//throw $ex;
    }//AddExcel_InvoicesInfoSheet

    /**
     * @static
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the invoices table in the excel report
     *
     * @updateDate      31/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the seats confirmed.
     */
    private static function Add_HeaderExcel_CourseTable(&$my_xls,&$row) {
        /* Variables    */
        $col = 0;
        $str_name       = get_string('rpt_name','enrol_invoice');
        $str_place      = get_string('rpt_work','enrol_invoice');
        $str_mail       = get_string('rpt_mail','enrol_invoice');
        $str_street     = get_string('invoice_street','enrol_invoice');
        $str_post       = get_string('invoice_post_code','enrol_invoice');
        $str_city       = get_string('invoice_city','enrol_invoice');
        $str_bil        = get_string('invoice_bil','enrol_invoice');
        $str_resp       = get_string('invoice_resp','enrol_invoice');
        $str_service    = get_string('invoice_service','enrol_invoice');
        $str_project    = get_string('invoice_project','enrol_invoice');
        $str_act        = get_string('invoice_act','enrol_invoice');
        $str_seats      = get_string('rpt_seats','enrol_invoice');
        $str_resource   = get_string('rpt_resource','enrol_invoice');

        try {
            /* User/Name    */
            $my_xls->write($row, $col, $str_name,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);

            /* Work Place   */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_place,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);

            /* Mail         */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_mail,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);

            /* Seats    */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_seats,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);

            /* Street           */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_street,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);
            /* Post Code        */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_post,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);
            /* City             */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_city,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);
            /* Marked with      */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_bil,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);

            /* Responsibility number    */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_resp,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);
            /* Service number           */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_service,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);
            /* Project number           */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_project,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);
            /* Activity number          */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_act,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);

            /* Resource Number */
            $col = $col + 1;
            $my_xls->write($row, $col, $str_resource,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_HeaderExcel_CourseTable

    /**
     * @static
     * @param           $invoices_lst
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the invoices table to the excel report
     *
     * @updateDate      31/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the seats confirmed.
     */
    private static function Add_ContentExcel_CourseTable($invoices_lst,&$my_xls,&$row) {
        /* Variables    */
        $col    = 0;

        try {
            foreach($invoices_lst as $id=>$invoice) {
                /* User/name    */
                $my_xls->write($row, $col, $invoice->name,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,25);

                /* Work Place   */
                $col = $col + 1;
                $my_xls->write($row, $col, str_replace('</br>',',',$invoice->arbeidssted),array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,25);

                /* Mail         */
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->email,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,25);

                /* Seats    */
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->seats,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,25);

                /* Street       */
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->street,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,25);
                /* Post Code    */
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->post_code,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,25);
                /* City         */
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->city,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,25);
                /* Marked with  */
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->bil_to,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,25);

                /* Responsibility number    */
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->respo,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,25);
                /* Service number           */
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->service,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,25);
                /* Project number           */
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->project,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,25);
                /* Activity number          */
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->act,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,25);

                /* Resource Number */
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->resource_number,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,25);

                $row ++;
                $col = 0;
            }//for_invoices
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_ContentExcel_CourseTable

    /**
     * @param           $userId
     * @param           $courseId
     * @param           $waitingListId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    31/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get seats confirmed
     */
    private static function GetConfirmedSeats($userId,$courseId,$waitingListId) {
        /* Variables    */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['userid']           = $userId;
            $params['courseid']         = $courseId;
            $params['waitinglistid']    = $waitingListId;


            /* SQL Instruction  */
            $sql = " SELECT	id,
                            allocseats
                     FROM	{enrol_waitinglist_queue}
                     WHERE	userid 			= :userid
                        AND	courseid 		= :courseid
                        AND waitinglistid 	= :waitinglistid
                        -- AND methodtype 		= 'unnamedbulk' ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->allocseats) {
                    return $rdo->allocseats;
                }else {
                    return 0;
                }
            }else {
                return '-';
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetConfirmedSeats
}//Invoices