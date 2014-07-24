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

/**
 * Renderers to align Moodle's HTML with that expected by kommit
 *
 * @package    theme_kommit
 * @copyright  2014
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_kommit_core_renderer extends core_renderer {
    // additional block regions
    public function blocks($region, $classes = array(), $tag = 'aside') {
        global $editing;

        $displayregion = $this->page->apply_theme_region_manipulations($region);
        if ($this->page->blocks->region_has_content($displayregion, $this) || $editing) {
//            return parent::blocks($region, $classes, $tag);
            $blocks_html = parent::blocks($region, $classes, $tag);
            if ($region === 'content-top' && strpos($blocks_html, 'mod_quiz_navblock') !== false) {
                $blocks_html = $this->process_quiz_nav_block($blocks_html);
            }

            return $blocks_html;
        }

        return '';
    }

    protected function process_quiz_nav_block($blocks_html) {
        $jsscript = <<<EOT
YUI().use('anim', 'node-event-simulate', function(Y) {
  var nbl = Y.one("#mod_quiz_navblock"),
    p = nbl.one("a.thispage"),
    p_id = p.getAttribute('id'),
    p_no = parseInt(p_id.replace(/\D/g, ''), 10),
    c = nbl.one("#qn-buttons-wrapper"),
    l_btn_ar = c.all('a:last-child'),
    l_btn = l_btn_ar.item(0),
    l_btn_id = l_btn.getAttribute('id'),
    l_btn_no = parseInt(l_btn_id.replace(/\D/g, ''), 10),
    btnp = nbl.one(".prev-btn"),
    btnn = nbl.one(".next-btn"),
    ppos = parseInt(p.getX()),
    cpos = parseInt(c.getX()),
    poff = ppos - cpos;

  btnp.on('click', function() {
    if (p_no > 1) {
      c.one('#quiznavbutton' + (p_no - 1)).simulate('click');
    }
  });

  btnn.on('click', function() {
    if (p_no < l_btn_no) {
      c.one('#quiznavbutton' + (p_no + 1)).simulate('click');
    }
  });

  if (poff > 0) {
    ani = new Y.Anim({
      node: c,
      to: {
        scrollLeft: poff
      }
    });
    ani.run();
  }

//  console.log(p_id, p_no, l_btn_no);
});

EOT;

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;
        libxml_use_internal_errors(true);
        $dom->loadHTML($blocks_html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        // Get the "qn_buttons" and its parent node
        $qn_buttons = $xpath->query("//div[contains(@class, 'qn_buttons')]")->item(0);
        $othernav = $xpath->query("//div[contains(@class, 'othernav')]")->item(0);
        $contentNode = $qn_buttons->parentNode;

        // Create a new wrapper
        $wrapper = $dom->createElement('div');
        $wrapper->setAttribute('id', 'qn-buttons-wrapper');

        $back_btn = $dom->createDocumentFragment();
        $back_btn->appendXML('<button class="btn prev-btn">&lt;</button>');
        $next_btn = $dom->createDocumentFragment();
        $next_btn->appendXML('<button class="btn next-btn">&gt;</button>');

        // Put the new wrapper around the button node and
        // add both to the content node.
        $contentNode->removeChild($qn_buttons);
        $wrapper->appendChild($qn_buttons);
        $contentNode->insertBefore($back_btn, $othernav);
        $contentNode->insertBefore($wrapper, $othernav);
        $contentNode->insertBefore($next_btn, $othernav);

        // Create the script node and append it at the end
        $blockscript = $dom->createElement('script', $jsscript);
        $blockscript->setAttribute('type', 'text/javascript');
        $wrapper->appendChild($blockscript);

        $body = $dom->getElementsByTagName('body')->item(0);
        $out = $this->getNodeInnerHTML($body);

        return $out;
    }

    protected function getNodeInnerHTML(DOMNode $oNode) {
        $oDom = new DOMDocument('1.0', 'utf-8');
        foreach ($oNode->childNodes as $oChild) {
            $oDom->appendChild($oDom->importNode($oChild, true));
        }

        return $oDom->saveHTML();
    }

    /*
   * This code replaces the icons in the Admin block with
   * FontAwesome variants where available.
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
            'i/hide' => 'eye',
            'i/import' => 'upload',
            'i/lock' => 'lock',
            'i/move_2d' => 'arrows',
            'i/navigationitem' => 'circle',
            'i/outcomes' => 'magic',
            'i/publish' => 'globe',
            'i/reload' => 'refresh',
            'i/report' => 'list-alt',
            'i/restore' => 'cloud-upload',
            'i/return' => 'repeat',
            'i/roles' => 'user',
            'i/settings' => 'cogs',
            'i/show' => 'eye-slash',
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
        $list_items = '<li>' . join($divider, $breadcrumbs) . '</li>';
        $title = '<span class="accesshide">' . get_string('pagepath') . '</span>';

        return $title . "<ul class=\"breadcrumb\">$list_items</ul>";
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
    public function user_menu() {
        global $CFG;
        $usermenu = new custom_menu('', current_language());

        return $this->render_user_menu($usermenu);
    }

    /*
     * This renders the bootstrap top menu.
     *
     * This renderer is needed to enable the Bootstrap style navigation.
     */
    protected function render_user_menu(custom_menu $menu) {
        global $CFG, $USER, $DB;

        $addusermenu = true;
        $addlangmenu = true;

        $langs = get_string_manager()->get_list_of_translations();
        if (count($langs) < 1
            or empty($CFG->langmenu)
            or ($this->page->course != SITEID and !empty($this->page->course->lang))
        ) {
            $addlangmenu = false;
        }

        if ($addlangmenu) {
            $language = $menu->add(get_string('language'), new moodle_url('#'), get_string('language'), 10000);
            foreach ($langs as $langtype => $langname) {
                $language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        if (!$menu->has_children() && $addlangmenu === false) {
            return '';
        }

        if ($addusermenu) {
            if (isloggedin()) {
                $usermenu = $menu->add(fullname($USER), new moodle_url('#'), fullname($USER), 10001);
                $usermenu->add('<i class="fa fa-lock"></i>' . get_string('logout'), new moodle_url('/login/logout.php',
                        array('sesskey' => sesskey(), 'alt' => 'logout')),
                    get_string('logout'));

                $usermenu->add('<i class="fa fa-user"></i>' . get_string('viewprofile'), new moodle_url('/user/profile.php',
                        array('id' => $USER->id)),
                    get_string('viewprofile'));

                $usermenu->add('<i class="fa fa-cog"></i>' . get_string('editmyprofile'),
                    new moodle_url('/user/edit.php',
                        array('id' => $USER->id)),
                    get_string('editmyprofile'));
            } else {
                /*
                 * Hide login in custom menu area
                $usermenu = $menu->add(get_string('login'), new moodle_url('/login/index.php'), get_string('login'), 10001);*/
            }
        }

        $content = '<ul class="nav navbar-nav navbar-right">';
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }

        return $content . '</ul>';
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
            $content .= html_writer::start_tag('a', array('href' => $url, 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'title' => $menunode->get_title()));
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
                // backward compartibility when link was passed as quoted string
                $link = "<a href=\"$tab->link\" title=\"$tab->title\">$tab->text</a>";
            } else {
                $link = html_writer::link($tab->link, $tab->text, array('title' => $tab->title));
            }

            return html_writer::tag('li', $link);
        }
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
     * Renders a single button widget.
     * Made the input button responsive, the input label does not adopt
     * to the line length - changed submit button from input element to button element.
     *
     * This will return HTML to display a form containing a single button.
     *
     * @param single_button $button
     *
