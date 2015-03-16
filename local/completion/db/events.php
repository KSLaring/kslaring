<?php
/**
 * Events Completion Course - Completion Activity  Course
 *
 * @package         local
 * @subpackage      completion/db
 * @copyright       2013 eFaktor    {@link https://www.efaktor.no}
 *
 * @creationDate    22/04/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * The events we need to capture
 */

defined('MOODLE_INTERNAL') || die();

    $handlers = array(
        'course_completion_updated' => array(
            'handlerfile'       => '/local/completion/event_logger.php',
            'handlerfunction'   => 'local_completion_handle_course_completion_updated',
            'schedule'          => 'instant',
            'internal' => 1,
        ),
        'activity_completion_changed' => array(
            'handlerfile'       => '/local/completion/event_logger.php',
            'handlerfunction'   => 'local_completion_handle_activity_completion_changed',
            'schedule'          => 'instant',
            'internal' => 1,
        ),
        'quiz_attempt_submitted' => array(
            'handlerfile'       => '/local/completion/event_logger.php',
            'handlerfunction'   => 'local_completion_handle_quiz_attempt_submitted',
            'schedule'          => 'instant',
            'internal' => 1,
        ),
);
