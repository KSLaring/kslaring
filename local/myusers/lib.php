<?php
/**
 * Users Admin - Category plugin - Category navigatio
 *
 * Description
 *
 * @package         local
 * @subpackage      myusers
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      27/01/2014
 * @author          eFaktor     (fbv)
 *
 */

function local_myusers_extend_navigation_category_settings($navigation, $coursecategorycontext) {
    /* Variables */
    global $PAGE;
    $url = null;
    $node = null;

    if (has_capability('moodle/user:editprofile',$PAGE->context)) {
        $url = new moodle_url('/local/myusers/myusers.php',array('id' => $PAGE->context->instanceid));

        $node = navigation_node::create(
            get_string('pluginname','local_myusers'),
            $url,
            navigation_node::TYPE_SETTING,
            'myusers',
            'myusers',
            new pix_icon('i/settings','')
        );

        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $node->make_active();
        }

        $navigation->add_node($node);
    }

}//navigation_category_settings 