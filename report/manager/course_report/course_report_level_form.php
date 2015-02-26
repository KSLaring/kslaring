<?php

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once('../locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/manager/js/libdev.js');

/* Course Report Level - Form  */
class manager_course_report_level_form extends moodleform {
    function definition() {
        /* General Settings */
        $level_select_attr = array(
            'class' => REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL,
            'size' => '5'
        );
        $button_array_attr = array(
            'class' => 'submit-btn'
        );

        $m_form = $this->_form;
        list($report_level) = $this->_customdata;

        /* Course List */
        $m_form->addElement('header', 'course', get_string('course'));
        $m_form->addElement('html', '<div class="level-wrapper">');
            $m_form->addElement('select',
                                REPORT_MANAGER_COURSE_LIST,
                                get_string('select_course_to_report', 'report_manager'),
                                report_manager_get_course_list(),
                                'onchange=saveCourse("course_list")');
        if (isset($_COOKIE['courseReport'])) {
            $m_form->setDefault(REPORT_MANAGER_COURSE_LIST,$_COOKIE['courseReport']);
        }//if_cookie
        $m_form->addElement('html', '</div>');

        /* Levels */
        $m_form->addElement('header', 'company', get_string('company', 'report_manager'));
        $m_form->setExpanded('company',true);
        for ($i = 1; $i <= $report_level; $i++) {
            /* Attributes */
            $name_select = REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL . $i;
            $title = get_string('select_company_structure_level', 'report_manager', $i);
            $event = $this->getEvent($i,$report_level);

            /* List */
            $options = $this->getCompanies_Level($i);

            /* Select */
            $m_form->addElement('html', '<div class="level-wrapper">');
                $select = &$m_form->addElement('select',$name_select, $title,$options,$event);
                /* Multiple Selection - Level 3 */
                if ($i == 3) {
                    $select->setMultiple(true);
                    $select->setSize(10);
                    $m_form->addElement('html', '<p class="helptext">' . get_string('help_multi_select', 'report_manager') . '</p>');
                }
            $m_form->addElement('html', '</div>');

            /* Default Values */
            $m_form->setDefault($name_select,$this->getDefault_Value($i));

            /* Disabled Selects */
            if ($i >1) {
                $name_parent = REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL . ($i-1);
                $m_form->disabledIf($name_select,$name_parent,'eq',0);
            }//if_>_1
        }//for

        /* Job Role */
        $m_form->addElement('header', 'job_role', get_string('job_role', 'report_manager'));
        $m_form->setExpanded('job_role',true);
        $m_form->addElement('html', '<div class="level-wrapper">');
            $select =& $m_form->addElement('select',
                                           REPORT_MANAGER_JOB_ROLE_LIST,
                                           get_string('select_job_role', 'report_manager'),
                                           report_manager_get_job_role_list(),
                                           $level_select_attr);

            $select->setMultiple(true);
            $m_form->addElement('html', '<p class="helptext">' . get_string('help_multi_select', 'report_manager') . '</p>');
        $m_form->addElement('html', '</div>');

        /* Reports */
        $m_form->addElement('header', 'report', get_string('report'));
        $m_form->setExpanded('report',true);
        $m_form->addElement('html', '<div class="level-wrapper">');
            $m_form->addElement('select',
                                REPORT_MANAGER_COMPLETED_LIST,
                                get_string('completed_list', 'report_manager'),
                                report_manager_get_completed_list());
            $m_form->setDefault(REPORT_MANAGER_COMPLETED_LIST, 4);

            /* Format Report */
            $m_form->addElement('select',
                                REPORT_MANAGER_REPORT_FORMAT_LIST,
                                get_string('report_format_list', 'report_manager'),
                                report_manager_get_report_format_list());
        $m_form->addElement('html', '</div>');

        /* */
        $m_form->addElement('hidden','rpt');
        $m_form->setDefault('rpt',$report_level);
        $m_form->setType('rpt',PARAM_INT);

        $this->add_action_buttons(true, get_string('create_report', 'report_manager'));
    }//definition

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        /* Check Course */
        if (!$data[REPORT_MANAGER_COURSE_LIST]) {
            $errors[REPORT_MANAGER_COURSE_LIST] = get_string('missing_course','report_manager');
        }//if_course_list

        /* Check Level Company */
        if (!$data[REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL . '1']) {
            $errors[REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL .'1'] = get_string('missing_level','report_manager');
        }else if ($data['rpt']>1){
            if (!$data[REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL . '2']) {
                $errors[REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL .'2'] = get_string('missing_level','report_manager');
            }
        }//if_company_level

        return $errors;
    }//validation

    /**
     * @param           $parent_level       Parent Level
     * @param           $report_level       Report Level
     * @return          string
     *
     * @creationDate    14/09/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the function It must be called with onchange event is triggered
     */
    function getEvent($parent_level,$report_level) {
        $str_event = '';

        /* Select Event */
        switch ($report_level) {
            case 2:
                if ($parent_level == 1) {
                    $str_event .= 'onchange=GetLevelTwo("company_structure_level1");';
                }
                break;
            case 3:
                switch ($parent_level) {
                    case 1:
                        $str_event .= 'onchange=GetLevelTwo("company_structure_level1");';
                        break;
                    case 2:
                        $str_event .= 'onchange=GetLevelTree("company_structure_level2");';
                        break;
                }//switch_parent
                break;
        }//switch

        return $str_event;
    }//getEvent

    /**
     * @param           $level      Level
     * @return          array
     *
     * @creationDate    14/09/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return a list of all companies that are connected with a level.
     */
    function getCompanies_Level($level) {
        /* Companies List */
        $options = array();

        /* Get parent */
        switch ($level) {
            case 1:
                $options = report_manager_get_level_list($level);
                break;
            case 2:
                if (isset($_COOKIE['parentLevelOne'])) {
                    $options = report_manager_get_level_list($level,$_COOKIE['parentLevelOne']);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE
                break;
            case 3:
                if (isset($_COOKIE['parentLevelTwo'])) {
                    $options = report_manager_get_level_list($level,$_COOKIE['parentLevelTwo']);
                }//IF_COOKIE
                break;
        }//switch

        return $options;
    }//getParent_Company


    function getDefault_Value($level){
        $str_value = 0;

        /* Get Default Value */
        switch ($level) {
            case 1:
                if (isset($_COOKIE['parentLevelOne']) && isset($_COOKIE['parentLevelOne']) != 0) {
                    $str_value = $_COOKIE['parentLevelOne'];
                }
                break;
            case 2:
                if (isset($_COOKIE['parentLevelTwo'])) {
                    $str_value = $_COOKIE['parentLevelTwo'];
                }
                break;
            case 3:
                    $str_value = -1;
                break;
        }//switch

        return $str_value;
    }//setDefault_Value
}//manager_course_report_level_form
