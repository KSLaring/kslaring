<?php
/**
 * Express Login  - Library Extends Navigation Node
 *
 * @package         local
 * @subpackage      express_login
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    26/11/2014
 * @author          eFaktor     (fbv)
 */

function local_express_login_extend_settings_navigation($settingsnav, $context) {
    /* Plugin Info */
    $plugin     = get_config('local_express_login');
    if (($plugin) && (isset($plugin->activate_express))) {
        if ($plugin->activate_express) {
            if ($setting_node = $settingsnav->get('usercurrentsettings')) {
                $str_title = get_string('pluginname', 'local_express_login');
                $url = new moodle_url('/local/express_login/index.php');
                /* Create Node  */
                $express_node = navigation_node::create($str_title,
                    null,
                    navigation_node::TYPE_SETTING,'express_login',
                    'express_login',
                    null);

                /* Generate PIN CODE    */
                $express_node->add($str_title,$url);
                /* Change PIN CODE      */
                $express_node->add(get_string('btn_change_pin_code','local_express_login'),new moodle_url('/local/express_login/change_express.php'));
                /* Regenerate Express Link  */
                $express_node->add(get_string('btn_regenerate_link','local_express_login'),new moodle_url('/local/express_login/regenerate_express.php'));

                $setting_node->add_node($express_node);
            }//if_usercurrentsettings
        }
    }//if_activate
}//local_express_login_extend_settings_navigation

/**
 * @creationDate    15/06/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Trigger the cron
 */
function local_express_login_cron() {
    /* Variables    */
    $pluginInfo     = null;
    $dateHour       = null;
    $dateMin        = null;
    $cronHour       = null;
    $cronMin        = null;
    $time           = null;

    try {
        mtrace("Cron Express Login Ini");
        /* Plugin Info */
        $pluginInfo     = get_config('local_express_login');

        /* Trigger the Cron */
        if ($pluginInfo->express_cron_active) {
            require_once('cron/expresscron.php');
            Express_Cron::cron();
        }else {
            mtrace("Cron Express Login Deactivated");
        }//if_Activate
    }catch (Exception $ex) {
        throw  $ex;
    }//try_catch
}//local_express_login_cron
