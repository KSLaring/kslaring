<?php
// This file is part of Moodle - http://moodle.org/
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
 * Renderer for outputting the netcourse course format.
 *
 * @package    format_netcourse
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/renderer.php');

/**
 * Basic renderer for netcourse format.
 *
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_netcourse_renderer extends format_section_renderer_base {

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string      $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_netcourse_renderer::section_edit_controls() only displays the 'Set current section' control
        // when editing mode is on we need to be sure that the link 'Turn editing mode on' is available for a user
        // who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     *
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'netcourse'));
    }

    /**
     * Generate the closing container html for a list of sections
     *
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     *
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the edit controls of a section
     *
     * @param stdClass $course        The course entry from DB
     * @param stdClass $section       The course_section entry from DB
     * @param bool     $onsectionpage true if being printed on a section page
     *
     * @return array of links with edit controls
     */
    protected function section_edit_controls($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();
        if (has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $controls[] = html_writer::link($url,
                    html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marked'),
                        'class' => 'icon ', 'alt' => get_string('markedthistopic'))),
                    array('title' => get_string('markedthistopic'), 'class' => 'editing_highlight'));
            } else {
                $url->param('marker', $section->section);
                $controls[] = html_writer::link($url,
                    html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marker'),
                        'class' => 'icon', 'alt' => get_string('markthistopic'))),
                    array('title' => get_string('markthistopic'), 'class' => 'editing_highlight'));
            }
        }

        return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
    }

    /**
     * Output the html for a single section page.
     *
     * @param stdClass $course         The course entry from DB
     * @param array    $sections       (argument not used)
     * @param array    $mods           (argument not used)
     * @param array    $modnames       (argument not used)
     * @param array    $modnamesused   (argument not used)
     * @param int      $displaysection The section number in the course which is being displayed
     */
    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        global $PAGE;
        $sectionnavlinks = array();

        $edit = optional_param('edit', false, PARAM_BOOL);

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $editing = $PAGE->user_is_editing() || $edit;

        // Can we view the section in question?
        if (!($sectioninfo = $modinfo->get_section_info($displaysection))) {
            // This section doesn't exist.
            print_error('unknowncoursesection', 'error', null, $course->fullname);

            return;
        }

        if (!$sectioninfo->uservisible) {
            if (!$course->hiddensections) {
                echo $this->start_section_list();
                echo $this->section_hidden($displaysection);
                echo $this->end_section_list();
            }

            // Can't view this section.
            return;
        }

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, $displaysection);
        $thissection = $modinfo->get_section_info(0);

        if ($editing) {
            if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                echo $this->start_section_list();
                echo $this->section_header($thissection, $course, true, $displaysection);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
                echo $this->courserenderer->course_section_add_cm_control($course, 0, $displaysection);
                echo $this->section_footer();
                echo $this->end_section_list();
            }
        }

        // Start single-section div.
        echo html_writer::start_tag('div', array('class' => 'single-section'));

        // The requested section page.
        $thissection = $modinfo->get_section_info($displaysection);

        // Title with section navigation links.
        if ($editing) {
            $sectionnavlinks = $this->get_nav_links($course, $modinfo->get_section_info_all(), $displaysection);
        }
        $sectiontitle = '';
        $sectiontitle .= html_writer::start_tag('div', array('class' => 'section-navigation navigationtitle'));

        if ($editing) {
            $sectiontitle .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
            $sectiontitle .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));
        }

        // Title attributes.
        $classes = 'sectionname';
        if (!$thissection->visible) {
            $classes .= ' dimmed_text';
        }
        $sectiontitle .= $this->output->heading(get_section_name($course, $displaysection), 3, $classes);

        $sectiontitle .= html_writer::end_tag('div');
        echo $sectiontitle;

        // Now the list of sections..
        echo $this->start_section_list();

        echo $this->section_header($thissection, $course, true, $displaysection);
        // Show completion help icon.
        if ($editing) {
            $completioninfo = new completion_info($course);
            echo $completioninfo->display_help_icon();

            echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
            echo $this->courserenderer->course_section_add_cm_control($course, $displaysection, $displaysection);
        }
        echo $this->section_footer();
        echo $this->end_section_list();

        if ($editing) {
            // Display section bottom navigation.
            $sectionbottomnav = '';
            $sectionbottomnav .= html_writer::start_tag('div', array('class' => 'section-navigation mdl-bottom'));
            $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
            $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));
            $sectionbottomnav .= html_writer::tag('div', $this->section_nav_selection($course, $sections, $displaysection),
                array('class' => 'mdl-align'));
            $sectionbottomnav .= html_writer::end_tag('div');
            echo $sectionbottomnav;
        }

        // Close single-section div.
        echo html_writer::end_tag('div');
    }

    /**
     * Renders the special course naviagtion above the course content
     *
     * @param renderable $specialnav
     *
     * @return string The rendered HTML
     */
    public function render_format_netcourse_specialnav($specialnav) {
        return html_writer::tag('div', $specialnav->text, array(
            'class' => 'netcourse-header'
        ));
    }
}

