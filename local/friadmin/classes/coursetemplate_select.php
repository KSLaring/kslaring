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

//namespace local_friadmin;

defined('MOODLE_INTERNAL') || die;

//use renderable;
//use renderer_base;
//use stdClass;

/**
 * Class containing data for the local_friadmin course_template selection area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_coursetemplate_select extends local_friadmin_widget implements renderable {

    // The form feeback - for debugging
    protected $formdatadump = null;

    // The course creation process result
    protected $coursecreationresult = null;

    // The Moodle form
    protected $mform = null;

    // The returned form data
    protected $fromform = null;

    // The id of the created course
    protected $newcourseid = null;

    /**
     * Construct the courselist_page renderable.
     *
     * @updateDate  23/06/2015
     * @author      eFaktor (fbv)
     *
     * Description
     * Add Exception
     * Clean code
     */
    public function __construct() {
        /* Variables    */
        $customdata = null;

        try {
            // Create the data object and set the first values
            parent::__construct();

            /* custom data used */
            $customdata = $this->get_popup_data();

            /* Create form  */
            $mform          = new local_friadmin_coursetemplate_select_form(null, $customdata, 'post','', array('id' => 'mform-coursetemplate-select'));
            $this->mform    = $mform;

            /* Collect the input data and create the new course */
            if ($fromform = $mform->get_data()) {
                $this->fromform = $fromform;

                $this->formdatadump = '<div class="form-data"><h4>Form data</h4><pre>' . var_export($fromform, true) . '</pre></div>';

                /* Create the course    */
                $this->coursecreationresult = $this->create_course();
            }//if_get_data
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//construct

    /**
     * Get the returned form data, if any
     */
    public function get_fromform() {
        return $this->fromform;
    }

    /**
     * Get the form formdatadump
     */
    public function get_formdatadump() {
        return $this->formdatadump;
    }

    /**
     * Get the new courseid
     */
    public function get_newcourseid() {
        return $this->newcourseid;
    }

    /**
     * Render the form and set the data
     */
    public function render() {
        if (is_null($this->coursecreationresult)) {
            $this->data->content = $this->mform->render();
        } else {
            $this->data->content = $this->coursecreationresult;
        }
    }

    /**
     * Set the default form values
     *
     * The associative $defaults: array 'elementname' => 'defaultvalue'
     *
     * @param Array $defaults The default values
     */
    public function set_defaults($defaults = array()) {
        $this->mform->set_defaults($defaults);
    }

    /**
     * Get the user managed categories and the template list
     *
     * @return      array       An array with the two popup lists
     * @throws      Exception
     *
     * @updateDate  23/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add exception
     * clean code
     * rename variables
     */
    protected function get_popup_data() {
        /* Variables    */
        global $CFG;
        $result             = null;
        $pluginInfo         = null;
        $templateCatId      = null;
        $courseCat          = null;
        $templateCourses    = null;

        try {
            require_once($CFG->libdir . '/coursecatlib.php');

            /* Plugin Info      */
            $pluginInfo = get_config('local_friadmin');

            /* Result Structure */
            $result = array('categories' => array(),
                            'templates' => array()
                           );

            /* Get Categories   */
            $result['categories'] = coursecat::make_categories_list('moodle/category:manage');

            /* Fill the data    */
            if ($pluginInfo) {
                if (isset($pluginInfo->template_category) && ($pluginInfo->template_category)) {
                    /* Get Template Category && Course Category */
                    $templateCatId = $pluginInfo->template_category;
                    $courseCat     = coursecat::get($templateCatId, MUST_EXIST, true);

                    /* Get Courses category */
                    $templateCourses = $courseCat->get_courses();

                    /* Add to result Structure  */
                    foreach ($templateCourses as $templateCo) {
                        if ($templateCo->visible) {
                            $result['templates'][$templateCo->shortname] = $templateCo->fullname;
                        }//if_visible
                    }//for_templatesCourse
                }//if_template_category
            }//if_pluginInfo

            return $result;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_popup_data

    /**
     * Create the course with the given data
     *
     * Use uplaodcourse functionality to create a course from CSV data,
     * see tool/uploadcourse/cli/uploadcourse.php
     */
    /**
     * @return          array|string
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * @updateDate      23/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Rebuild function.
     *  - Add Exception
     *  - Check if the user has the correct permissions
     *  - Create a Fake Permission before create the new course from the template and remove later.
     *  - Errors is an array. So, it has to be converted into a string
     */
    protected function create_course() {
        /* Variables    */
        global $DB,$CFG;
        $result             = false;
        $fakePermission     = null;
        $courseConfig       = null;
        $options            = null;
        $processorOptions   = null;
        $processor          = null;
        $defaults           = array();
        $templateFormat     = null;
        $content            = null;
        $importId           = null;
        $cir                = null;
        $readCount          = null;
        $newCourse          = null;
        $info               = null;
        $errorMsg           = null;

        try {

            /* Course Config    */
            $courseConfig = get_config('moodlecourse');

            /* Set the course generation options */
            $options = array('mode'                 => tool_uploadcourse_processor::MODE_CREATE_NEW,
                             'updatemode'           => tool_uploadcourse_processor::UPDATE_NOTHING,
                             'file'                 => '',
                             'delimiter'            => 'comma',
                             'encoding'             => 'UTF-8',
                             'shortnametemplate'    => '',
                             'templatecourse'       => $this->fromform->seltemplate,
                             'restorefile'          => false,
                             'allowdeletes'         => false,
                             'allowrenames'         => false,
                             'allowresets'          => false,
                             'reset'                => false,
                             'category'             => $this->fromform->selcategory
                            );

            /* Set processor options */
            $processorOptions = array('mode'                => $options['mode'],
                                      'updatemode'          => $options['updatemode'],
                                      'allowdeletes'        => $options['allowdeletes'],
                                      'allowrenames'        => $options['allowrenames'],
                                      'allowresets'         => $options['allowresets'],
                                      'reset'               => $options['reset'],
                                      'shortnametemplate'   => $options['shortnametemplate'],
                                      'templatecourse'      => $options['templatecourse']
                                     );

            /* Set the course default values */
            $defaults['category']       = $options['category'];
            $defaults['startdate']      = time() + 3600 * 24;
            $defaults['newsitems']      = $courseConfig->newsitems;
            $defaults['showgrades']     = $courseConfig->showgrades;
            $defaults['showreports']    = $courseConfig->showreports;
            $defaults['maxbytes']       = $courseConfig->maxbytes;
            $defaults['legacyfiles']    = $CFG->legacyfilesinnewcourses;
            $defaults['groupmode']      = $courseConfig->groupmode;
            $defaults['groupmodeforce'] = $courseConfig->groupmodeforce;
            $defaults['visible']        = $courseConfig->visible;
            $defaults['lang']           = $courseConfig->lang;

            /* Set the course format to the same format as the template */
            $templateFormat = $DB->get_field('course','format',array('shortname' => $this->fromform->seltemplate),MUST_EXIST);
            if ($templateFormat) {
                $defaults['format'] = $templateFormat;
            }//if_templateFormat

            /* Create the course from CSV data                  */
            /* Create the CSV: line 1: field names, line2: data */
            $content  = 'shortname,fullname,category,templatecourse' . "\n";
            $content .= $this->fromform->selshortname   . ',';
            $content .= $this->fromform->selfullname    . ',';
            $content .= $this->fromform->selcategory    . ',';
            $content .= $this->fromform->seltemplate;

            $importId   = csv_import_reader::get_new_iid('uploadcourse');
            $cir        = new csv_import_reader($importId, 'uploadcourse');
            $readCount  = $cir->load_csv_content($content, $options['encoding'], $options['delimiter']);
            unset($content);
            if ($readCount === false) {
                print_error('csvfileerror', 'tool_uploadcourse', '', $cir->get_error());
            } else if ($readCount == 0) {
                print_error('csvemptyfile', 'error', '', $cir->get_error());
            }//if_readCount

            /* First, it checks if the user has the correct permissions */
            if (!self::HasCorrectPermissions()) {
                /* Create a Fake Permission */
                $fakePermission     = new stdClass();
                $fakePermission->id = self::Add_FakePermission_To_User();
            }//if_Has_not_permissions

            /* Process the creation of new course form the template */
            $processor = new tool_uploadcourse_processor($cir, $processorOptions, $defaults);
            $processor->execute(new tool_uploadcourse_tracker(tool_uploadcourse_tracker::NO_OUTPUT));
            $errors = $processor->get_errors();
            if (!$errors) {
                /* Get Info new course  */
                $newCourse = $DB->get_record('course',array('shortname' => $this->fromform->selshortname), '*', MUST_EXIST);
                if ($newCourse) {
                    $this->newcourseid = $newCourse->id;
                    $info = array('id' => $newCourse->id,
                                  'shortname' => $newCourse->shortname,
                                  'fullname' => $newCourse->fullname
                    );

                    $result  = '<p class="result">';
                    $result .= get_string('coursetemplate_result', 'local_friadmin', $info) . '</p>';
                }else {
                    /* The course should exist when no processor errors had been generated */
                    $result  = '<p class="result">';
                    $result .= get_string('coursetemplate_error', 'local_friadmin') . '</p>';
                }//if_else_new_course
            }else {
                /* Convert to string */
                foreach($errors as $code=>$msg) {
                    $errorMsg .= $msg . "</br>";
                }//for_Errors

                /* Return the correct value */
                $result = '<p class="result">' . $errorMsg . '</p>';
            }//if_errors

            /* Finally, remove the fake permission that have been added */
            if ($fakePermission) {
                self::Delete_FakePermission($fakePermission->id);
            }//if_fakePermission

            return $result;
        }catch (Exception $ex) {
            /* Remove the fake permission that have been added */
            if ($fakePermission) {
                self::Delete_FakePermission($fakePermission->id);
            }//if_fakePermission

            throw $ex;
        }//try_Catch
    }//create_course

    /**********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    23/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user has the correct permissions to create a new course from the template
     */
    private function HasCorrectPermissions() {
        /* Variables    */
        global $DB,$USER;

        try {
            /* Fist, check if the user has the correct permissions  */
            /* Search Criteria  */
            $params = array();
            $params['user']         = $USER->id;
            $params['context']      = '1';
            $params['archetype']    = 'manager';

            /* SQL Instruction  */
            $sql = " SELECT		ra.id,
                                ra.contextid,
                                ra.userid
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 	r.id 			= ra.roleid
                                                            AND	r.archetype		= :archetype
                                                            AND r.shortname     = r.archetype
                     WHERE		ra.userid     = :user
                        AND     ra.contextid  = :context ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//HasCorrectPermissions

    /**
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    23/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a fake permission, temporary permission, to the user.
     * So, the user will be able to create a new course from the template
     */
    private function Add_FakePermission_To_User() {
        /* Variables    */
        global $DB,$USER;
        $fakePermission = null;
        $context        = null;
        $role           = null;

        try {
            /* Context System   */
            $context = CONTEXT_SYSTEM::instance();
            /* Role Id      */
            $role = $DB->get_record('role',array('archetype' => 'manager','shortname' => 'manager'));

            /* New Fake Permission  */
            $fakePermission = new stdClass();
            $fakePermission->userid         = $USER->id;
            $fakePermission->roleid         = $role->id;
            $fakePermission->contextid      = $context->id;
            $fakePermission->timemodified   = time();

            /* Insert   */
            $fakePermission->id = $DB->insert_record('role_assignments',$fakePermission);

            /* Reload All Capabilities  */
            reload_all_capabilities();

            return $fakePermission->id;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_FakePermission_To_User

    /**
     * @param           $fakePermissionId
     * @throws          Exception
     *
     * @creationDate    23/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete the fake permission have been created for the user
     */
    private function Delete_FakePermission($fakePermissionId) {
        /* Variables    */
        global $DB;

        try {
            /* Delete Fake Permission   */
            $DB->delete_records('role_assignments',array('id' => $fakePermissionId));

            /* Reload All Capabilities  */
            reload_all_capabilities();
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Delete_FakePermission
}
