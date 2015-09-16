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
                /* Title*/
                $content .= html_writer::start_div('summary_content');
                    $content .= '<label class="title">' . get_string('home_prerequisities','format_classroom_frikomport')  . '</label>';
                $content .= html_writer::end_div();//summary_content
                /* Value    */
                if (array_key_exists('prerequisities',$formatOptions)) {
                    if ($formatOptions['prerequisities']) {
                        $preReq = $formatOptions['prerequisities'];
                    }//if_prerequisites

                    $content .= '<label class="value">' . $preReq . '</label>';
                }//if_prerequisites
                $content .= html_writer::end_div();//summary_content

                /* Produced By          */
                /* Title    */
                $content .= html_writer::start_div('summary_content');
                    $content .= '<label class="title">' . get_string('home_producedby','format_classroom_frikomport')  . '</label>';
                $content .= html_writer::end_div();//summary_content
                /* Value    */
                $content .= html_writer::start_div('summary_content');
                    if (array_key_exists('producedby',$formatOptions)) {
                        if ($formatOptions['producedby']) {
                            $prodBy = $formatOptions['producedby'];
                        }//if_producedBy

                        $content .= '<label class="value">' . $prodBy . '</label>';
                    }//if_prerequisites
                $content .= html_writer::end_div();//summary_content

                /* Coordinator          */
                /* Title    */
                $content .= html_writer::start_div('summary_content');
                    $content .= '<label class="title">' . get_string('home_coordinater','local_course_page')  . '</label>';
                $content .= html_writer::end_div();//summary_content
                /* Value    */
                $content .= html_writer::start_div('summary_content');
                    if ((array_key_exists('manager',$formatOptions)) && $formatOptions['manager']) {
                        /* Get Manager */
                        $manager = self::GetManagerName($formatOptions['manager']);
                        $content .= '<label class="value">' . $manager . '</label>';
                    }//if_prerequisites
                $content .= html_writer::end_div();//summary_content

                /* Location             */
                /* Title    */
                $content .= html_writer::start_div('summary_content');
                    $content .= '<label class="title">' . get_string('home_title_location','format_classroom_frikomport')  . '</label>';
                $content .= html_writer::end_div();//summary_content
                /* Value    */
                $content .= html_writer::start_div('summary_content');
                    if (array_key_exists('course_location',$formatOptions)) {
                        /* Get Location Name    */
                        if ($formatOptions['course_location']) {
                            $location = self::GetLocationName($formatOptions['course_location']);
                        }//if_location

                        $content .= '<label class="value">' . $location . '</label>';
                    }//if_prerequisites
                $content .= html_writer::end_div();//summary_content

                /* Sectors              */
                /* Title    */
                $content .= html_writer::start_div('summary_content');
                    $content .= '<label class="title">' . get_string('home_title_sector','format_classroom_frikomport')  . '</label>';
                $content .= html_writer::end_div();//summary_content
                /* Value    */
                $content .= html_writer::start_div('summary_content');
                    if (array_key_exists('course_sector',$formatOptions)) {
                        /* Get Sectors Name    */
                        if ($formatOptions['course_sector']) {
                            $sectors = self::GetSectorsName($formatOptions['course_sector']);
                        }//if_sectors

                        $content .= '<label class="value">' . str_replace(',','</br>',$sectors) . '</label>';
                    }//if_prerequisites
                $content .= html_writer::end_div();//summary_content

                /* Time From - To       */
                /* Title    */
                $content .= html_writer::start_div('summary_content');
                    $content .= '<label class="title">' . get_string('home_time_from_to','format_classroom_frikomport')  . '</label>';
                $content .= html_writer::end_div();//summary_content
                /* Value    */
                $content .= html_writer::start_div('summary_content');
                    if (array_key_exists('time',$formatOptions)) {
                        if ($formatOptions['time']) {
                            $fromTo = str_replace(',','</br>',$formatOptions['time']);
                        }//if_time
                        $content .= '<label class="value">' . $fromTo . '</label>';
                    }//if_prerequisites
                $content .= html_writer::end_div();//summary_content

                /* Estimated Time Spent */
                /* Title    */
                $content .= html_writer::start_div('summary_content');
                    $content .= '<label class="title">' . get_string('home_length','format_classroom_frikomport')  . '</label>';
                $content .= html_writer::end_div();//summary_content
                /* Value    */
                $content .= html_writer::start_div('summary_content');
                    if (array_key_exists('length',$formatOptions)) {
                        if ($formatOptions['length']) {
                            $length = $formatOptions['length'];
                        }//if_time
                        $content .= '<label class="value">' . $length . '</label>';
                    }//if_prerequisites
                $content .= html_writer::end_div();//summary_content

                /* Estimated Effort     */
                /* Title    */
                $content .= html_writer::start_div('summary_content');
                    $content .= '<label class="title">' . get_string('home_effort','format_classroom_frikomport')  . '</label>';
                $content .= html_writer::end_div();//summary_content
                /* Value    */
                $content .= html_writer::start_div('summary_content');
                    if (array_key_exists('effort',$formatOptions)) {
                        if ($formatOptions['effort']) {
                            $effort = $formatOptions['effort'];
                        }//if_time
                        $content .= '<label class="value">' . $effort . '</label>';
                    }//if_prerequisites
                $content .= html_writer::end_div();//summary_content
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