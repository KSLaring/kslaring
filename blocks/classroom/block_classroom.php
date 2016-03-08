<?php
/**
 * Classroom Course Format Block
 *
 * @package         block
 * @subpackage      classroom
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    15/09/2015
 * @author          efaktor     (fbv)
 */

class block_classroom extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_classroom');
    }//init

    function has_config() {
        return false;
    }//has_config

    public function applicable_formats() {
        return array('all'=>true);
    }

    function get_content() {
        /* Variables    */
        global $COURSE;

        try {
            /* Title Block  */
            $this->title = $COURSE->fullname;

            if ($this->content !== NULL) {
                return $this->content;
            }

            /* Add the content to the block */
            $this->content = new stdClass;
            $this->content->text = '';
            $this->content->footer = '';

            /* Add course info to the block */
            require_once('locallib.php');
            $this->content->text .= ClassroomBlock::GetContentBlock($COURSE->id);

            return $this->content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_content
}//block_classroom