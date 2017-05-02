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
     * Description
     * Get invoice data connected with the company
     *
     * @param           $levelTwo
     * @param           $levelThree
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    14/09/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_invoice_data($levelTwo,$levelThree) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $invoice    = null;

        try {
            // Level three
            $params = array();
            $params['id']               = $levelThree;
            $params['hierarchylevel']   = 3;

            // Invoice data for level three
            $rdo = $DB->get_record('report_gen_companydata',$params,'id,tjeneste,ansvar');
            if ($rdo) {
                if ($rdo->tjeneste && $rdo->ansvar) {
                    $invoice = $rdo;
                }
            }//if_rdo

            // Invoice data for level two
            if (!$invoice) {
                // Level two
                $params['id']               = $levelTwo;
                $params['hierarchylevel']   = 2;

                // Execute
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
    }//get_invoice_data
    
    /**
     * Description
     * Add the elements to the form
     *
     * @param           $form
     *
     * @throws          Exception
     *
     * @creationDate    28/10/2015
     * @author          eFaktor     (fbv)
     */
    public static function add_elements_to_form(&$form) {
        /* Variables    */
        global $COURSE;
        $invoice    = null;
        $grp        = null;

        try {
            // Invoice type
            $form->addElement('html','<label class="invoice_info">' . get_string('invoice_info','enrol_invoice') . '</label>');

            // Account
            $invoice = array();
            $invoice[0] = $form->createElement('radio', 'invoice_type','',get_string('account_invoice','enrol_invoice'),ACCOUNT_INVOICE);
            $invoice[0]->setValue(ACCOUNT_INVOICE);

            // Responsabitly number
            $invoice[1] = $form->createElement('text','resp_number',get_string('invoice_resp','enrol_invoice'),'class="address" disabled');
            $form->setType('resp_number',PARAM_TEXT);

            // Service number
            $invoice[2] = $form->createElement('text','service_number',get_string('invoice_service','enrol_invoice'),'class="address" disabled');
            $form->setType('service_number',PARAM_TEXT);
            // Project number
            $invoice[3] = $form->createElement('text','project_number',get_string('invoice_project','enrol_invoice'),'class="address" disabled');
            $form->setType('project_number',PARAM_TEXT);
            // Activity number
            $invoice[4] = $form->createElement('text','act_number',get_string('invoice_act','enrol_invoice'),'class="address" disabled');
            $form->setType('act_number',PARAM_TEXT);
            // Invoice approval
            $invoice[5] = $form->createElement('text','resource_number',get_string('invoice_approval','enrol_invoice'),'class="address" disabled');
            $form->setType('resource_number',PARAM_TEXT);

            $grp = $form->addElement('group', 'grp_InvoiceType', null, $invoice,'</br>' , false);
            $form->addRule('grp_InvoiceType',get_string('required'),'required', null, 'server');

            $urlResource = new moodle_url('/enrol/waitinglist/invoice/invoiceusers.php',array('id' => $COURSE->id));
            $lnkResource = '<a href="' . $urlResource . '" class="link_search" id="id_lnk_search">' . get_string('search_approval','enrol_invoice'). '</a></br>';
            $form->addElement('html',$lnkResource);
            // address
            $invoice = array();
            $invoice[0] = $form->createElement('radio', 'invoice_type','',get_string('address_invoice','enrol_invoice'),ADDRESS_INVOICE);
            $invoice[0]->setValue(ADDRESS_INVOICE);
            // Street
            $invoice[1] = $form->createElement('text','street',get_string('invoice_street','enrol_invoice'),'class="address" disabled');
            $form->setType('street',PARAM_TEXT);
            // Post code
            $invoice[2] = $form->createElement('text','post_code',get_string('invoice_post_code','enrol_invoice'),'class="address" disabled');
            $form->setType('post_code',PARAM_TEXT);
            // City
            $invoice[3] = $form->createElement('text','city',get_string('invoice_city','enrol_invoice'),'class="address" disabled');
            $form->setType('city',PARAM_TEXT);
            // Bil to/Marked with
            $invoice[4] = $form->createElement('text','bil_to',get_string('invoice_bil','enrol_invoice'),'class="address" disabled');
            $form->setType('bil_to',PARAM_TEXT);
            
            $grp = $form->addElement('group', 'grp_InvoiceType', null, $invoice,'</br>' , false);
            $form->addRule('grp_InvoiceType',get_string('required'),'required', null, 'server');
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_elements_to_form

    /**
     * Description
     * Validate the invoice data
     *
     * @param           $data
     * @param           $errors
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    30/10/2015
     * @author          eFaktor     (fbv)
     */
    public static function validate_invoice_data($data,&$errors) {
        /* Variables    */
        $msg_error = '';

        try {
            if (isset($data['invoice_type']) && $data['invoice_type']) {
                switch ($data['invoice_type']) {
                    case ACCOUNT_INVOICE:
                        // Responsability number
                        if (!$data['resp_number']) {
                            $errors['grp_InvoiceType'] = get_string('resp_required','enrol_invoice');
                        }//resp_number
                        // Service number
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
    }//validate_invoice_data

    /**
     * Description
     * Save information about the invoice
     * Add company id
     *
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
     * @updateDate          14/09/2016
     * @author              eFaktor     (fbv)
     */
    public static function add_invoice_info($data,$user_id,$course_id,$waitingId=0) {
        /* Variables    */
        global $DB;

        try {
            // Invoices detail
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

            // Waiting list
            if ($waitingId) {
                $invoice_info->waitinglistid    = $waitingId;
                $invoice_info->id               = $DB->insert_record('enrol_invoice',$invoice_info);
            }else {
                // check if the users has been enrolled
                $rdo = $DB->get_record('user_enrolments',array('userid' => $user_id,'enrolid' => $data->instance),'id');
                if ($rdo) {
                    $invoice_info->userenrolid      = $rdo->id;

                    // Insert
                    $invoice_info->id               = $DB->insert_record('enrol_invoice',$invoice_info);
                }//if_rdo
            }//if_waitinglist
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_invoice_info

    /**
     * Description
     * Activate the invoice entry
     *
     * @param           $userId
     * @param           $courseId
     * @param           $waitingId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    29/04/2015
     * @author          eFaktor     (fbv)
     */
    public static function activate_enrol_invoice($userId,$courseId,$waitingId) {
        /* Variables    */
        global $DB;
        $rdo        = null;
        $params     = null;
        $sql        = null;
        $time       = null;

        try {
            // Search criteria
            $params = array();
            $params['courseid'] = $courseId;
            $params['userid']   = $userId;
            $params['enrolid']  = 0;
            $params['waiting']  = $waitingId;
            $params['unenrol']  = 0;

            // SQL instruction -- Check if the invoice connected with has to be activated
            $sql = " SELECT		  ei.id,
                                  ei.userid,
                                  ei.courseid,
                                  ue.id as 'userenrolid',
                                  ei.waitinglistid,
                                  ei.timemodified
                         FROM	  {enrol_invoice}	ei
                            JOIN  {user_enrolments} ue	ON 	ue.userid 	= ei.userid
                                                        AND	ue.enrolid	= ei.waitinglistid
                         WHERE	  ei.userenrolid	= :enrolid
                            AND	  ei.userid			= :userid
                            AND	  ei.courseid		= :courseid
                            AND	  ei.waitinglistid	= :waiting ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                // Local time
                $time = time();

                foreach ($rdo as $instance) {
                    $instance->timemodified = $time;

                    // Execute
                    $DB->update_record('enrol_invoice',$instance);
                }//for_rdo
            }//if_Rdo

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//activate_enrol_invoice

    /**
     * Description
     * Get all the invoices connected with the course and their details
     * Check if the company is demanded or not to know where thw workplace has to be taken
     * Add the seats confirmed
     *
     * @param           $courseId
     * @param           $enrolId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    29/09/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      31/10/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      26/10/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_invoices_users($courseId,$enrolId) {
        /* Variables */
        $lstInvoices        = null;

        try {

            // Get invoices list
            $lstInvoices = self::get_invoices($courseId,$enrolId);
            // Manaul invoices
            self::get_manual_invoices($courseId,$enrolId,$lstInvoices);
            
            return $lstInvoices;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_invoices_users

    /**
     * Description
     * Get info about the course
     *
     * @param           $course_id
     * @param           $enrol_id
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     */
    public static function get_info_course($course_id,$enrol_id) {
        /* Variables    */
        global $DB;
        $info    = null;

        try {
            // Search criteria
            $params = array();
            $params['course_id']    = $course_id;
            $params['enrol_id']     = $enrol_id;

            // SQL Instruction - Info course
            $sql = " SELECT	      c.id,
                                  c.fullname        as 'name',
                                  c.startdate,
                                  e.name			as 'enrol',
                                  e.customtext3     as 'internal',
                                  e.customtext4 	as 'external',
                                  cf.value 		    as 'location',
                                  ''                as 'instructors'
                     FROM	      {course}				  c
                        JOIN      {course_categories}	  ca	ON 	ca.id 		= c.category
                        JOIN      {enrol}				  e		ON	e.courseid	= c.id
                                                                AND	e.id		= :enrol_id
	                    LEFT JOIN {course_format_options} cf	ON  cf.courseid = c.id
												                AND cf.name like '%course_location%'
                     WHERE	  c.id	= :course_id  ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_info_course

    /**
     * Description
     * Get all the invoices to display on the screen
     *
     * @param           $invoices_lst
     * @param           $course_info
     * @param           $enrol_id
     * @return          string
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     */
    public static function display_invoices_course($invoices_lst,$course_info,$enrol_id) {
        /* Variables    */
        global $OUTPUT;
        $out_report     = '';
        $return_url     = new moodle_url('/course/view.php',array('id' => $course_info->id));

        try {
            $out_report .= html_writer::start_div('block_enrol_invoices');
                // back to the course - lnk
                $out_report .= $OUTPUT->action_link($return_url,get_string('return_course','enrol_invoice'));
                $csv_url    = new moodle_url('/enrol/invoice/report/report_invoice.php',array('id' => $enrol_id,'courseid' => $course_info->id,'format' => 'csv'));
                $out_report .= '<a href="'.$csv_url->out().'" class="label_download">'.get_string('csvdownload','enrol_invoice').'</a>';

                // Course info header
                $out_report .= html_writer::start_div('block_invoices');
                    $out_report .= self::add_course_info_header($course_info);
                $out_report .= html_writer::end_div();//block_invoices

                // Invoices list
                if ($invoices_lst) {
                    $out_report .= "</br>";
                    $out_report .= html_writer::start_div('block_invoices');
                        // Invoices table
                        $out_report .= self::add_invoice_user_table($invoices_lst);
                    $out_report .= html_writer::end_div();//block_invoices
                }else {
                    $out_report .= html_writer::start_div('block_invoices');
                        $out_report .= $OUTPUT->notification(strtoupper(get_string('not_invoices','enrol_invoice')), 'notifysuccess');
                    $out_report .= html_writer::end_div();//block_invoices
                }//if_invoices_lst

                // Return to the course page
                $out_report .= "</br>";
                $out_report .= $OUTPUT->action_link($return_url,get_string('return_course','enrol_invoice'));
                $out_report .= '<a href="'.$csv_url->out().'" class="label_download">'.get_string('csvdownload','enrol_invoice').'</a>';
            $out_report .= html_writer::end_div();//block_enrol_invoices

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//display_invoices_course

    /**
     * Description
     * Add the information of the course to the screen report
     *
     * @param           $course_info
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     */
    public static function add_course_info_header($course_info) {
        /* Variables    */
        $location       = null;
        $strlocation    = null;
        $strdate        = null;
        $price          = null;
        $urlinstructor  = null;
        $strinstructor  = null;
        $header_info    = " ";

        try {
            // url Instructor
            $urlinstructor = new moodle_url('/user/profile.php');

            // Course name
            $header_info .= html_writer::start_div('block_invoices_course_one');
                $header_info .= '<label class="label_info_course">' . get_string('course') . '</label>';
            $header_info .= html_writer::end_div();
            $header_info .= html_writer::start_div('block_invoices_course_two');
                $header_info .= '<p class="info_course_value">' . $course_info->name  . '</p>';
            $header_info .= html_writer::end_div();//right

            // Course Date
            $header_info .= html_writer::start_div('block_invoices_course_one');
                $header_info .= '<label class="label_info_course">' . get_string('date') . '</label>';
            $header_info .= html_writer::end_div();
            $header_info .= html_writer::start_div('block_invoices_course_two');
                if ($course_info->startdate) {
                    $strdate = userdate($course_info->startdate,'%d.%m.%Y', 99, false);
                }
                $header_info .= '<p class="info_course_value">' . $strdate  . '</p>';
            $header_info .= html_writer::end_div();//right

            // Location
            $header_info .= html_writer::start_div('block_invoices_course_one');
                $header_info .= '<label class="label_info_course">' . get_string('rpt_location','enrol_invoice') . '</label>';
            $header_info .= html_writer::end_div();
            $header_info .= html_writer::start_div('block_invoices_course_two');
                if ($course_info->location) {
                    $location = self::get_location($course_info->location);
                    $strlocation = $location->street . "</br>";
                    if ($location->postcode) {
                        $strlocation .= $location->postcode . " ";
                    }
                    if ($location->city) {
                        $strlocation .= $location->city;
                    }
                }else {
                    $strlocation = ' - ';
                }
                $header_info .= '<p class="info_course_value">' . $strlocation . '</p>';
            $header_info .= html_writer::end_div();//right

            // Instructors
            $header_info .= html_writer::start_div('block_invoices_course_one');
                $header_info .= '<label class="label_info_course">' . get_string('home_teachers','local_course_page') . '</label>';
            $header_info .= html_writer::end_div();
            $header_info .= html_writer::start_div('block_invoices_course_two');
                if ($course_info->instructors) {
                    foreach ($course_info->instructors as $id => $teacher) {
                        $urlinstructor->param('id',$id);
                        $strinstructor .= '<a href="' . $urlinstructor . '">' . $teacher . '</a></br>';
                    }//foreach_teacher
                }else {
                    $strinstructor = ' ';
                }
                $header_info .= '<p class="info_course_value">' . $strinstructor . '</p>';
            $header_info .= html_writer::end_div();//right

            // Internal price
            $header_info .= html_writer::start_div('block_invoices_course_one');
                $header_info .= '<label class="label_info_course">' . get_string('price_int','enrol_invoice') . '</label>';
            $header_info .= html_writer::end_div();//left
            $header_info .= html_writer::start_div('block_invoices_course_two');
                if (is_string($course_info->internal)) {
                    $price = $course_info->internal;
                }else if (is_double($course_info->internal)) {
                    $price = number_format($course_info->internal,2,',','.');
                }else {
                    $price = $course_info->internal;
                }
                $header_info .= '<p class="info_course_value">' . $price . '</p>';
            $header_info .= html_writer::end_div();//right

            // External price
            $header_info .= html_writer::start_div('block_invoices_course_one');
                $header_info .= '<label class="label_info_course">' . get_string('price_ext','enrol_invoice') . '</label>';
            $header_info .= html_writer::end_div();//left
            $header_info .= html_writer::start_div('block_invoices_course_two');
                if (is_string($course_info->external)) {
                    $price = $course_info->external;
                }else if (is_double($course_info->external)) {
                    $price = number_format($course_info->external,2,',','.');
                }else {
                    $price = $course_info->external;
                }
                $header_info .= '<p class="info_course_value">' . $price . '</p>';
            $header_info .= html_writer::end_div();//right
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch

        return $header_info;
    }//add_course_info_header

    /**
     * Description
     * Create the table with all the invoices connected to the course.
     *
     * @param           $invoices_lst
     * @return          string
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     */
    public static function add_invoice_user_table($invoices_lst) {
        /* Variables    */
        $table = '';

        $table .= html_writer::start_tag('table',array('class' => 'generaltable'));
            // Header
            $table .= self::add_invoice_user_header_table();

            // Add invoices
            if ($invoices_lst) {
                $table .= self::add_invoice_user_content($invoices_lst);
            }//if_invoices_lst
        $table .= html_writer::end_tag('table');

        return $table;
    }//add_invoice_user_table

    /**
     * Description
     * Add the seats confirmed.
     * 
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
     */
    public static function add_invoice_user_header_table() {
        /* Variables    */
        $header     = '';
        $str_name       = get_string('rpt_name','enrol_invoice');
        $str_place      = get_string('rpt_work','enrol_invoice');
        $str_mail       = get_string('rpt_mail','enrol_invoice');
        $str_detail     = get_string('rpt_details','enrol_invoice');
        $str_seats      = get_string('rpt_seats','enrol_invoice');
        $str_resource   = get_string('rpt_resource','enrol_invoice');
        $str_completed  = get_string('rpt_completed','enrol_invoice');

        $header .=  html_writer::start_tag('thead');
        $header .=  html_writer::start_tag('tr',array('class' => 'header_invoice'));
            // Username
            $header .= html_writer::start_tag('th',array('class' => 'user'));
                $header .= $str_name;
            $header .= html_writer::end_tag('th');
            // workplace
            $header .= html_writer::start_tag('th',array('class' => 'info'));
                $header .= $str_place;
            $header .= html_writer::end_tag('th');
            // Mail
            $header .= html_writer::start_tag('th',array('class' => 'email'));
                $header .= $str_mail;
            $header .= html_writer::end_tag('th');
            // Seats
            $header .= html_writer::start_tag('th',array('class' => 'seats'));
                $header .= $str_seats;
            $header .= html_writer::end_tag('th');
            // Details
            $header .= html_writer::start_tag('th',array('class' => 'type'));
                $header .= $str_detail;
            $header .= html_writer::end_tag('th');
            // Resource number
            $header .= html_writer::start_tag('th',array('class' => 'type'));
                $header .= $str_resource;
            $header .= html_writer::end_tag('th');
            // Completed
            $header .= html_writer::start_tag('th',array('class' => 'seats'));
                $header .= $str_completed;
            $header .= html_writer::end_tag('th');
        $header .= html_writer::end_tag('tr');
        $header .= html_writer::end_tag('thead');

        return $header;
    }//add_invoice_user_header_table

    /**
     * Description
     * Add the content of the invoices table
     * Add the seats confirmed.
     *
     * @param           $invoices_lst
     * @return          string
     *
     * @creationDate    29/09/2014
     * @author          eFaktor         (fbv)
     *
     * @updateDate      31/10/2015
     * @author          eFaktor     (fbv)
     *
     */
    public static function add_invoice_user_content($invoices_lst) {
        /* Variables    */
        $body = ' ';
        $str_name       = get_string('rpt_name','enrol_invoice');
        $str_place      = get_string('rpt_work','enrol_invoice');
        $str_mail       = get_string('rpt_mail','enrol_invoice');
        $str_detail     = get_string('rpt_details','enrol_invoice');
        $str_seats      = get_string('rpt_seats','enrol_invoice');
        $str_resource   = get_string('rpt_resource','enrol_invoice');
        $str_completed  = get_string('rpt_completed','enrol_invoice');

        foreach($invoices_lst as $id=>$invoice) {
            $body .= html_writer::start_tag('tr');
                // user name
                $body .= html_writer::start_tag('td',array('class' => 'user','data-th' => $str_name));
                    $url_user = new moodle_url('/user/profile.php',array('id' => $id));
                    $body .= '<a href="' . $url_user . '">' . $invoice->name . '</a>';
                $body .= html_writer::end_tag('td');
                // workplace
                $body .= html_writer::start_tag('td',array('class' => 'info','data-th' => $str_place));
                    if ($invoice->arbeidssted) {
                        $body .= $invoice->municipality . '/' . $invoice->sector . '/' . $invoice->arbeidssted;
                    }//if_arbeidssted
                $body .= html_writer::end_tag('td');
                // Mail
                $body .= html_writer::start_tag('td',array('class' => 'email','data-th' => $str_mail));
                    $body .= $invoice->email;
                $body .= html_writer::end_tag('td');
                // Seats
                $body .= html_writer::start_tag('td',array('class' => 'seats','data-th' => $str_seats));
                    $body .= $invoice->seats;
                $body .= html_writer::end_tag('td');
                // Details
                $body .= html_writer::start_tag('td',array('class' => 'type','data-th' => $str_detail));
                    switch ($invoice->type) {
                        case 'ACCOUNT':
                            if ($invoice->respo && $invoice->service) {
                                $body .= $invoice->respo . "/" . $invoice->service;
                            }

                            // Project field
                            if ($invoice->project) {
                                $body .= "/" . $invoice->project;
                            }

                            // Act field
                            if ($invoice->act) {
                                $body .= "/" . $invoice->act;
                            }

                            break;
                        case 'ADDRESS':
                            $body .= $invoice->street . "</br>" . $invoice->post_code . " " . $invoice->city . "</br>" . $invoice->bil_to;
                            break;
                    }//switch_type
                $body .= html_writer::end_tag('td');
                // Resource number
                $body .= html_writer::start_tag('td',array('class' => 'type','data-th' => $str_resource));
                    $body .= $invoice->resource_number;
                $body .= html_writer::end_tag('td');
                // Completed
                $body .= html_writer::start_tag('td',array('class' => 'seats','data-th' => $str_completed));
                    if ($invoice->completed) {
                        $body .= get_string('yes');
                    }else {
                        $body .= get_string('no');
                    }
                $body .= html_writer::end_tag('td');
            $body .= html_writer::end_tag('tr');
        }//for_invoices_lst

        return $body;
    }//add_invoice_user_content

    /**
     * Description
     * Download all the invoices connected to the course in excel file
     *
     * @param           $invoices_lst
     * @param           $course_info
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     */
    public static function download_request_courses($invoices_lst,$course_info) {
        /* Variables    */
        $row = 0;

        try {
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $name = clean_filename('Invoices_' . $course_info->name . '_' . $time . ".xls");
            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($name);

            // Course info
            self::add_excel_course_info_sheet($course_info,$export);

            // Invoices
            self::add_excel_invoices_info_sheet($invoices_lst,$export);

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//download_request_courses

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Get location detail
     * @param       integer $locationid
     *
     * @return              mixed|null
     * @throws              Exception
     *
     * @creationDate    14/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_location($locationid) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        try {
            // Search criteria
            $params = array();
            $params['location'] = $locationid;

            // SQL instruction location detail
            $sql = " SELECT	  cl.id,
                              levelzero.name 	as 'county',
                              levelone.name 	as 'muni',
                              cl.name,
                              cl.floor,
                              cl.room,
                              cl.seats,
                              cl.street,
                              cl.postcode,
                              cl.city
                     FROM	  {course_locations}		    cl
                        JOIN  {report_gen_companydata}		levelzero	ON  levelzero.id    = cl.levelzero
                        JOIN  {report_gen_companydata}		levelone	ON  levelone.id 	= cl.levelone
                     WHERE		cl.id = :location ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rod
        }catch (Exception $ex) {
            throw $ex;
        }
    }//get_location
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
     * Description
     * Get invoices connected with when company is demanded
     *
     * @param           $courseId
     * @param           $enrolId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/10/2016
     * @author          eFaktor     (fbv)
     */
    private static function get_invoices($courseId,$enrolId) {
        /* Variables */
        global  $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $info           = null;
        $lstInvoices    = array();

        try {
            // Search criteria
            $params = array();
            $params['course_id']    = $courseId;
            $params['enrol_id']     = $enrolId;
            $params['sector']       = 2;
            $params['muni']         = 1;


            // SQL Instruction - to get invoices
            $sql = " SELECT	  DISTINCT	
                                    u.id,
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
                                    co.name			as 'workplace',
                                    se.name	 		as 'sector',
                                    mu.name 		as 'municipality',
                                    cc.timecompleted
                     FROM		    {user}						  u
                        JOIN	    {user_enrolments}		  	  ue	ON 	ue.userid 			= 	u.id
                                                                        AND	ue.enrolid			=	:enrol_id
                        JOIN	    {enrol_invoice}				  ei	ON	ei.userenrolid		=	ue.id
                                                                        AND	ei.courseid			=	:course_id
                                                                        AND	ei.userid			= 	ue.userid
                                                                        AND	ei.unenrol			= 	0
                        -- Workplace
                        LEFT JOIN	{report_gen_companydata}  	  co	ON	co.id				= 	ei.companyid
                        -- Sector
                        LEFT JOIN	{report_gen_company_relation} se_r	ON 	se_r.companyid 		= co.id
                        LEFT JOIN	{report_gen_companydata}	  se	ON 	se.id 				= se_r.parentid
                                                                        AND se.hierarchylevel 	= :sector
                        -- Municipality
                        LEFT JOIN	{report_gen_company_relation} mu_r	ON 	mu_r.companyid 		= se.id
                        LEFT JOIN	{report_gen_companydata}	  mu	ON 	mu.id 				= mu_r.parentid
                                                                        AND mu.hierarchylevel 	= :muni
                        -- Completions
                        LEFT JOIN	{course_completions}		  cc	ON cc.course			= ei.courseid 
                                                                        AND cc.userid			= ei.userid
                     WHERE		    u.deleted = 0
                     ORDER BY 	    u.firstname, u.lastname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $invoice) {
                    // Invoice info
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
                    $info->arbeidssted      = $invoice->workplace;
                    $info->sector           = $invoice->sector;
                    $info->municipality     = $invoice->municipality;
                    $info->completed        = $invoice->timecompleted;

                    if ($invoice->waitinglistid) {
                        $info->seats        = self::get_confirmed_seats($invoice->id,$courseId,$invoice->waitinglistid);
                    }//if_waitinglist

                    // Add invoice
                    $lstInvoices[$invoice->id] = $info;
                }//for_rdo
            }//if_rdo

            return $lstInvoices;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_invoices

    /**
     * Description
     * Get users invoices when the user has been enrolled manually
     *
     * @creationDate    12/12/2016
     * @author          eFaktor     (fbv)
     * 
     * @param       int     $courseId               Course id
     * @param       int     $instanceId             Enrol instance id
     * @param       array   $lstInvoices            list of invoices users
     *
     * @throws      Exception
     */
    private static function get_manual_invoices($courseId,$instanceId,&$lstInvoices) {
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
            $params['sector']   = 2;
            $params['muni']     = 1;

            // SQL Instruction - Get invoices
            $sql = " SELECT	DISTINCT	
                                  u.id,
                                  u.firstname,
                                  u.lastname,
                                  u.email,
                                  co.name			as 'workplace',
                                  se.name	 		as 'sector',
                                  mu.name 		    as 'municipality',
                                  co.tjeneste,
                                  co.ansvar,
                                  cc.timecompleted
                     FROM		  {user}						u
                        JOIN	  {enrol_waitinglist_queue}		ewq		ON  ewq.userid 			= u.id
                                                                        AND ewq.courseid 		= :course
                                                                        AND ewq.waitinglistid	= :instance
                                                                        AND ewq.methodtype like 'manual'
                        JOIN	  {user_enrolments}				ue		ON  ue.userid 			= ewq.userid
                                                                        AND ue.enrolid 			= ewq.waitinglistid
                        -- Workplace
                        LEFT JOIN {report_gen_companydata}  	co		ON  co.id			    = ewq.companyid
                        -- Sector
                        LEFT JOIN {report_gen_company_relation}	se_r	ON 	se_r.companyid 		= co.id
                        LEFT JOIN {report_gen_companydata}		se		ON 	se.id 				= se_r.parentid
                                                                        AND se.hierarchylevel 	= :sector
                        -- Municipality
                        LEFT JOIN {report_gen_company_relation}	mu_r	ON 	mu_r.companyid 		= se.id
                        LEFT JOIN {report_gen_companydata}		mu		ON 	mu.id 				= mu_r.parentid
                                                                        AND mu.hierarchylevel 	= :muni
                        -- Completions
                        LEFT JOIN {course_completions}			cc		ON cc.course			= ewq.courseid 
                                                                        AND cc.userid			= ue.userid
                     WHERE		  u.deleted = 0
                     ORDER BY 	  u.firstname, u.lastname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $invoice) {
                    // Invoice info
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
                    $info->arbeidssted      = $invoice->workplace;
                    $info->sector           = $invoice->sector;
                    $info->municipality     = $invoice->municipality;
                    $info->completed        = $invoice->timecompleted;

                    // Seats -> Manual invoice --> 1
                    $info->seats        = 1;

                    // Add invoice
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
     * Description
     * Add the information of the course in the excel report. New Sheet
     *
     * @param           $course_info
     * @param           $export
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     */
    private static function add_excel_course_info_sheet($course_info,&$export) {
        /* Variables    */
        $row            = 0;
        $col            = 0;
        $price          = null;
        $str_course     = null;
        $str_date       = null;
        $str_location   = null;
        $str_internal   = null;
        $str_external   = null;
        $str_instructor = null;
        $cdate          = null;
        $clocation      = null;
        $cinstructor    = null;
        $location       = null;

        try {
            // Headers
            $str_course         = get_string('course');
            $str_date           = get_string('date');
            $str_location       = get_string('rpt_location','enrol_invoice');
            $strinternal        = get_string('price_int','enrol_invoice');
            $strexternal        = get_string('price_ext','enrol_invoice');
            $str_instructor     = get_string('home_teachers','local_course_page');
            
            // Sheet
            $my_xls = $export->add_worksheet(get_string('rpt_course_info','enrol_invoice'));

            // Course
            $my_xls->write($row, $col, $str_course,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $col = $col + 6;
            $my_xls->write($row, $col,$course_info->name ,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'left','align' => 'right'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // Course date
            $row ++;
            $col = 0;
            $my_xls->write($row, $col, $str_date,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $col = $col + 6;
            if ($course_info->startdate) {
                $cdate = userdate($course_info->startdate,'%d.%m.%Y', 99, false);
            }
            $my_xls->write($row, $col,$cdate,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'left','align' => 'right'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // Location
            $row ++;
            $col = 0;
            $my_xls->write($row, $col, $str_location,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'top'));
            $my_xls->merge_cells($row,$col,$row+1,$col+5);
            $my_xls->set_row($row,20);
            $col = $col + 6;
            if ($course_info->location) {
                $location = self::get_location($course_info->location);
                $clocation = $location->street . "\n";
                if ($location->postcode) {
                    $clocation .= $location->postcode . " ";
                }
                if ($location->city) {
                    $clocation .= $location->city;
                }
            }else {
                $clocation = '  ';
            }
            $my_xls->write($row, $col,$clocation,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top','align' => 'right'));
            $my_xls->merge_cells($row,$col,$row+1,$col+5);
            $my_xls->set_row($row,20);

            // Instructors
            $row = $row + 2;
            $col = 0;
            $my_xls->write($row, $col, $str_instructor,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'top'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $col = $col + 6;
            if ($course_info->instructors) {
                foreach ($course_info->instructors as $id => $teacher) {
                    $cinstructor .= $teacher . "\n";
                }
            }else {
                $cinstructor = '  ';
            }
            $my_xls->write($row, $col,$cinstructor,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top','align' => 'right'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);


            // Internal price
            $row ++;
            $col = 0;
            $my_xls->write($row, $col, $strinternal,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            if (is_string($course_info->internal)) {
                $price = $course_info->internal;
            }else if (is_double($course_info->internal)) {
                $price = number_format($course_info->internal,2,',','.');
            }else {
                $price = $course_info->internal;
            }
            $col = $col + 6;
            $my_xls->write($row, $col,$price ,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'left','align' => 'right'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // External price
            $row ++;
            $col = 0;
            $my_xls->write($row, $col, $strexternal,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            if (is_string($course_info->external)) {
                $price = $course_info->external;
            }else if (is_double($course_info->external)) {
                $price = number_format($course_info->external,2,',','.');
            }else {
                $price = $course_info->external;
            }
            $col = $col + 6;
            $my_xls->write($row, $col,$price ,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'left','align' => 'right'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // Merggin cells
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
    }//add_excel_course_info_sheet

    /**
     * Description
     * Add the Invoices sheet to the excel report.
     *
     * @param           $invoices_lst
     * @param           $export
     * @throws          Exception
     *
     * @creationDate    03/11/2014
     * @author          eFaktor     (fbV)
     */
    private static function add_excel_invoices_info_sheet($invoices_lst,&$export) {
        /* Variables    */
        $row = 0;

        try {
            // Adding the worksheet
            $my_xls = $export->add_worksheet(get_string('rpt_invoices_info','enrol_invoice'));

            // Header
            self::add_header_excel_course_table($my_xls,$row);

            // Add content
            $row ++;
            self::add_content_excel_course_table($invoices_lst,$my_xls,$row);
        }catch (Exception $ex) {
            throw $ex;
        }//throw $ex;
    }//add_excel_invoices_info_sheet

    /**
     * Description
     * Add the header of the invoices table in the excel report
     * Add the seats confirmed.
     *
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * @updateDate      31/10/2015
     * @author          eFaktor     (fbv)
     *
     */
    private static function add_header_excel_course_table(&$my_xls,&$row) {
        /* Variables    */
        $col = 0;
        $str_name       = get_string('rpt_name','enrol_invoice');
        $str_place      = get_string('rpt_work','enrol_invoice');
        $str_muni       = get_string('rpt_muni','enrol_invoice');
        $str_sector     = get_string('rpt_sector','enrol_invoice');
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
        $str_completed  = get_string('rpt_completed','enrol_invoice');

        try {
            // user
            $my_xls->write($row, $col, $str_name,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);

            // Municipality
            $col = $col + 1;
            $my_xls->write($row, $col, $str_muni,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);

            // Sector
            $col = $col + 1;
            $my_xls->write($row, $col, $str_sector,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);

            // Workplace
            $col = $col + 1;
            $my_xls->write($row, $col, $str_place,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);

            // Mail
            $col = $col + 1;
            $my_xls->write($row, $col, $str_mail,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);

            // seats
            $col = $col + 1;
            $my_xls->write($row, $col, $str_seats,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);

            // Street
            $col = $col + 1;
            $my_xls->write($row, $col, $str_street,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);
            // Post code
            $col = $col + 1;
            $my_xls->write($row, $col, $str_post,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);
            // City
            $col = $col + 1;
            $my_xls->write($row, $col, $str_city,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);
            // Marked with
            $col = $col + 1;
            $my_xls->write($row, $col, $str_bil,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);

            // Responsability number
            $col = $col + 1;
            $my_xls->write($row, $col, $str_resp,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);
            // Service number
            $col = $col + 1;
            $my_xls->write($row, $col, $str_service,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);
            // Project number
            $col = $col + 1;
            $my_xls->write($row, $col, $str_project,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);
            // Activity number
            $col = $col + 1;
            $my_xls->write($row, $col, $str_act,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);

            // Resource number
            $col = $col + 1;
            $my_xls->write($row, $col, $str_resource,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);

            // Completed
            $col = $col + 1;
            $my_xls->write($row, $col, $str_completed,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->set_row($row,50);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_excel_course_table

    /**
     * Description
     * Add the content of the invoices table to the excel report
     * Add the seats confirmed.
     *
     * @param           $invoices_lst
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    29/09/2014
     * @author          eFaktor     (fbv)
     *
     * @updateDate      31/10/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_content_excel_course_table($invoices_lst,&$my_xls,&$row) {
        /* Variables    */
        $col    = 0;

        try {
            foreach($invoices_lst as $id=>$invoice) {
                // User
                $my_xls->write($row, $col, $invoice->name,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,50);

                //Municipality
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->municipality,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,50);

                // Sector
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->sector,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,50);

                // Workplace
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->arbeidssted,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,50);

                // Mail
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->email,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,50);

                // Seats
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->seats,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,50);

                // street
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->street,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,50);
                // Post code
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->post_code,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,50);
                // City
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->city,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,50);
                // Marked with
                $col = $col + 1;
                $my_xls->write($row, $col, $invoice->bil_to,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->set_row($row,50);

                // Responsability number
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->respo,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,50);
                // Service number
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->service,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,50);
                // Project number
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->project,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,50);
                // Activity number
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->act,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,50);

                // Resource number
                $col = $col + 1;
                $my_xls->write_string($row, $col, $invoice->resource_number,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,50);

                // Completed
                $col = $col + 1;
                $my_xls->write_string($row, $col, ($invoice->completed ? get_string('yes') : get_string('no')),array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'center','align' => 'right'));
                $my_xls->set_row($row,50);

                $row ++;
                $col = 0;
            }//for_invoices
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_content_excel_course_table

    /**
     * Description
     * Get seats confirmed
     *
     * @param           $userId
     * @param           $courseId
     * @param           $waitingListId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    31/10/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_confirmed_seats($userId,$courseId,$waitingListId) {
        /* Variables    */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            // Search criteria
            $params = array();
            $params['userid']           = $userId;
            $params['courseid']         = $courseId;
            $params['waitinglistid']    = $waitingListId;


            // SQL Instruction - get seats
            $sql = " SELECT	id,
                            allocseats
                     FROM	{enrol_waitinglist_queue}
                     WHERE	userid 			= :userid
                        AND	courseid 		= :courseid
                        AND waitinglistid 	= :waitinglistid ";

            // Execute
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
    }//get_confirmed_seats
}//Invoices