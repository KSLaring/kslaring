<?php
/**
 * Tracker Block - Library Page
 *
 * @package         block
 * @subpackage      tracker
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    21/02/2014
 * @author          efaktor     (fbv)
 */

/**
 * @param           $outcome
 * @param           $toggle_outcome
 * @param           $url
 * @return          string
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the tag for the Outcome title
 */
function block_tracker_getTagTitleOutcome($outcome,$toggle_outcome,$url) {
    /* Variables    */
    $tag_header = '';

    $tag_header .= html_writer::start_tag('div',array('class' => 'header_list'));
    $tag_header .= '<h3>'. $outcome . '&nbsp;' . '<button class="toggle" type="image" id="' . $toggle_outcome . '"><img id="' . $toggle_outcome . '_img' . '" src="' . $url . '"></button>' . '</h3>';
    $tag_header .= html_writer::end_tag('div');

    return $tag_header;
}//block_tracker_getTitleOutcome
