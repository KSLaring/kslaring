<?php
/**
 * Mypage plugin - Version
 *
 * Description
 *
 * @package         local
 * @subpackage      mypage
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      23/01/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Set the mypage in the user preferences
 */

require_once( '../../config.php');

$url = optional_param('url',null,PARAM_TEXT);

if ($url) {
    set_user_preference('user_home_page_preference_frikomport', $url);
    redirect($url);
}else {
    $DB->delete_records('user_preferences',array('userid' => $USER->id,'name' => 'user_home_page_preference_frikomport'));
    redirect($CFG->wwwroot);
}

