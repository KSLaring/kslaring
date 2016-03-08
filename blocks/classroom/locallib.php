<?php
/**
 * Classroom Course Format Block - Library
 *
 * @package         block
 * @subpackage      classroom
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    15/09/2015
 * @author          efaktor     (fbv)
 */

class ClassroomBlock {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    /**
     * @param           $courseId
     * @return          string
     * @throws          Exception
     *
     * @creationDate    15/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the classroom content course to the block
     */
    public static function GetContentBlock($courseId) {
        /* Variables    */
        $content = '';
        $formatOptions  = null;
        $manager        = null;
        $urlHomePage    = null;
        $location       = ' - ';
        $sectors        = ' - ';
        $fromTo         = ' - ';
        $preReq         = ' - ';
        $prodBy         = ' - ';
        $length         = ' - ';
        $effort         = ' - ';

        try {
            /* Get Format Options   */
            $formatOptions = course_get_format($courseId)->get_format_options();

            /* Add Options      */
            $content .= html_writer::start_div('summary');
                /* Prerequisites        */
                if (array_key_exists('prerequisities',$formatOptions)) {
                    if ($formatOptions['prerequisities']) {
                        /* Title*/
                        $content .= html_writer::start_div('summary_content');
                            $content .= '<label class="title">' . get_string('home_prerequisities','format_classroom_frikomport')  . '</label>';
                        $content .= html_writer::end_div();//summary_content
                        /* Value    */
                        $content .= html_writer::start_div('summary_content');
                            $preReq = $formatOptions['prerequisities'];
                            $content .= '<label class="value">' . $preReq . '</label>';
                        $content .= html_writer::end_div();//summary_content
                    }//if_prerequisities
                }//if_pre_requisites

                /* Produced By          */
                if (array_key_exists('producedby',$formatOptions)) {
                    if ($formatOptions['producedby']) {
                        /* Title    */
                        $content .= html_writer::start_div('summary_content');
                            $content .= '<label class="title">' . get_string('home_producedby','format_classroom_frikomport')  . '</label>';
                        $content .= html_writer::end_div();//summary_content
                        /* Value    */
                        $content .= html_writer::start_div('summary_content');
                            $prodBy = $formatOptions['producedby'];
                            $content .= '<label class="value">' . $prodBy . '</label>';
                        $content .= html_writer::end_div();//summary_content
                    }//if_produced_by
                }//if_produced_by

                /* Coordinator          */
                if ((array_key_exists('manager',$formatOptions)) && $formatOptions['manager']) {
                    /* Title    */
                    $content .= html_writer::start_div('summary_content');
                        $content .= '<label class="title">' . get_string('home_coordinater','local_course_page')  . '</label>';
                    $content .= html_writer::end_div();//summary_content
                    /* Value    */
                    $content .= html_writer::start_div('summary_content');
                        /* Get Manager */
                        $manager = self::GetManagerName($formatOptions['manager']);
                        $content .= '<label class="value">' . $manager . '</label>';
                    $content .= html_writer::end_div();//summary_content
                }//if_manager

                /* Location             */
                if (array_key_exists('course_location',$formatOptions)) {
                    if ($formatOptions['course_location']) {
                        /* Title    */
                        $content .= html_writer::start_div('summary_content');
                            $content .= '<label class="title">' . get_string('home_title_location','format_classroom_frikomport')  . '</label>';
                        $content .= html_writer::end_div();//summary_content
                        /* Value    */
                        $content .= html_writer::start_div('summary_content');
                            /* Get Location Name    */
                            $location = self::GetLocationName($formatOptions['course_location']);
                            $content .= '<label class="value">' . $location . '</label>';
                        $content .= html_writer::end_div();//summary_content
                    }//if_location
                }//if_location

                /* Sectors              */
                if (array_key_exists('course_sector',$formatOptions)) {
                    if ($formatOptions['course_sector']) {
                        /* Title    */
                        $content .= html_writer::start_div('summary_content');
                            $content .= '<label class="title">' . get_string('home_title_sector','format_classroom_frikomport')  . '</label>';
                        $content .= html_writer::end_div();//summary_content
                        /* Value    */
                        $content .= html_writer::start_div('summary_content');
                            /* Get Sectors Name    */
                            $sectors = self::GetSectorsName($formatOptions['course_sector']);
                            $content .= '<label class="value">' . str_replace(',','</br>',$sectors) . '</label>';
                        $content .= html_writer::end_div();//summary_content
                    }//if_course_sector
                }//if_course_sector

                /* Time From - To       */
                if (array_key_exists('time',$formatOptions)) {
                    if ($formatOptions['time']) {
                        /* Title    */
                        $content .= html_writer::start_div('summary_content');
                            $content .= '<label class="title">' . get_string('home_time_from_to','format_classroom_frikomport')  . '</label>';
                        $content .= html_writer::end_div();//summary_content
                        /* Value    */
                        $content .= html_writer::start_div('summary_content');
                            $fromTo = str_replace(',','</br>',$formatOptions['time']);
                            $content .= '<label class="value">' . $fromTo . '</label>';
                        $content .= html_writer::end_div();//summary_content
                    }//if_time
                }//if_time

                /* Estimated Time Spent */
                if (array_key_exists('length',$formatOptions)) {
                    if ($formatOptions['length']) {
                        /* Title    */
                        $content .= html_writer::start_div('summary_content');
                            $content .= '<label class="title">' . get_string('home_length','format_classroom_frikomport')  . '</label>';
                        $content .= html_writer::end_div();//summary_content
                        /* Value    */
                        $content .= html_writer::start_div('summary_content');
                            $length = $formatOptions['length'];
                            $content .= '<label class="value">' . $length . '</label>';
                        $content .= html_writer::end_div();//summary_content
                    }//if_length
                }//if_length

                /* Estimated Effort     */
                if (array_key_exists('effort',$formatOptions)) {
                    if ($formatOptions['effort']) {
                        /* Title    */
                        $content .= html_writer::start_div('summary_content');
                            $content .= '<label class="title">' . get_string('home_effort','format_classroom_frikomport')  . '</label>';
                        $content .= html_writer::end_div();//summary_content
                        /* Value    */
                        $content .= html_writer::start_div('summary_content');
                            $effort = $formatOptions['effort'];
                            $content .= '<label class="value">' . $effort . '</label>';
                        $content .= html_writer::end_div();//summary_content
                    }//if_effort
                }//if_effort

            /* Add Link Course Home Page    */
            if (array_key_exists('homepage',$formatOptions)) {
                if ($formatOptions['homepage']) {
                    /* Check if it's visible    */
                    if (array_key_exists('homevisible',$formatOptions)) {
                        if ($formatOptions['homevisible']) {
                            $urlHomePage = new moodle_url('/local/course_page/home_page.php',array('id' => $courseId,'start' => 0));
                            /* Title    */
                            $content .= html_writer::start_div('summary_content');
                                $content .= '<a href="' . $urlHomePage . '">' . get_string('home_page','local_course_page') . '</a>';
                            $content .= html_writer::end_div();//summary_content
                        }//if_homevisible
                    }//if_homevisible
                }//if_homepage
            }//if_hompeage
            $content .= html_writer::end_div();//summary

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetContentBlock


    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @param           $managerId
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    15/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the manager name
     */
    private static function GetManagerName($managerId) {
        /* Variables    */
        global $DB;
        $params = null;
        $rdo    = null;
        $name   = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['id']       = $managerId;
            $params['deleted']  = 0;

            /* Execute  */
            $rdo = $DB->get_record('user',$params,'firstname,lastname');
            if ($rdo) {
                $name = $rdo->firstname . ' '  .$rdo->lastname;
                $name = trim($name);

                return $name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetManagerName

    /**
     * @param           $locationId
     * @return          null
     * @throws          Exception
     *
     * @creationDate    15/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the location name
     */
    private static function GetLocationName($locationId) {
        /* Variables    */
        global $DB;
        $rdo = null;

        try {
            /* Execute  */
            $rdo = $DB->get_record('course_locations',array('id' => $locationId),'name');
            if ($rdo) {
                return $rdo->name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetLocationName

    /**
     * @param           $sectorsLst
     * @return          null
     * @throws          Exception
     *
     * @creationDate    15/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the sectors name
     */
    private static function GetSectorsName($sectorsLst) {
        /* Variables    */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $sectorsName    = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		GROUP_CONCAT(DISTINCT CONCAT(rgc.industrycode,' - ', rgc.name) ORDER BY rgc.industrycode, rgc.name SEPARATOR ', ') as 'sectors'
                     FROM		{report_gen_companydata}	rgc
                     WHERE      rgc.id IN ($sectorsLst) ";

            /* Execute*/
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                $sectorsName = $rdo->sectors;
            }//if_rdo

            return $sectorsName;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetSectorsName

}//ClassroomBlock