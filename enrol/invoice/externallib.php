<?php
/**
 * Invoice Enrolment Plugin - External Lib
 *
 * @package         enrol
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    25/09/2014
 * @author          efaktor     (fbv)
 */
class enrol_invoice_external extends external_api {
    /**
     * Returns description of get_instance_info() parameters.
     *
     * @return external_function_parameters
     */
    public static function get_instance_info_parameters() {
        return new external_function_parameters(
            array('instanceid' => new external_value(PARAM_INT, 'instance id of self enrolment plugin.'))
        );
    }//get_instance_info_parameters


    /**
     * Return invoice-enrolment instance information.
     *
     * @param int $instanceid instance id of self enrolment plugin.
     * @return array instance information.
     */
    public static function get_instance_info($instanceid) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');

        $params = self::validate_parameters(self::get_instance_info_parameters(), array('instanceid' => $instanceid));

        // Retrieve self enrolment plugin.
        $enrolplugin = enrol_get_plugin('invoice');
        if (empty($enrolplugin)) {
            throw new moodle_exception('invaliddata', 'error');
        }

        $enrolinstance = $DB->get_record('enrol', array('id' => $params['instanceid']), '*', MUST_EXIST);
        $coursecontext = context_course::instance($enrolinstance->courseid);
        $categorycontext = $coursecontext->get_parent_context();
        self::validate_context($categorycontext);

        $instanceinfo = (array) $enrolplugin->get_enrol_info($enrolinstance);
        if (isset($instanceinfo['requiredparam']->enrolpassword)) {
            $instanceinfo['enrolpassword'] = $instanceinfo['requiredparam']->enrolpassword;
        }
        unset($instanceinfo->requiredparam);

        return $instanceinfo;
    }//get_instance_info

    /**
     * Returns description of get_instance_info() result value.
     *
     * @return external_description
     */
    public static function get_instance_info_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'id of course enrolment instance'),
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'type' => new external_value(PARAM_PLUGIN, 'type of enrolment plugin'),
                'name' => new external_value(PARAM_RAW, 'name of enrolment plugin'),
                'status' => new external_value(PARAM_RAW, 'status of enrolment plugin'),
                'enrolpassword' => new external_value(PARAM_RAW, 'password required for enrolment', VALUE_OPTIONAL),
            )
        );
    }//get_instance_info_returns

}//enrol_invoice_external
