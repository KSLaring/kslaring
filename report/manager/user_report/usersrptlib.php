<?php
/**
 * Library code for the Company Report Competence Manager.
 *
 * @package         report
 * @subpackage      manager/user_report
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    24/05/2017
 * @author          eFaktor     (fbv)
 *
 */

define('USER_REPORT_FORMAT_SCREEN', 0);
define('USER_REPORT_FORMAT_SCREEN_EXCEL', 1);
define('USER_REPORT_FORMAT_LIST', 'report_format_list');
define('USER_REPORT_STRUCTURE_LEVEL','level_');

class UserReport {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Initialize the organization structure selectors
     *
     * @param           String $selector
     *
     * @throws                 Exception
     *
     * @creationDate    25/05/2017
     * @author          eFaktor     (fbv)
     */
    public static function Init_OrganizationStructure($selector) {
        /* Variables    */
        global $PAGE;
        $options    = null;
        $hash       = null;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;
        $sp         = null;

        try {
            /* Initialise variables */
            $name       = 'level_structure';
            $path       = '/report/manager/user_report/js/organization.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                'fullpath'    => $path,
                'requires'    => $requires,
                'strings'     => $strings
            );

            $PAGE->requires->js_init_call('M.core_user.init_organization',
                array($selector),
                false,
                $jsModule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_OrganizationStructure

    /**
     * Description
     * Get user report data connected with criteria
     *
     * @param           array $data
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    25/05/2017
     * @author          eFaktor     (fbv)
     */
    public static function data_user_report($data) {
        /* Variables */
        global $DB;
        $sql        = null;
        $sqlWhere   = null;
        $qlOrder    = null;
        $rdo        = null;
        $report     = null;
        $params     = null;
        $report     = null;

        try {
            // Users report object
            $report = new stdClass();
            $report->data   = null;
            $report->zero   = null;
            $report->one    = null;
            $report->two    = null;
            $report->three  = null;

            //Search criteria
            $params = array();
            $params['zero'] = $data[USER_REPORT_STRUCTURE_LEVEL . '0'];
            $report->zero = CompetenceManager::GetCompany_Name($data[USER_REPORT_STRUCTURE_LEVEL . '0']);
            // Criteria - level one
            if ($data[USER_REPORT_STRUCTURE_LEVEL . '1']) {
                $params['one'] = $data[USER_REPORT_STRUCTURE_LEVEL . '1'];
                $sqlWhere .= " AND one = :one ";
                $report->one = CompetenceManager::GetCompany_Name($data[USER_REPORT_STRUCTURE_LEVEL . '1']);
            }//if_one
            // Criteria - level two
            if ($data[USER_REPORT_STRUCTURE_LEVEL . '2']) {
                $params['two'] = $data[USER_REPORT_STRUCTURE_LEVEL . '2'];
                $sqlWhere .= " AND two = :two ";
                $report->two = CompetenceManager::GetCompany_Name($data[USER_REPORT_STRUCTURE_LEVEL . '2']);
            }//if_two
            // Criteria - level three
            if ($data[USER_REPORT_STRUCTURE_LEVEL . '3']) {
                $params['three'] = $data[USER_REPORT_STRUCTURE_LEVEL . '3'];
                $sqlWhere .= " AND three = :three ";
                $report->three = CompetenceManager::GetCompany_Name($data[USER_REPORT_STRUCTURE_LEVEL . '3']);
            }//if_three

            // SQL Instruction
            $sql = " SELECT		CONCAT(up.zero,'_',uc.id) as 'uq',
                                up.*,
                                uc.*
                     FROM		user_profile	up
                        JOIN	user_course		uc	ON uc.userid = up.userid
                     WHERE		up.zero = :zero ";

            // SQL order
            $sqlOrder = " ORDER BY 	up.zero,up.one,up.two,up.three, up.lastname,up.firstname, uc.fullname ";

            // Execute
            $sql .= $sqlWhere . $sqlOrder;
            $report->data = $DB->get_records_sql($sql,$params);

            return $report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//data_user_report

    /**
     * Description
     * Get table for screen report
     *
     * @param           Object  $user_report
     *
     * @return                  null|string
     * @throws                  Exception
     *
     * @creationDate    25/05/2017
     * @author          eFaktor     (fbv)
     */
    public static function print_user_report_screen($user_report) {
        /* Variables */
        $out    = null;
        $return = null;
        $index  = null;

        try {
            // Url to return
            $return = new moodle_url('/report/manager/user_report/user_report.php');
            $index  = new moodle_url('/report/manager/index.php');

            // Report
            $out .= html_writer::start_div('user_rpt_div');
                // Company levels
                $out .= '<ul class="level-list unlist">';
                    // Level zero
                    $out .= '<li>';
                        $out .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . $user_report->zero . '</h3>';
                    $out .= '</li>';
                    // Level one
                    if ($user_report->one) {
                        $out .= '<li>';
                            $out .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $user_report->one . '</h3>';
                        $out .= '</li>';
                    }//if_one
                    // Level two
                    if ($user_report->two) {
                        $out .= '<li>';
                            $out .= '<h3>'. get_string('company_structure_level', 'report_manager', 2) . ': ' . $user_report->two . '</h3>';
                        $out .= '</li>';
                    }//if_two
                    // Level three
                    if ($user_report->three) {
                        $out .= '<li>';
                            $out .= '<h3>'. get_string('company_structure_level', 'report_manager', 3) . ': ' . $user_report->three . '</h3>';
                        $out .= '</li>';
                    }//if_three
                $out .= '</ul>';

                $out .= "</br>";

                // Data report
                if ($user_report->data) {
                    // Return back/selection page
                    $out .= html_writer::link($return,get_string('user_return_to_selection','report_manager'),array('class' => 'link_return'));
                    $out .= html_writer::link($index,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

                    $out .= html_writer::start_tag('div',array('class' => 'user_content'));
                        $out .= html_writer::start_tag('table');
                            // Add header
                            $out .= self::add_header_screen_table();
                            // Add content
                            $out .= self::add_content_screen_table($user_report->data);
                        $out .= html_writer::end_tag('table');
                    $out .= html_writer::end_tag('div');//outcome_content
                }else {
                    // Non data
                    $out     = '</h3>' . get_string('no_data', 'report_manager') . '</h3>';
                }//if_data
            $out .= html_writer::end_div();//outcome_rpt_div

            // Return back/selection page
            $out .= html_writer::link($return,get_string('user_return_to_selection','report_manager'),array('class' => 'link_return'));
            $out .= html_writer::link($index,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

            $out .= '<hr class="line_rpt_lnk">';

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//print_user_report_screen

    public static function download_user_report($report) {
        /* Variables */
        global $CFG;
        $row        = 0;
        $time       = null;
        $name       = null;
        $filename   = null;
        $export     = null;
        $my_xls     = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            $time = userdate(time(),'%d.%m.%Y', 99, false);
            // Build name
            $name = $report->zero;
            if ($report->one) {
                $name .= '_' . $report->one;
            }
            if ($report->two) {
                $name .= '_' . $report->two;
            }
            if ($report->three) {
                $name .= '_' . $report->three;
            }
            $filename = clean_filename(get_string('user_report','report_manager') . '_' . $name . '_' . $time . ".xls");

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");

            // Sending HTTP headers
            $export->send($filename);

            // Sheet
            $my_xls = $export->add_worksheet($name);

            // Add Header
            self::add_header_excel($my_xls,$row);
            $row ++;
            // Add content
            self::add_content_excel($my_xls,$row,$report->data);

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//download_user_report

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Add header to the excel
     *
     * @param               $my_xls
     * @param       integer $row
     *
     * @throws              Exception
     *
     * @creationDate        25/05/2017
     * @author              eFaktor     (fbv)
     */
    private static function add_header_excel($my_xls,$row) {
        /* Variables */
        $col            = 0;
        $strZero        = get_string('select_company_structure_level', 'report_manager', 0);
        $strOne         = get_string('select_company_structure_level', 'report_manager', 1);
        $strTwo         = get_string('select_company_structure_level', 'report_manager', 2);
        $strThree       = get_string('select_company_structure_level', 'report_manager', 3);
        $strLast        = get_string('lastname');
        $strFirst       = get_string('firstname');
        $strCourse      = get_string('course');
        $strGender      = get_string('rpt_gender','report_manager');
        $strRole        = get_string('role');
        $strProduce     = get_string('rpt_produce','report_manager');
        $strFormat      = get_string('format');
        $strStart       = get_string('startdate');
        $strCompletion  = get_string('completed','report_manager');
        $strStatus      = get_string('status','report_manager');
        $strDay         = get_string('rpt_days','report_manager');
        $strVisible     = get_string('visible');
        $strEnrolled    = get_string('rpt_enrolled','report_manager');

        try {
            // Level Zero
            $my_xls->write($row, $col, $strZero,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            // Level one
            $col += 3;
            $my_xls->write($row, $col, $strOne,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            // Level two
            $col += 3;
            $my_xls->write($row, $col, $strTwo,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            // Level Three
            $col += 3;
            $my_xls->write($row, $col, $strThree,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+3);
            $my_xls->set_row($row,20);

            // Last name
            $col += 4;
            $my_xls->write($row, $col, $strLast,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            // First name
            $col += 3;
            $my_xls->write($row, $col, $strFirst,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            // Gender
            $col += 3;
            $my_xls->write($row, $col, $strGender,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col);
            $my_xls->set_row($row,20);

            // Role
            $col += 1;
            $my_xls->write($row, $col, $strRole,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Produce by
            $col += 2;
            $my_xls->write($row, $col, $strProduce,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Course
            $col += 2;
            $my_xls->write($row, $col, $strCourse,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            // Course Format
            $col += 3;
            $my_xls->write($row, $col, $strFormat,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Start date
            $col += 2;
            $my_xls->write($row, $col, $strStart,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Enrolled
            $col += 2;
            $my_xls->write($row, $col, $strEnrolled,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Completion date
            $col += 2;
            $my_xls->write($row, $col, $strCompletion,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Status
            $col += 2;
            $my_xls->write($row, $col, $strStatus,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Visible
            $col += 2;
            $my_xls->write($row, $col, $strVisible,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#c4ccd9','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_excel

    private static function add_content_excel($my_xls,$row,$data) {
        /* Variables */
        $col    = 0;
        $gender     = null;
        $start      = null;
        $enrolled   = null;
        $completed  = null;
        $status     = null;
        $visible    = null;

        try {
            // Add the content
            foreach ($data as $info) {
                // Level Zero
                $my_xls->write($row, $col, $info->zeroname,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                // Level one
                $col += 3;
                $my_xls->write($row, $col, $info->onename,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                // Level two
                $col += 3;
                $my_xls->write($row, $col, $info->twoname,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                // Level Three
                $col += 3;
                $my_xls->write($row, $col, $info->threename,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+3);
                $my_xls->set_row($row,20);

                // Last name
                $col += 4;
                $my_xls->write($row, $col, $info->lastname,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                // First name
                $col += 3;
                $my_xls->write($row, $col, $info->firstname,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                // Gender
                $col += 3;
                if ($info->gender == 1) {
                    $gender = get_string('rpt_male','report_manager');
                }else if ($info->gender == 2) {
                    $gender = get_string('rpt_female','report_manager');
                }else {
                    $gender = '';
                }
                $my_xls->write($row, $col, $gender,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col);
                $my_xls->set_row($row,20);

                // Role
                $col += 1;
                $my_xls->write($row, $col, $info->role,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                // Produce by
                $col += 2;
                $my_xls->write($row, $col, $info->producedby,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                // Course
                $col += 2;
                $my_xls->write($row, $col, $info->fullname,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                // Course Format
                $col += 3;
                $my_xls->write($row, $col, $info->format,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                // Start date
                $col += 2;
                $start = ($info->startdate ? userdate($info->startdate ,'%d.%m.%Y', 99, false) : '');
                $my_xls->write($row, $col, $start,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                // Enrolled
                $col += 2;
                $enrolled = ($info->enrolleddata ? userdate($info->enrolleddata ,'%d.%m.%Y', 99, false) : '');
                $my_xls->write($row, $col, $enrolled,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                // Completion date
                $col += 2;
                $completed = ($info->timecompleted ? userdate($info->timecompleted ,'%d.%m.%Y', 99, false) : '');
                $my_xls->write($row, $col, $completed,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                // Status
                $col += 2;
                $status = ($info->timecompleted ? get_string('completed','report_manager') : get_string('progress','report_manager'));
                $my_xls->write($row, $col, $status,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                // Visible
                $col += 2;
                $visible = ($info->visible ? get_string('yes'): get_string('no'));
                $my_xls->write($row, $col, $visible,array('size'=>12, 'name'=>'Arial','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col);
                $my_xls->set_row($row,20);

                // New row
                $row ++;
                $col = 0;
            }//for
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_content_excel

     /**
     * Description
     * Add the content to the table
     *
     * @param       Object  $data
     *
     * @return              null|string
     * @throws              Exception
     *
     * @creationDate    25/05/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_content_screen_table($data) {
        /* Variables */
        $content        = null;
        $strCompany     = null;
        $strUser        = null;
        $strCourse      = null;
        $strCompletion  = null;
        $strStatus      = null;
        $color          = null;

        try {
            // Headers
            $strCompany     = get_string('company','report_manager');
            $strUser        = get_string('user');
            $strCourse      = get_string('course');
            $strCompletion  = get_string('completed','report_manager');
            $strStatus      = get_string('status','report_manager');

            // Add the content
            foreach ($data as $info) {
                $class = '';
                if ($color == 'r2') {
                    $color = 'rcolor';
                }else {
                    $color = 'r2';
                }
                $class .= ' ' . $color;
                $content .= html_writer::start_tag('tr',array('class' => $class));
                    // Company
                    $content .= html_writer::start_tag('td',array('class' => 'company','data-th' => $strCompany));
                        $content .= $info->zeroname . '/' . $info->onename . '/' . $info->twoname . '/' . $info->threename;
                    $content .= html_writer::end_tag('td');
                    // User
                    $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strUser));
                        $content .= $info->lastname . ' ' . $info->firstname;
                    $content .= html_writer::end_tag('td');
                    // Course
                    $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strCourse));
                        $content .= $info->fullname;
                    $content .= html_writer::end_tag('td');
                    // Completion
                    $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strCompletion));
                        $content .= ($info->timecompleted ? userdate($info->timecompleted,'%d.%m.%Y', 99, false) : '');
                    $content .= html_writer::end_tag('td');
                    // Status
                    $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strStatus));
                        $content .= ($info->timecompleted ? get_string('completed','report_manager') : get_string('progress','report_manager'));
                    $content .= html_writer::end_tag('td');
                $content .= html_writer::end_tag('tr');
            }//for_each

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_content_screen_table

    /**
     * Description
     * Add the header to the table
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    25/05/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_header_screen_table() {
        /* Variables */
        $header         = null;
        $strCompany     = null;
        $strUser        = null;
        $strCourse      = null;
        $strCompletion  = null;
        $strStatus      = null;

        try {
            // Headers
            $strCompany     = get_string('company','report_manager');
            $strUser        = get_string('user');
            $strCourse      = get_string('course');
            $strCompletion  = get_string('completed','report_manager');
            $strStatus      = get_string('status','report_manager');

            // Build the header
            $header .= html_writer::start_tag('tr',array('class' => 'header'));
                // Company
                $header .= html_writer::start_tag('th',array('class' => 'company'));
                    $header .= $strCompany;
                $header .= html_writer::end_tag('th');
                // User
                $header .= html_writer::start_tag('th',array('class' => 'info'));
                    $header .= $strUser;
                $header .= html_writer::end_tag('th');
                // Course
                $header .= html_writer::start_tag('th',array('class' => 'info'));
                    $header .= $strCourse;
                $header .= html_writer::end_tag('th');
                // Completion
                $header .= html_writer::start_tag('th',array('class' => 'status'));
                    $header .= $strCompletion;
                $header .= html_writer::end_tag('th');
                // Status
                $header .= html_writer::start_tag('th',array('class' => 'status'));
                    $header .= $strStatus;
                $header .= html_writer::end_tag('th');
            $header .= html_writer::end_tag('tr');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_screen_table
}//User Report