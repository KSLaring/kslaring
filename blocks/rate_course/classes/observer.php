<?php

/**
 * Event observer - Rate a course.
 *
 * @package         block
 * @subpackage      rate_course
 * @copyright       2012 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    23/08/2016
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();
class block_rate_course_observer {

    /** @var int indicates that course module was created */
    const CM_CREATED = 0;
    /** @var int indicates that course module was udpated */
    const CM_UPDATED = 1;
    /** @var int indicates that course module was deleted */
    const CM_DELETED = 2;
    
    /**
     * @param       \core\event\base $event
     * @return      bool
     * @throws      Exception
     *
     * Description
     * Delete all rates for the course
     */
    public static function delete(\core\event\base $event) {
        /* Variables */
        global $DB;

        try {
            $res = $DB->delete_records('block_rate_course',
                                       array('course'=>$event->courseid));
            if ($res === false) {
                return $res;
            }
            return true;
        }catch (Exception $ex) {
            throw $ex;
        }
    }
}