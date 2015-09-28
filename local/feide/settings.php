<?php
/**
 * Feide Integration WebService - Settings
 *
 * @package         local
 * @subpackage      feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    21/09/2015
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_feide', get_string('pluginname','local_feide'));
    $ADMIN->add('localplugins', $settings);

    /* Web Service  */
    $settings->add(new admin_setting_configtext('local_feide/ks_point',get_string('ks_site','local_feide'),'','',PARAM_TEXT,50));
}//if_config