<?php
/**
 * Express Login  - Index
 *
 * @package         local
 * @subpackage      express_login
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    26/11/2014
 * @author          eFaktor     (fbv)
 */
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once('expressloginlib.php');
require_once('index_form.php');

require_login();

/* Params   */
$user_id         = required_param('id',PARAM_INT);
$current_user    = ($user_id == $USER->id);
$current_page    = null;
$plugin_info     = null;
$return_url      = new moodle_url('/user/profile.php',array('id' => $user_id));
// Get the profile page.  Should always return something unless the database is broken.
if (!$current_page = my_get_page($user_id, MY_PAGE_PUBLIC)) {
    print_error('mymoodlesetup');
}

/* Settings Page    */
$PAGE->set_context(CONTEXT_USER::instance($user_id));
$PAGE->set_pagelayout('mypublic');
$PAGE->set_pagetype('user-profile');
$PAGE->set_url(new moodle_url('/local/express_login/index.php',array('id' => $user_id)));

// Start setting up the page.
$PAGE->set_subpage($current_page->id);
$PAGE->navbar->add(get_string('pluginname','local_express_login'));


/* Plugins Info */
$plugin_info     = get_config('local_express_login');

/* Add Form     */
$exists_express = Express_Login::Exists_ExpressLogin($user_id);
if ($exists_express) {

    $force = Express_Login::Force_NewExpressLogin($plugin_info,$user_id);
    if ($force) {
        $url = new moodle_url('/local/express_login/change_express.php',array('id' => $user_id));
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('pin_code_expired','local_express_login'), 'notifysuccess');
        echo $OUTPUT->continue_button($url);
        echo $OUTPUT->footer();
        die();
    }else {
        /* Express Link */
        $express_link = Express_Login::Get_ExpressLink($user_id);
        /* Add to Bookmark      */
        $bookmarkURL  = '<a href="#">' . $SITE->shortname . '</a>';
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname','local_express_login'));
        echo html_writer::label(get_string('title_link','local_express_login',$SITE->fullname),null);
        ?>
        <html>
            <body>
                <div id="clipboardDiv" style="display: none;"><?php echo get_string('clipboardDiv','local_express_login'); ?></div>
                <div id="bookmarkDiv" style="display: none;"><?php echo get_string('bookmarkDiv','local_express_login',$bookmarkURL); ?></div>
                <br/>
                <button id="btn_copy_link" data-clipboard-text="<?php echo $express_link;?>"><?php echo get_string('btn_copy_link','local_express_login'); ?></button>
                <script type="text/javascript" src="zeroclipboard/ZeroClipboard.js"></script>
                <script type="text/javascript">
                    var client      = new ZeroClipboard( document.getElementById("btn_copy_link"));
                    var divClip     = document.getElementById("clipboardDiv");
                    var bookmarkDiv = document.getElementById("bookmarkDiv");
                    client.on( "ready", function( readyEvent ) {
                        client.on( "aftercopy", function( event ) {
                            divClip.style.display = 'block';
                            var urlBook = event.data["text/plain"];
                            var newContent  = bookmarkDiv.innerHTML.valueOf();
                            newContent = bookmarkDiv.innerHTML.replace("#",urlBook) + '</br>';
                            bookmarkDiv.innerHTML = newContent;
                            bookmarkDiv.style.display = "block";
                        } );
                    } );
                </script>
            </body>
        </html>
        <?php
        echo $OUTPUT->footer();
    }//if_force
}else {
    $form = new express_login_form(null,$plugin_info);

    if ($form->is_cancelled()) {
        $_POST = array();
        redirect($return_url);
    }else if ($data = $form->get_data()) {
        /* Generate Express Login */
        $express_login = Express_Login::Generate_ExpressLink($data,false);

        if ($express_login) {
            /* Express Link */
            $express_link = Express_Login::Get_ExpressLink($USER->id);
            /* Add to Bookmark      */
            $bookmarkURL  = '<a href="#">' . $SITE->shortname . '</a>';
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('pluginname','local_express_login'));
            echo html_writer::label(get_string('title_link','local_express_login',$SITE->fullname),null);
            ?>
            <html>
            <body>
            <div id="clipboardDiv" style="display: none;"><?php echo get_string('clipboardDiv','local_express_login'); ?></div>
            <div id="bookmarkDiv" style="display: none;"><?php echo get_string('bookmarkDiv','local_express_login',$bookmarkURL); ?></div>
            <br/>
            <button id="btn_copy_link" data-clipboard-text="<?php echo $express_link;?>"><?php echo get_string('btn_copy_link','local_express_login'); ?></button>
            <script type="text/javascript" src="zeroclipboard/ZeroClipboard.js"></script>
            <script type="text/javascript">
                var client      = new ZeroClipboard( document.getElementById("btn_copy_link"));
                var divClip     = document.getElementById("clipboardDiv");
                var bookmarkDiv = document.getElementById("bookmarkDiv");
                client.on( "ready", function( readyEvent ) {
                    client.on( "aftercopy", function( event ) {
                        divClip.style.display = 'block';
                        var urlBook = event.data["text/plain"];
                        var newContent  = bookmarkDiv.innerHTML.valueOf();
                        newContent = bookmarkDiv.innerHTML.replace("#",urlBook) + '</br>';
                        bookmarkDiv.innerHTML = newContent;
                        bookmarkDiv.style.display = "block";
                    } );
                } );
            </script>
            </body>
            </html>
            <?php
            echo $OUTPUT->footer();
        }else {
            echo $OUTPUT->header();
            echo $OUTPUT->notification(get_string('err_generic','local_express_login'), 'notifysuccess');
            echo $OUTPUT->continue_button($return_url);
            echo $OUTPUT->footer();
        }
    }//if_form

    echo $OUTPUT->header();
        $form->display();
    echo $OUTPUT->footer();
}//if_exists