/**
 * The course navigation tree render methods.
 * Copied from the navigation block.
 */
class format_netcourse_fakeblock_renderer extends plugin_renderer_base {
    /**
     * Returns the content of the course_navigation tree.
     *
     * @param navigation_node $course_navigation
     * @param int             $expansionlimit
     * @param array           $options
     *
     * @return string $content
     */
    public function course_navigation_tree(navigation_node $course_navigation,
        $expansionlimit, array $options = array()) {
        $course_navigation->add_class('course_navigation_node');
        $content = $this->course_navigation_node(array($course_navigation),
            array('class' => 'block_tree list'), $expansionlimit, $options);
        if (isset($course_navigation->id) && !is_numeric($course_navigation->id) &&
            !empty($content)
        ) {
            $content = $this->output->box($content, 'block_tree_box', $course_navigation->id);
        }

        return $content;
    }

    /**
     * Produces a course_navigation node for the course_navigation tree
     *
     * @param array $items
     * @param array $attrs
     * @param int   $expansionlimit
     * @param array $options
     * @param int   $depth
     *
     * @return string
     */
    protected function course_navigation_node_($items, $attrs = array(),
        $expansionlimit = null, array $options = array(), $depth = 1) {

        // Exit if empty, we don't want an empty ul element.
        if (count($items) == 0) {
            return '';
        }

        // Array of nested li elements.
        $lis = array();
        foreach ($items as $item) {
            if (!$item->display && !$item->contains_active_node()) {
                continue;
            }
            $content = $item->get_content();
            $title = $item->get_title();

            $isexpandable = (empty($expansionlimit) ||
                ($item->type > navigation_node::TYPE_ACTIVITY ||
                    $item->type < $expansionlimit) ||
                ($item->contains_active_node() && $item->children->count() > 0));
            $isbranch = $isexpandable && ($item->children->count() > 0 ||
                    ($item->has_children() && (isloggedin() ||
                            $item->type <= navigation_node::TYPE_CATEGORY)));

            // Skip elements which have no content and no action - no point in showing them.
            if (!$isexpandable && empty($item->action)) {
                continue;
            }

            $hasicon = ((!$isbranch || $item->type == navigation_node::TYPE_ACTIVITY ||
                    $item->type == navigation_node::TYPE_RESOURCE) &&
                $item->icon instanceof renderable);

            if ($hasicon) {
                $icon = $this->output->render($item->icon);
            } else {
                $icon = '';
            }
            $content = $icon . $content; // Use CSS for spacing of icons.
            if ($item->helpbutton !== null) {
                $content = trim($item->helpbutton) . html_writer::tag('span',
                        $content, array('class' => 'clearhelpbutton'));
            }

            if ($content === '') {
                continue;
            }

            $attributes = array();
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            if (is_string($item->action) || empty($item->action) ||
                (($item->type === navigation_node::TYPE_CATEGORY ||
                        $item->type === navigation_node::TYPE_MY_CATEGORY) &&
                    empty($options['linkcategories']))
            ) {
                // Add tab support to span but still maintain character stream sequence.
                $attributes['tabindex'] = '0';
                $content = html_writer::tag('span', $content, $attributes);
            } else if ($item->action instanceof action_link) {
                // TODO: to be replaced with something else.
                $link = $item->action;
                $link->text = $icon . $link->text;
                $link->attributes = array_merge($link->attributes, $attributes);
                $content = $this->output->render($link);
                $linkrendered = true;
            } else if ($item->action instanceof moodle_url) {
                // Remove the $isbranch option for activity nodes to deactivate
                // the optional activity offered direct links.
                $isbranch = false;
                $action_url = $item->action;
                $content = html_writer::link($action_url, $content, $attributes);
            }

            // This applies to the li item which contains all child lists too.
            $liclasses = array($item->get_css_type(), 'depth_' . $depth);
            $liexpandable = array();
            if ($item->has_children() && (!$item->forceopen || $item->collapse)) {
                $liclasses[] = 'collapsed';
            }
            if ($isbranch) {
                $liclasses[] = 'contains_branch';
                $liexpandable = array('aria-expanded' =>
                    in_array('collapsed', $liclasses) ? "false" : "true");
            } else if ($hasicon) {
                $liclasses[] = 'item_with_icon';
            }
            if ($item->isactive === true) {
                $liclasses[] = 'current_branch';
            }
            $liattr = array('class' => join(' ', $liclasses)) + $liexpandable;
            // Class attribute on the div item which only contains the item content.
            $divclasses = array('tree_item');
            if ($isbranch) {
                $divclasses[] = 'branch';
            } else {
                $divclasses[] = 'leaf';
            }
            if ($hasicon) {
                $divclasses[] = 'hasicon';
            }
            if (!empty($item->classes) && count($item->classes) > 0) {
                $divclasses[] = join(' ', $item->classes);
            }
            $divattr = array('class' => join(' ', $divclasses));
            if (!empty($item->id)) {
                $divattr['id'] = $item->id;
            }

            // Don't render the course name in the navigation (node depth 1).
            if ($depth === 1) {
                $content = '';
            } else {
                $content = html_writer::tag('p', $content, $divattr);
            }

            if ($isexpandable) {
                $content .= $this->course_navigation_node($item->children,
                    array(), $expansionlimit, $options, $depth + 1);
            }
            if (!empty($item->preceedwithhr) && $item->preceedwithhr === true) {
                $content = html_writer::empty_tag('hr') . $content;
            }
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }

        if (count($lis)) {
            return html_writer::tag('ul', implode("\n", $lis), $attrs);
        } else {
            return '';
        }
    }

