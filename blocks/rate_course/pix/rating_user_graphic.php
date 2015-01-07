<?php
/**
 * Block Rating Course - Rating User Graphics
 *
 * Description
 *
 * @package         block
 * @subpackage      rate_course
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      20/05/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once(dirname(__FILE__).'/../../../config.php');

$rate = required_param('rate', PARAM_INT); // Rate User.

@header('Content-Type: image/gif');
@header("Expires: ".gmdate("D, d M Y H:i:s") . " GMT" );
@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Cache-Control: post-check=0, pre-check=0", false);
@header("Pragma: no-cache");

if ($rate >= 0) {
    $rate = $rate * 2;
    echo file_get_contents( $CFG->dirroot.'/blocks/rate_course/pix/star'.$rate.'.png' );
}