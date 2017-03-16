<?php
// This file is part of the custom Moodle Bootstrap theme
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by kommit
 *
 * @package    theme_kommit
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_kommit_core_renderer extends theme_bootstrapbase_core_renderer {
    // Additional block regions.
    public function blocks($region, $classes = array(), $tag = 'aside') {
        global $editing;

        $displayregion = $this->page->apply_theme_region_manipulations($region);
        if ($this->page->blocks->region_has_content($displayregion, $this) || $editing) {
            $blockshtml = parent::blocks($region, $classes, $tag);
            // Change the quiz navblock HTML only for a netcourse.
            if ($this->page->course->format === 'netcourse' &&
                $region === 'content-top' && strpos($blockshtml, 'mod_quiz_navblock') !== false) {
                $this->page->requires->js_call_amd('theme_kommit/quiznav', 'init');
                $blockshtml = $this->process_quiz_nav_block($blockshtml);
            }

            return $blockshtml;
        }

        return '';
    }

    protected function process_quiz_nav_block($blockshtml) {
        $jsscript = <<<EOT

EOT;

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;
        libxml_use_internal_errors(true);
        // DOMDocument uses the ISO-8859-1 encoding, to keep unicode text UTF-8 needs
        // to be converted to HTML entities.
        // http://stackoverflow.com/questions/11309194/php-domdocument-failing-to-handle-utf-8-characters-%E2%98%86.
        $blockshtmlencoded = mb_convert_encoding($blockshtml, 'HTML-ENTITIES', 'UTF-8');
        $dom->loadHTML($blockshtmlencoded);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        // Get the "qn_buttons" and its parent node.
        $qnbuttons = $xpath->query("//div[contains(@class, 'qn_buttons')]")->item(0);
        $othernav = $xpath->query("//div[contains(@class, 'othernav')]")->item(0);
        $contentnode = $qnbuttons->parentNode;

        // Create a new wrapper.
        $wrapper = $dom->createElement('div');
        $wrapper->setAttribute('id', 'qn-buttons-wrapper');

        $backbtn = $dom->createDocumentFragment();
        $backbtn->appendXML('<button class="btn prev-btn">&lt;</button>');
        $nextbtn = $dom->createDocumentFragment();
        $nextbtn->appendXML('<button class="btn next-btn">&gt;</button>');

        // Put the new wrapper around the button node and
        // add both to the content node.
        $contentnode->removeChild($qnbuttons);
        $wrapper->appendChild($qnbuttons);
        $contentnode->insertBefore($backbtn, $othernav);
        $contentnode->insertBefore($wrapper, $othernav);
        $contentnode->insertBefore($nextbtn, $othernav);

        // Create the script node and append it at the end.
        $blockscript = $dom->createElement('script', $jsscript);
        $blockscript->setAttribute('type', 'text/javascript');
        $wrapper->appendChild($blockscript);

        $body = $dom->getElementsByTagName('body')->item(0);
        $out = $this->get_node_inner_html($body);

        return $out;
    }

    protected function get_node_inner_html(DOMNode $onode) {
        $odom = new DOMDocument('1.0', 'utf-8');
        foreach ($onode->childNodes as $ochild) {
            $odom->appendChild($odom->importNode($ochild, true));
        }

        return $odom->saveHTML();
    }

    /**
     * This code replaces the icons in the Admin block with
     * FontAwesome variants where available.
     *
     * @param pix_icon $icon
     *
     * @return bool|string
     */
    protected function render_pix_icon(pix_icon $icon) {
        if (self::replace_moodle_icon($icon->pix) !== false && $icon->attributes['alt'] === '') {
            return self::replace_moodle_icon($icon->pix);
        } else {
            return parent::render_pix_icon($icon);
        }
    }

    private static function replace_moodle_icon($name) {
        $icons = array(
            'add' => 'plus',
            'book' => 'book',
            'chapter' => 'file',
            'docs' => 'question-sign',
            'generate' => 'gift',
            'i/backup' => 'cloud-download',
            'i/checkpermissions' => 'user',
            'i/dragdrop' => 'arrows',
            'i/edit' => 'pencil',
            'i/filter' => 'filter',
            'i/grades' => 'table',
            'i/group' => 'group',
            //'i/hide' => 'eye',
            'i/import' => 'upload',
            'i/lock' => 'lock',
            //'i/move_2d' => 'arrows',
            'i/navigationitem' => 'circle',
            'i/outcomes' => 'magic',
            'i/publish' => 'globe',
            'i/reload' => 'refresh',
            'i/report' => 'list-alt',
            'i/restore' => 'cloud-upload',
            'i/return' => 'repeat',
            'i/roles' => 'user',
            'i/settings' => 'cogs',
            //'i/show' => 'eye-slash',
            'i/switchrole' => 'random',
            'i/switch_minus' => 'minus-square',
            'i/user' => 'user',
            'i/users' => 'user',
            't/right' => 'arrow-right',
            't/left' => 'arrow-left',


        );
        if (isset($icons[$name])) {
            return "<i class=\"fa fa-$icons[$name]\" id=\"icon\"></i>";
        } else {
            return false;
        }
    }


    /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar() {
        $items = $this->page->navbar->get_items();
        $breadcrumbs = array();
        foreach ($items as $item) {
            $item->hideicon = true;
            $breadcrumbs[] = $this->render($item);
        }
        $divider = '</li>' . '<span class="divider">></span>' . '<li>';
        $listitems = '<li>' . join($divider, $breadcrumbs) . '</li>';
        $title = '<span class="accesshide">' . get_string('pagepath') . '</span>';

        return $title . "<ul class=\"breadcrumb\">$listitems</ul>";
    }

    /*
     * Overriding the custom_menu function ensures the custom menu is
     * always shown, even if no menu items are configured in the global
     * theme settings page.
     */
    public function custom_menu($custommenuitems = '') {
        global $CFG;

        if (!empty($CFG->custommenuitems)) {
            $custommenuitems .= $CFG->custommenuitems;
        }

        if (theme_kommit_show_hidden_blocks()) {
            $custommenuitems .= "\r\n" . get_string('adminmenuentry', 'theme_kommit') . '|#hidden-blocks-admin';
        }

        $custommenu = new custom_menu($custommenuitems, current_language());

        return $this->render_custom_menu($custommenu);
    }

    /*
     * This renders the bootstrap top menu.
     *
     * This renderer is needed to enable the Bootstrap style navigation.
     */
    protected function render_custom_menu(custom_menu $menu) {
        global $CFG, $USER;

        // TODO: eliminate this duplicated logic, it belongs in core, not
        // here. See MDL-39565.

        $content = '<ul class="nav navbar-nav">';
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }

        return $content . '</ul>';
    }

    /*
     * Overriding the custom_menu function ensures the custom menu is
     * always shown, even if no menu items are configured in the global
     * theme settings page.
     */
    public function user_menu($user = null, $withlinks = null) {
        global $CFG;

        if (isloggedin()) {
            return parent::user_menu($user, $withlinks);
        }
    }

    /*
     * This code renders the custom menu items for the
     * bootstrap dropdown menu.
     */
    protected function render_custom_menu_item(custom_menu_item $menunode, $level = 0) {
        static $submenucount = 0;

        if ($menunode->has_children()) {

            if ($level == 1) {
                $dropdowntype = 'dropdown';
            } else {
                $dropdowntype = 'dropdown-submenu';
            }

            $content = html_writer::start_tag('li', array('class' => $dropdowntype));
            // If the child has menus render it as a sub menu.
            $submenucount++;
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_' . $submenucount;
            }
            $content .= html_writer::start_tag('a', array('href' => $url, 'class' => 'dropdown-toggle',
                'data-toggle' => 'dropdown', 'title' => $menunode->get_title()));
            $content .= $menunode->get_text();
            if ($level == 1) {
                $content .= '<b class="caret"></b>';
            }
            $content .= '</a>';
            $content .= '<ul class="dropdown-menu">';
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode, 0);
            }
            $content .= '</ul>';
        } else {
            $content = '<li>';
            // The node doesn't have children so produce a final menuitem.
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('title' => $menunode->get_title()));
        }

        return $content;
    }

    /**
     * Renders tabtree
     *
     * @param tabtree $tabtree
     *
     * @return string
     */
    protected function render_tabtree(tabtree $tabtree) {
        if (empty($tabtree->subtree)) {
            return '';
        }
        $firstrow = $secondrow = '';
        foreach ($tabtree->subtree as $tab) {
            $firstrow .= $this->render($tab);
            if (($tab->selected || $tab->activated) && !empty($tab->subtree) && $tab->subtree !== array()) {
                $secondrow = $this->tabtree($tab->subtree);
            }
        }

        return html_writer::tag('ul', $firstrow, array('class' => 'nav nav-tabs')) . $secondrow;
    }

    /**
     * Renders tabobject (part of tabtree)
     *
     * This function is called from {@link core_renderer::render_tabtree()}
     * and also it calls itself when printing the $tabobject subtree recursively.
     *
     * @param tabobject $tab
     *
     * @return string HTML fragment
     */
    protected function render_tabobject(tabobject $tab) {
        if ($tab->selected or $tab->activated) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'active'));
        } else if ($tab->inactive) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'disabled'));
        } else {
            if (!($tab->link instanceof moodle_url)) {
                // Backward compatible when link was passed as quoted string.
                $link = "<a href=\"$tab->link\" title=\"$tab->title\">$tab->text</a>";
            } else {
                $link = html_writer::link($tab->link, $tab->text, array('title' => $tab->title));
            }

            return html_writer::tag('li', $link);
        }
    }

    /**
     * This code renders the navbar button to control the display of the custom menu
     * on smaller screens.
     *
     * Do not display the button if the menu is empty.
     *
     * @return string HTML fragment
     */
    protected function navbar_button() {
        global $CFG;

        if (empty($CFG->custommenuitems) && $this->lang_menu() == '') {
            return '';
        }

        $iconbar = html_writer::tag('span', '', array('class' => 'icon-bar'));
        $barwrapper = html_writer::tag('div', $iconbar . "\n" . $iconbar . "\n" . $iconbar, array(
            'class' => 'bar-wrapper'
        ));
        $button = html_writer::tag('a', '', array(
            'class' => 'btn btn-navbar',
            'data-toggle' => 'collapse',
            'data-target' => '.nav-collapse'
        ));
        $outerwrapper = html_writer::tag('div', $barwrapper . $button, array(
            'class' => 'outer-wrapper'
        ));

        return $outerwrapper;
    }


    /**
     * Returns HTML to display a "Turn editing on/off" button in a form.
     *
     * @param moodle_url $url The URL + params to send through when clicking the button
     *
     * @return string HTML the button
     * Written by G J Bernard
     * Modified by Jon Jack
     */
    public function edit_button(moodle_url $url) {
        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $contain = 'toggle_container_active';
            $switch = 'toggle_switch_active';
            $title = get_string('editon', 'theme_kommit');
            $edit = 'edit_text_active';
            $label = get_string('edit_label', 'theme_kommit');


        } else {
            $url->param('edit', 'on');
            $contain = 'toggle_container';
            $switch = 'toggle_switch';
            $title = get_string('editoff', 'theme_kommit');
            $edit = 'edit_text';
            $label = get_string('edit_label', 'theme_kommit');

        }

        return
            html_writer::tag('span', $label, array('class' => 'edit-label')) .
            html_writer::start_tag('div', array('href' => $url, 'class' => $contain)) .
            html_writer::start_tag('span', array('class' => $edit)) . $title .
            html_writer::end_tag('span') .
            html_writer::tag('a',
                html_writer::start_tag('div', array('href' => $url, 'class' => $switch)) .
                html_writer::end_tag('div') .
                html_writer::end_tag('a'), array('href' => $url)) .
            html_writer::end_tag('div');
    }

    /**
     * Return the standard string that says whether you are logged in (and switched
     * roles/logged in as another user).
     *
     * @param bool $withlinks if false, then don't include any links in the HTML
     *                        produced.
     *                        If not set, the default is the nologinlinks option from the
     *                        theme config.php file, and if that is not set, then links
     *                        are included.
     *
     * @return string HTML fragment.
     */
    public function login_info($withlinks = null) {
        global $USER, $CFG, $DB, $SESSION;

        if (during_initial_install()) {
            return '';
        }

        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        $loginpage = ((string)$this->page->url === get_login_url());
        $course = $this->page->course;
        if (\core\session\manager::is_loggedinas()) {
            $realuser = \core\session\manager::get_realuser();
            $fullname = fullname($realuser, true);
            if ($withlinks) {
                $loginastitle = get_string('loginas');
                $realuserinfo = " [<a href=\"$CFG->wwwroot/course/loginas.php?id=$course->id&amp;sesskey=" . sesskey() . "\"";
                $realuserinfo .= "title =\"" . $loginastitle . "\">$fullname</a>] ";
            } else {
                $realuserinfo = " [$fullname] ";
            }
        } else {
            $realuserinfo = '';
        }

        $loginurl = get_login_url();

        if (empty($course->id)) {
            // The $course->id is not defined during installation.
            return '';
        } else if (isloggedin()) {
            $context = context_course::instance($course->id);

            $fullname = fullname($USER, true);
            // Since Moodle 2.0 this link always goes to the public profile page (not the course profile page).
            if ($withlinks) {
                $linktitle = get_string('viewprofile');
                $username = "<a href=\"$CFG->wwwroot/user/profile.php?id=$USER->id\" title=\"$linktitle\">$fullname</a>";
            } else {
                $username = $fullname;
            }
            if (is_mnet_remote_user($USER) and $idprovider = $DB->get_record('mnet_host', array('id' => $USER->mnethostid))) {
                if ($withlinks) {
                    $username .= " from <a href=\"{$idprovider->wwwroot}\">{$idprovider->name}</a>";
                } else {
                    $username .= " from {$idprovider->name}";
                }
            }
            if (isguestuser()) {
                $loggedinas = $realuserinfo . get_string('loggedinasguest');
                if (!$loginpage && $withlinks) {
                    $loggedinas .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
                }
            } else if (is_role_switched($course->id)) { // Has switched roles.
                $rolename = '';
                if ($role = $DB->get_record('role', array('id' => $USER->access['rsw'][$context->path]))) {
                    $rolename = ': ' . role_get_name($role, $context);
                }
                $loggedinas = get_string('loggedinas', 'moodle', $username) . $rolename;
                if ($withlinks) {
                    $url = new moodle_url('/course/switchrole.php', array('id' => $course->id, 'sesskey' => sesskey(), 'switchrole' => 0, 'returnurl' => $this->page->url->out_as_local_url(false)));
                    $loggedinas .= ' (' . html_writer::tag('a', get_string('switchrolereturn'), array('href' => $url)) . ')';
                }
            } else {
                $loggedinas = $realuserinfo . get_string('loggedinas', 'moodle', $username);
                if ($withlinks) {
                    $loggedinas .= " (<a href=\"$CFG->wwwroot/login/logout.php?sesskey=" . sesskey() . "\">" .
                        get_string('logout') . '</a>)';
                }
            }
        } else {
            // Start change uh 2014-11-18.
            // Remove the text »You are not logged in.« and the brackets,
            // set the login link to bold.
            //            $loggedinas = get_string('loggedinnot', 'moodle');
            $loggedinas = '';
            if (!$loginpage && $withlinks) {
                $loggedinas .= " <a href=\"$loginurl\"><strong>" . get_string('login') . '</strong></a>';
            }
            // End change uh.
        }

        $loggedinas = '<div class="logininfo">' . $loggedinas . '</div>';

        if (isset($SESSION->justloggedin)) {
            unset($SESSION->justloggedin);
            if (!empty($CFG->displayloginfailures)) {
                if (!isguestuser()) {
                    // Include this file only when required.
                    require_once($CFG->dirroot . '/user/lib.php');
                    if ($count = user_count_login_failures($USER)) {
                        $loggedinas .= '<div class="loginfailures">';
                        $a = new stdClass();
                        $a->attempts = $count;
                        $loggedinas .= get_string('failedloginattempts', '', $a);
                        if (file_exists("$CFG->dirroot/report/log/index.php") &&
                            has_capability('report/log:view', context_system::instance())
                        ) {
                            $loggedinas .= ' (' . html_writer::link(new moodle_url('/report/log/index.php', array('chooselog' => 1,
                                    'id' => 0, 'modid' => 'site_errors')), get_string('logs')) . ')';
                        }
                        $loggedinas .= '</div>';
                    }
                }
            }
        }

        return $loggedinas;
    }

    /**
     * Get the inline style definition for the background image.
     *
     * Is called from the theme layout files. Returned CSS definition:
     * background-image: url("moodlepath/to/image");
     *
     * Example placement in the layout file:
     * <div class="hero-unit" style="<?php echo $OUTPUT->hero_img(); ?>">
     *
     * @return string The background image CSS definition
     */
    public function hero_img() {
        $out = '';
        $context = context_course::instance(1);

        $out = $this->get_bgimg_style($context);

        return $out;
    }

    /**
     * Get the edit button for the background image.
     *
     * Is called from the theme layout files.
     *
     * Example placement in the layout file:
     * <?php echo $OUTPUT->edit_hero_img(); ?>
     *
     * @return string The HTML for the edit button
     */
    public function edit_hero_img() {
        global $PAGE;

        $out = '';
        $context = context_course::instance(1);

        if ($PAGE->user_is_editing()) {
            $out = $this->editbtn($context, 1);
        }

        return $out;
    }

    /**
     * Create a link »Return to normal role« when the admin had switched role.
     *
     * Use the code from outputrenderers->login_info.
     *
     * @return string The HTML for the link
     */
    public function return_to_role() {
        global $CFG, $DB, $PAGE, $USER;
        $loggedinas = '';
        $withlinks = true;

        $course = $PAGE->course;
        if (\core\session\manager::is_loggedinas()) {
            $realuser = \core\session\manager::get_realuser();
            $fullname = fullname($realuser, true);
            if ($withlinks) {
                $loginastitle = get_string('loginas');
                $realuserinfo = " [<a href=\"$CFG->wwwroot/course/loginas.php?id=$course->id&amp;sesskey=" . sesskey() . "\"";
                $realuserinfo .= "title =\"" . $loginastitle . "\">$fullname</a>] ";
            } else {
                $realuserinfo = " [$fullname] ";
            }
            $fullname = fullname($USER, true);
            // Since Moodle 2.0 this link always goes to the public profile page (not the course profile page).
            if ($withlinks) {
                $linktitle = get_string('viewprofile');
                $username = "<a href=\"$CFG->wwwroot/user/profile.php?id=$USER->id\" title=\"$linktitle\">$fullname</a>";
            } else {
                $username = $fullname;
            }
            $loggedinas .= '<div class="returntorole">';
            $loggedinas .= $realuserinfo . get_string('loggedinas', 'moodle', $username);
            if ($withlinks) {
                $loggedinas .= " (<a href=\"$CFG->wwwroot/login/logout.php?sesskey=" . sesskey() . "\">" .
                    get_string('logout') . '</a>)';
            }
            $loggedinas .= '</div>';
        } else {
            $realuserinfo = '';
        }

        if (!is_role_switched($PAGE->course->id)) { // Has no switched roles.
            return $loggedinas;
        }

        $context = context_course::instance($PAGE->course->id);
        $fullname = fullname($USER, true);

        $rolename = '';
        if ($role = $DB->get_record('role', array('id' => $USER->access['rsw'][$context->path]))) {
            $rolename = ': ' . role_get_name($role, $context);
        }

        $loggedinas .= '<div class="returntorole">';
        $loggedinas .= get_string('loggedinas', 'moodle', $fullname) . $rolename;
        $url = new moodle_url('/course/switchrole.php',
            array('id' => $PAGE->course->id, 'sesskey' => sesskey(),
                'switchrole' => 0,
                'returnurl' => $this->page->url->out_as_local_url(false)));
        $loggedinas .= ' (' . html_writer::tag('a', get_string('switchrolereturn'),
                array('href' => $url)) . ')';
        $loggedinas .= '</div>';

        return $loggedinas;
    }

    /**
     * Return the Moodle course search form.
     *
     * @return string The HTML for the search form
     */
    public function search_form() {
        global $CFG;

        $out = '';
        $form = <<<T_END_HEREDOC
        <form id="topsearch" action="{{baseurl}}/course/search.php" method="get">
            <fieldset class="topsearchbox invisiblefieldset input-append">
                <label for="shorttopsearchbox">{{searchlabel}}</label>
                <input type="text" id="shorttopsearchbox" class="input-medium-" name="search"
                    placeholder="{{placeholder}}" value="">
                <button type="submit"><i class="icon-search icon-white"></i></button>
            </fieldset>
        </form>
        <script type="text/javascript">Y.one('#shorttopsearchbox').focus();</script>
T_END_HEREDOC;
        $baseurl = $CFG->wwwroot;
        $strsearchlabel = 'Search courses: ';
        $strplaceholder = 'Hva leter du etter?';

        $out = str_replace(
            array('{{baseurl}}', '{{searchlabel}}', '{{placeholder}}'),
            array($baseurl, $strsearchlabel, $strplaceholder),
            $form
        );

        return $out;
    }

    /**
     * Get the background image style with the Moodle URL.
     *
     * @param $context object The Moodle context
     *
     * @return string The CSS background image style
     */
    protected function get_bgimg_style_o($context) {
        // Img background.
        $bgimg = $this->bgimg_get_img(1, 1);

        $bgimgurl = '';
        $imgstyle = '';
        if (is_object($bgimg) && !empty($bgimg->imagepath)) {
            $bgimgurl = moodle_url::make_pluginfile_url(
                $context->id, 'local_background_image', 'picture', 1,
                '/', $bgimg->imagepath);
        }
        if (!empty($bgimgurl)) {
            $imgstyle = "background-image: url('" . $bgimgurl . "');";
        }

        return $imgstyle;
    }

    /**
     * Get the background image style with the Moodle URL.
     *
     * @param $context object The Moodle context
     *
     * @return string The CSS background image style
     */
    protected function get_bgimg_style($context) {
        $bgimgurl = $this->page->theme->setting_file_url('heroimg', 'heroimg');
        $imgstyle = '';
        if (!empty($bgimgurl)) {
            $imgstyle = "background-image: url('" . $bgimgurl . "');";
        }

        return $imgstyle;
    }

    /**
     * Get the background image edit button
     *
     * @param $context   object The Moodle context
     * @param $sectionid int The section id
     *
     * @return string The HTML for the edit button
     */
    protected function editbtn($context, $sectionid) {
        global $PAGE, $USER;

        $urlpicedit = $PAGE->theme->pix_url('t/edit', 'moodle');
        $streditimage = get_string('editimage', 'theme_kommit');
        $streditimagealt = get_string('editimage_alt', 'theme_kommit');

        return html_writer::link(
            $this->bgimg_moodle_url('editimage.php', array(
                'contextid' => $context->id,
                'sectionid' => $sectionid,
                'userid' => $USER->id)),
            html_writer::empty_tag('img', array(
                'src' => $urlpicedit,
                'alt' => $streditimagealt)) . '&nbsp;' . $streditimage,
            array(
                'title' => $streditimagealt,
                'class' => 'editbgimg'
            )
        );
    }

    /**
     * Get the course background image.
     *
     * @param $courseid
     * @param $sectionid
     *
     * @return bool|mixed
     */
    protected function bgimg_get_img($courseid, $sectionid) {
        global $CFG, $DB;

        if ((!$courseid) || (!$sectionid)) {
            return false;
        }

        // Check if the table exists.
        if ($DB->get_manager()->table_exists('background_image')) {
            if (!$bgimg = $DB->get_record('background_image', array('sectionid' => $sectionid))) {
                $bgimg = false;
            }

            return $bgimg;
        } else {
            return false;
        }
    }

    /**
     * Get the course URL with the given PHP file name and optional params
     *
     * @param string $url    The PHP file name
     * @param array  $params URL params needed to call the page
     *
     * @return moodle_url
     */
    protected function bgimg_moodle_url($url, array $params = null) {
        return new moodle_url('/local/background_image/' . $url, $params);
    }
}
