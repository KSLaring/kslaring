<?php
/**
 * Micro-Learning - Version
 *
 * @package         local
 * @subpackage      microlearning
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      12/09/2014
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die;

$plugin->version    = 2015090810;                   /* The current plugin version (Date: YYYYMMDDXX)  */
//$plugin->requires = 2012061700;                   /* Requires this Moodle version                   */
$plugin->component  = 'local_microlearning';        /* Full name of the plugin (used for diagnostics) */
$plugin->cron       =  24*3600;                     // Cron interval 1 day. //60; //(300 secs - 5 min)