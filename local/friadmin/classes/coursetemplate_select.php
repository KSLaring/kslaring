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

    // The form feeback - for debugging.
    protected $formdatadump = null;

    // The course creation process result.
    protected $coursecreationresult = null;

    // The Moodle form.
    protected $mform = null;

    // The returned form data.
    protected $fromform = null;

    // Does the user selected local template category exisist.
    protected $localtempcategoryexists = null;

    // The id of the created course.
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
     *
     * @updateDate  07/01/201
     * @author      eFaktor     (fbv)
     *
     * Description
     * Redirect to Course Settings
     */
    public function __construct($type) {
        /* Variables    */
        global $CFG,$USER;
        $customdata     = null;
        $urlSettings    = null;

        try {
            // Create the data object and set the first values.
            parent::__construct();

            /* custom data used */
            $customdata = local_friadmin_helper::get_usercategories_data();

            $this->localtempcategoryexists = $customdata['localtempcategoryexists'];

            $customdata['templates'] = array();
            $customdata['temptype'] = $type;

            if ($type == TEMPLATE_TYPE_EVENT) {
                $customdata['templates'] = $customdata['eventtemplates'];
                $customdata['preftemplate'] = $customdata['preftemplates'][TEMPLATE_TYPE_EVENT];
                $customdata['seltemplate'] = 'seleventtemplate';
            } else if ($type == TEMPLATE_TYPE_NETCOURSE) {
                $customdata['templates'] = $customdata['netcoursetemplates'];
                $customdata['preftemplate'] = $customdata['preftemplates'][TEMPLATE_TYPE_NETCOURSE];
                $customdata['seltemplate'] = 'selnetcoursetemplate';
            }

            /* Create form  */
            $mform = new local_friadmin_coursetemplate_select_form(null, $customdata,
                'post', '', array('id' => 'mform-coursetemplate-select'));
            $this->mform = $mform;

            /* Collect the input data and create the new course */
            if ($fromform = $mform->get_data()) {
                $this->fromform = $fromform;

                $this->formdatadump = '<div class="form-data"><h4>Form data</h4><pre>' .
                    var_export($fromform, true) . '</pre></div>';

                /* Create the course    */
                $this->coursecreationresult = $this->create_course();

                /**
                 * @updateDate  07/06/2016
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Enrol the course creator. This is the normal behaviour in Moodle
                 */
                // Get the context of the newly created course.
                $context = context_course::instance($this->newcourseid, MUST_EXIST);

                if (!empty($CFG->creatornewroleid) and !is_viewing($context, NULL, 'moodle/role:assign') and !is_enrolled($context, NULL, 'moodle/role:assign')) {
                    // Deal with course creators - enrol them internally with default role.
                    enrol_try_internal_enrol($this->newcourseid, $USER->id, $CFG->creatornewroleid);
                }

                /**
                 * @updateDate  07/01/2016
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Redirect the user to course settings to be able to complete it
                 */
                $urlSettings = new moodle_url('/local/friadmin/course_template/course_settings.php',
                    array('id' => $this->newcourseid,'ct' => (int)$this->fromform->seltemplate));
                redirect($urlSettings);
            }//if_get_data
        } catch (Exception $ex) {
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
     * Render the form and save the rendered HTML, add an error message
     * when the selected local template directory does not exist.
     */
    public function render() {
        $this->data->content = '';

        if (is_null($this->coursecreationresult)) {
            if (!$this->localtempcategoryexists) {
                $this->data->content .= $this->missing_localtempcategory_message();
            }
            $this->data->content .= $this->mform->render();
        } else {
            $this->data->content .= $this->coursecreationresult;
        }
    }

    /**
     * Set the default form values
     *
     * The associative $defaults: array 'elementname' => 'defaultvalue'
     *
     * @param array $defaults The default values
     */
    public function set_defaults($defaults = array()) {
        $this->mform->set_defaults($defaults);
    }

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

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
     *
     * @updateDate  30/06/2016
     * @auhtor      eFaktor     (fbv)
     *
     * Description
     * Show only categories connected with you
     */
    protected function get_popup_data() {
        /* Variables    */
        global $CFG;
        $result = null;
        $pluginInfo = null;
        $templateCatId = null;
        $courseCat = null;
        $templateCourses = null;

        try {
            require_once($CFG->libdir . '/coursecatlib.php');

            /* Plugin Info      */
            $pluginInfo = get_config('local_friadmin');

            /* Result Structure */
            $result = array('categories' => array(),
                            'templates' => array());

            /* Get Categories   */
            $result['categories'] = local_friadmin_helper::getMyCategories();

            /* Fill the data    */
            if ($pluginInfo) {
                if (isset($pluginInfo->template_category) && ($pluginInfo->template_category)) {
                    /* Get Template Category && Course Category */
                    $templateCatId = $pluginInfo->template_category;

                    /* Get Courses category */
                    $sql = " SELECT c.id,
                                    c.fullname,
                                    c.visible
                             FROM   {course} c 
                             WHERE  (c.format like '%classroom%' 
                                     OR  
                                     c.format like '%elearning%'
                                     OR  
                                     c.format like '%netcourse%')
                                    AND c.category = $templateCatId ";

                    global $DB;
                    $templateCourses = $DB->get_records_sql($sql);

                    /* Add to result Structure  */
                    foreach ($templateCourses as $templateCo) {
                        if ($templateCo->visible) {
                            $result['templates'][$templateCo->id] = $templateCo->fullname;
                        }//if_visible
                    }//for_templatesCourse
                }//if_template_category
            }//if_pluginInfo

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_popup_data

    /**
     * Create the course with the given data
     * Restore the course from an exisitng course backup file.
     * Create a course backup if non exists.
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
     *  - Create a Fake Permission before create the new course from the template and
     *  remove later.
     *  - Errors is an array. So, it has to be converted into a string
     */
    protected function create_course() {
        /* Variables    */
        global $DB, $CFG;

        $result = '';
        $fakePermission = null;
        $newcourseid = null;
        $error = null;
        $coursedata = null;
        $newCourse = null;
        $info = null;
        $errorMsg = null;
        $admin = get_admin();

        try {
            $coursedata = array(
                'userid' => $admin->id,
                'sourcedir' => $CFG->dataroot . '/temp/test/',
                'categoryid' => $this->fromform->selcategory,
                'fullname' => $this->fromform->selfullname,
                'shortname' => $this->fromform->selshortname,
            );

            list($newcourseid, $error) =
                $this->restore_course((int)$this->fromform->seltemplate, $coursedata);

            if (empty($error)) {
                // Get the infos for the new course.
                $newCourse =
                    $DB->get_record('course', array('id' => $newcourseid), '*', MUST_EXIST);
                if ($newCourse) {
                    $this->newcourseid = $newCourse->id;
                    $info = array('id' => $newCourse->id,
                        'shortname' => $newCourse->shortname,
                        'fullname' => $newCourse->fullname
                    );

                    $result .= '<p class="result">';
                    $result .=
                        get_string('coursetemplate_result', 'local_friadmin', $info) . '</p>';
                } else {
                    /* The course should exist when no processor errors had been generated */
                    $result = '<p class="result">';
                    $result .= get_string('coursetemplate_error', 'local_friadmin') . '</p>';
                }//if_else_new_course
            } else {
                $result .= $error;
            }

            return $result;
        } catch (Exception $ex) {

            throw $ex;
        }//try_Catch
    }//create_course

    /**
     * Restore a backup course file with the given parameters for the new course.
     *
     * @param int $cid The course id of the course to restore
     *
     * @return array With the course id and the error
     * @throws Exception
     * @throws dml_transaction_exception
     * @throws restore_controller_exception
     */
    protected function restore_course($cid,$options) {
        /* Variables */
        global $CFG,$DB;
        $error      = '';
        $courseid   = null;
        $component  = 'backup';
        $filearea   = 'course';
        $itemid     = '0';
        $sourcefile = null;

        try {
            require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

            $coursecontext  = context_course::instance($cid);
            $fs             = get_file_storage();

            $backupid = $this->create_backup_if_needed($cid);

            /**
             * @updateDate      29/06/2016
             * @author          eFaktor     (fbv)
             *
             * Description
             * Remove get_file_inof because it compares with course = 1
             */
            //$browser    = get_file_browser();
            //$fileinfo   = $browser->get_file_info($coursecontext, $component, $filearea, $itemid);

            //if (is_a($fileinfo, 'file_info_stored')) {
                $files      = $fs->get_area_files($coursecontext->id, $component, $filearea, $itemid);
                $sourcefile = $this->newest_stored_file($files);
            //}

            if (is_null($sourcefile)) {
                $error .= 'No backupfile found for course id ' . $cid . "<br>\n";
                return array($courseid, $error);
            }

            // Extract the file.
            $packer     = get_file_packer('application/vnd.moodle.backup');
            $backupid   = restore_controller::get_tempdir_name(SITEID, $options['userid']);
            $path       = "$CFG->tempdir/backup/$backupid/";
            if (!$packer->extract_to_pathname($sourcefile, $path)) {
                $error .= 'Invalid backup file ' . $sourcefile->get_filename() . "<br>\n";
                return array($courseid, $error);
            }

            // Start delegated transaction.
            $transaction = $DB->start_delegated_transaction();

            // Create new course.
            $courseid = restore_dbops::create_new_course(fix_utf8($sourcefile->get_filename()),
                                                         'restored-' . $backupid, $options['categoryid']);

            // Restore backup into course.
            $controller = new restore_controller($backupid,
                                                 $courseid,
                                                 backup::INTERACTIVE_NO,
                                                 backup::MODE_SAMESITE,
                                                 $options['userid'],
                                                 backup::TARGET_NEW_COURSE);

            if ($controller->execute_precheck()) {
                $controller->execute_plan();
            } else {
                $error .= 'Precheck fails for ' . $sourcefile->get_filename() . ' ... skipping' . "<br>\n";
                $results = $controller->get_precheck_results();
                foreach ($results as $type => $messages) {
                    foreach ($messages as $index => $message) {
                        $error .= 'precheck ' . $type . '[' . $index . '] = ' . $message . "<br>\n";
                    }
                }
                try {
                    $transaction->rollback(new Exception('Prechecked failed'));
                } catch (Exception $e) {
                    unset($transaction);
                    $controller->destroy();
                    unset($controller);

                    return array($courseid, $error);
                }
            }

            // Commit and clean up.
            $transaction->allow_commit();
            unset($transaction);
            $controller->destroy();
            unset($controller);

            // Set the course name choosen by the user
            $course = new stdClass;
            $course->id = $courseid;
            $course->fullname = fix_utf8($options['fullname']);
            $course->shortname = $options['shortname'];
            $DB->update_record('course', $course);

            return array($courseid, $error);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//restore_course

    /**
     * Get the newest file in the file area.
     *
     * @param array $files The stored files
     *
     * @return null|stored_file The newest file
     */
    protected function newest_stored_file($files) {
        $newesttime = 0;
        $newest = null;

        /* @var $f stored_file */
        foreach ($files as $f) {
            if ($f->get_filename() === '.') {
                continue;
            }

            if ($f->get_timecreated() > $newesttime) {
                $newesttime = $f->get_timecreated();
                $newest = $f;
            }
        }

        return $newest;
    }

    /**
     * Create an automatic course backup if none exisits.
     *
     * @param int $cid The course id
     *
     * @return int|null The backup id if success, null if failure
     */
    /**
     * @param       $cid        The course id
     *
     * @return      null        The backup id if success, null if failure
     * @throws      Exception
     */
    protected function create_backup_if_needed($cid) {
        /* Variables */
        global $CFG, $DB;
        $result     = null;
        $component  = 'backup';
        $filearea   = 'course';
        $itemid     = '0';

        try {
            $coursecontext  = context_course::instance($cid);
            $fs             = get_file_storage();

            if ($fs->is_area_empty($coursecontext->id, $component, $filearea)) {
                $course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
                if ($course) {
                    $admin = get_admin();
                    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
                    $bc = new backup_controller(backup::TYPE_1COURSE, $cid, backup::FORMAT_MOODLE,
                        backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $admin->id);

                    // Set the default filename.
                    $format = $bc->get_format();
                    $type = $bc->get_type();
                    $id = $bc->get_id();
                    $users = $bc->get_plan()->get_setting('users')->get_value();
                    $anonymised = $bc->get_plan()->get_setting('anonymize')->get_value();
                    $filename = backup_plan_dbops::get_default_backup_filename($format, $type,
                        $id, $users, $anonymised);
                    $bc->get_plan()->get_setting('filename')->set_value($filename);

                    // Backup the course.
                    $bc->set_status(backup::STATUS_AWAITING);
                    $bc->execute_plan();

                    // Get the results.
                    $backupid = $bc->get_backupid();
                    //$results = $bc->get_results();

                    $bc->destroy();

                    $result = $backupid;
                }
            }

            return $result;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch

    }

    /**
     * Create the missing local template category message.
     *
     * @return string HTML for the error message
     * @throws coding_exception
     */
    protected function missing_localtempcategory_message() {
        $out = '';
        $link = new moodle_url('/local/friadmin/mysettings.php');
        $strmissinglocaltempcategory = get_string('missinglocaltempcategory', 'local_friadmin',
            $link->out());

        $out .= '<div class="alert alert-error">' . $strmissinglocaltempcategory . '</div>';

        return $out;
    }
}
