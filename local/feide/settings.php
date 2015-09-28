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
    $options = array('0' => get_string('feide_activate','local_feide'),
                     '1' => get_string('feide_deactivate','local_feide'));
    $settings->add(new admin_setting_configselect('local_feide/feide_active', new lang_string('active'),  '', 1, $options));

    $settings->add(new admin_setting_configtext('local_feide/ks_point',get_string('ks_site','local_feide'),'','',PARAM_TEXT,50));
}//if_config