*@return string HTML fragment
     */
    protected function render_single_button(single_button $button) {
        $attributes = array('type'     => 'submit',
//                            'value'    => $button->label,
                            'disabled' => $button->disabled ? 'disabled' : null,
                            'title'    => $button->tooltip);

        if ($button->actions) {
            $id = html_writer::random_id('single_button');
            $attributes['id'] = $id;
            foreach ($button->actions as $action) {
                $this->add_action_handler($action, $id);
            }
        }

        // Test the long and short text span
//        $button->label = '<span class="btn-long-text">' . $button->label . '</span>' .
//            '<span class="btn-short-text">' . substr($button->label, 0, 10) . ' ...</span>';
        // first the input element
//        $output = html_writer::empty_tag('input', $attributes);
        $output = html_writer::tag('button', $button->label, $attributes);

        // then hidden fields
        $params = $button->url->params();
        if ($button->method === 'post') {
            $params['sesskey'] = sesskey();
        }
        foreach ($params as $var => $val) {
            $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $var, 'value' => $val));
        }

        // then div wrapper for xhtml strictness
        $output = html_writer::tag('div', $output);

        // now the form itself around it
        if ($button->method === 'get') {
            $url = $button->url->out_omit_querystring(true); // url without params, the anchor part allowed
        } else {
            $url = $button->url->out_omit_querystring();     // url without params, the anchor part not allowed
        }
        if ($url === '') {
            $url = '#'; // there has to be always some action
        }
        $attributes = array('method' => $button->method,
                            'action' => $url,
                            'id'     => $button->formid);
        $output = html_writer::tag('form', $output, $attributes);

        // and finally one more wrapper with class
        return html_writer::tag('div', $output, array('class' => $button->class));
    }
}