    /**
     * Produces a navigation node for the navigation tree
     *
     * @param navigation_node[] $items
     * @param array             $attrs
     * @param int               $expansionlimit
     * @param array             $options
     * @param int               $depth
     *
     * @return string
     */
    protected function course_navigation_node($items, $attrs = array(), $expansionlimit = null, array $options = array(), $depth = 1) {
        // Exit if empty, we don't want an empty ul element.
        if (count($items) === 0) {
            return '';
        }

        // Turn our navigation items into list items.
        $lis = array();
        // Set the number to be static for unique id's.
        static $number = 0;
        foreach ($items as $item) {
            $number++;
            if (!$item->display && !$item->contains_active_node()) {
                continue;
            }

            $isexpandable = (empty($expansionlimit) || ($item->type > navigation_node::TYPE_ACTIVITY || $item->type < $expansionlimit) || ($item->contains_active_node() && $item->children->count() > 0));

            // Skip elements which have no content and no action - no point in showing them.
            if (!$isexpandable && empty($item->action)) {
                continue;
            }

            $id = $item->id ? $item->id : html_writer::random_id();
            $content = $item->get_content();
            $title = $item->get_title();
            if ($depth <= 1) {
                $ulattr = ['id' => $id . '_group', 'role' => 'group'];
            } else {
                $ulattr = [
                    'id' => $id . '_group',
                    'role' => 'group',
                    'class' => 'collapse'
                ];

            }
            $liattr = ['class' => [$item->get_css_type(), 'depth_' . $depth]];
            $pattr = ['class' => ['tree_item'], 'role' => 'treeitem'];
            $pattr += !empty($item->id) ? ['id' => $item->id] : [];
            $isbranch = $isexpandable && ($item->children->count() > 0 || ($item->has_children() && (isloggedin() ||
                            $item->type <= navigation_node::TYPE_CATEGORY)));
            $hasicon = ((!$isbranch || $item->type == navigation_node::TYPE_ACTIVITY ||
                    $item->type == navigation_node::TYPE_RESOURCE) && $item->icon instanceof renderable);
            $icon = '';

            if ($hasicon) {
                $liattr['class'][] = 'item_with_icon';
                $pattr['class'][] = 'hasicon';
                $icon = $this->output->render($item->icon);
                // Because an icon is being used we're going to wrap the actual content in a span.
                // This will allow designers to create columns for the content, as we've done in styles.css.
                $content = $icon . html_writer::span($content, 'item-content-wrap');
            }
            if ($item->helpbutton !== null) {
                $content = trim($item->helpbutton) . html_writer::tag('span', $content, array('class' => 'clearhelpbutton'));
            }
            if (empty($content)) {
                continue;
            }

            $nodetextid = 'label_' . $depth . '_' . $number;
            $attributes = array('tabindex' => '-1', 'id' => $nodetextid);
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            if (is_string($item->action) || empty($item->action) ||
                (($item->type === navigation_node::TYPE_CATEGORY || $item->type === navigation_node::TYPE_MY_CATEGORY) &&
                    empty($options['linkcategories']))
            ) {
                $content = html_writer::tag('span', $content, $attributes);
            } else if ($item->action instanceof action_link) {
                // TODO: to be replaced with something else.
                $link = $item->action;
                $link->text = $icon . html_writer::span($link->text, 'item-content-wrap');
                $link->attributes = array_merge($link->attributes, $attributes);
                $content = $this->output->render($link);
            } else if ($item->action instanceof moodle_url) {
                $content = html_writer::link($item->action, $content, $attributes);
            }

            // Reduce the branch behaviour to the sections (level 2 elements).
            if ($isbranch && $depth === 2) {
                $pattr['class'][] = 'branch';
                $liattr['class'][] = 'contains_branch';
                $pattr += ['aria-expanded' => ($item->has_children() && (!$item->forceopen || $item->collapse)) ? "false" : "true"];
                // No AJAX loading.
                //$pattr += ['aria-owns' => $id . '_group'];
                $pattr += [
                    'aria-owns' => $id . '_group',
                    'data-toggle' => 'collapse',
                    'data-target' => '#' . $id . '_group'
                ];
            }

            if ($item->isactive === true) {
                $liattr['class'][] = 'current_branch';
            }
            if (!empty($item->classes) && count($item->classes) > 0) {
                $pattr['class'] = array_merge($pattr['class'], $item->classes);
            }

            $liattr['class'] = join(' ', $liattr['class']);
            $pattr['class'] = join(' ', $pattr['class']);

            $pattr += $depth == 1 ? ['data-collapsible' => 'false'] : [];
            if (isset($pattr['aria-expanded']) && $pattr['aria-expanded'] === 'false') {
                $ulattr += ['aria-hidden' => 'true'];
            } else if (isset($pattr['aria-expanded']) && $pattr['aria-expanded'] === 'true') {
                $ulattr['class'] = 'collapse in';
            }

            // Create the structure. Don't render the course name in the navigation (node depth 1).
            if ($depth === 1) {
                $content = '';
            } else {
                $content = html_writer::tag('p', $content, $pattr);
            }

            if ($isexpandable) {
                $content .= $this->course_navigation_node($item->children, $ulattr, $expansionlimit, $options, $depth + 1);
            }
            if (!empty($item->preceedwithhr) && $item->preceedwithhr === true) {
                $content = html_writer::empty_tag('hr') . $content;
            }

            $liattr['aria-labelledby'] = $nodetextid;
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }

        if (count($lis) === 0) {
            // There is still a chance, despite having items, that nothing had content and no list items were created.
            return '';
        }

        // We used to separate using new lines, however we don't do that now, instead we'll save a few chars.
        // The source is complex already anyway.
        return html_writer::tag('ul', implode('', $lis), $attrs);
    }
}
