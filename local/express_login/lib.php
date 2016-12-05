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

/**
 * Description
 * Add express login block to my profile settings
 *
 * @creationDate    05/12/2016
 * @author          eFaktor     (fbv)
 *
 * @param           \core_user\output\myprofile\tree $tree
 * @param           $user
 * @param           $iscurrentuser
 * @param           $course
 *
 * @return          bool
 * @throws          coding_exception
 */
function local_express_login_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    /* Variables */

    if (isguestuser($user)) {
        // The guest user cannot post, so it is not possible to view any posts.
        // Also blogs might be disabled.
        // May as well just bail aggressively here.
        return false;
    }

    // Add new category
    $category = new core_user\output\myprofile\category('express', get_string('pluginname', 'local_express_login'),'contact');
    $tree->add_category($category);

    // Index - Express login
    $node = new core_user\output\myprofile\node('express', 'express_index',
                                                get_string('pluginname', 'local_express_login'),
                                                null,
                                                new moodle_url('/local/express_login/index.php'));
    $tree->add_node($node);

    // Change pin code
    $node = new core_user\output\myprofile\node('express', 'express_pin_code',
                                                get_string('btn_change_pin_code','local_express_login'),
                                                'express_index',
                                                new moodle_url('/local/express_login/change_express.php'));
    $tree->add_node($node);

    // Regenerate code
    $node = new core_user\output\myprofile\node('express', 'express_regenerate_link',
                                                get_string('btn_regenerate_link','local_express_login'),
                                                'express_pin_code',
                                                new moodle_url('/local/express_login/regenerate_express.php'));
    $tree->add_node($node);

    return true;
}//local_express_login_myprofile_navigation

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
