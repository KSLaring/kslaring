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
 * Library functions
 *
 * @package   local_lessonexport
 * @copyright 2017 Adam King, SHEilds eLearning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir.'/pdflib.php');
require_once($CFG->dirroot.'/mod/lesson/locallib.php');

class local_lessonexport
{
    /** @var object */
    protected $cm;
    /** @var object */
    protected $lesson;
    /** @var local_lessonexport_info */
    protected $lessoninfo;

    const EXPORT_PDF = 'pdf';

    const MAX_EXPORT_ATTEMPTS = 2;

    protected static $exporttypes = array(self::EXPORT_PDF);

    public function __construct($cm, $lesson)
    {
        $this->cm = $cm;
        $this->lesson = $lesson;
        $exporttype = reset(self::$exporttypes);
        $this->lessoninfo = new local_lessonexport_info();

        if (in_array($exporttype, self::$exporttypes)) {
            $this->exporttype = $exporttype;
        } else {
            $this->exporttype = reset(self::$exporttypes); // Default to first type in the list.
        }
    }

    /**
     * Generate an array of links that should be placed on the page,
     * given that the user has the necessary permissions for the current
     * Course Module.
     *
     * @param object The Course Module from the current context.
     */
    public static function get_links($cm)
    {
        $context = context_module::instance($cm->id);
        $ret = array();

        // Add links for the different export types.
        foreach (self::$exporttypes as $exporttype) {
            $capability = 'local/lessonexport:export'.$exporttype;
            if (has_capability($capability, $context)) {
                $name = get_string('export'.$exporttype, 'local_lessonexport');
                $url = new moodle_url('/local/lessonexport/export.php', array('id' => $cm->id, 'type' => $exporttype));
                $ret[$name] = $url;
            }
        }
        return $ret;
    }

    /**
     * Ensure that the user has access to perform export operations where required.
     *
     * @throws required_capability_exception if the user does not have the capability.
     */
    public function check_access()
    {
        global $USER;
        $context = context_module::instance($this->cm->id);
        $capability = 'local/lessonexport:export'.$this->exporttype;
        require_capability($capability, $context);
    }

    /**
     * Generate the export file and (optionally) send direct to the user's browser.
     *
     * @param bool $download (optional) true to send the file directly to the user's browser
     * @return string the path to the generated file, if not downloading directly
     */
    public function export($download = true)
    {
        // Raise the max execution time to 5 min, not 30 seconds.
        @set_time_limit(300);

        $pages = $this->load_pages();

        $exp = $this->start_export($download);
        $this->add_coversheet($exp);
        foreach ($pages as $page) {
            $this->export_page($exp, $page);
        }
        return $this->end_export($exp, $download);
    }

    /**
     * The cron tasks to run every time the cron is run.
     * This includes checking the update_queue for changes to email
     * an exported document to the configured email address.
     */
    public static function cron()
    {
        $config = get_config('local_lessonexport');
        if (empty($config->publishemail)) {
            return; // No email specified.
        }
        if (!$destemail = trim($config->publishemail)) {
            return; // Email is empty.
        }
        if (empty($config->lastcron)) {
            return; // Don't export every lesson on the site the first time cron runs.
        }

        // Update the list of lessons waiting to be exported.
        self::update_queue($config);

        $touser = (object)array(
            'id' => -1,
            'email' => $destemail,
            'maildisplay' => 0,
        );
        foreach (get_all_user_name_fields(false) as $fieldname) {
            $touser->$fieldname = '';
        }

        $msg = get_string('lessonupdated_body', 'local_lessonexport');
        while ($lesson = self::get_next_from_queue()) {
            if ($lesson->exportattempts == self::MAX_EXPORT_ATTEMPTS) {
                // Already failed to export the maximum allowed times - drop an email to the user to let them know, then move on
                // to the next lesson to export.
                $lessonurl = new moodle_url('/mod/lesson/view.php', array('id' => $lesson->cm->id));
                $info = (object)array(
                    'name' => $lesson->name,
                    'url' => $lessonurl->out(false),
                    'exportattempts' => $lesson->exportattempts,
                );
                $failmsg = get_string('lessonexportfailed_body', 'local_lessonexport', $info);
                email_to_user($touser, $touser, get_string('lessonexportfailed', 'local_lessonexport', $lesson->name), $failmsg);
            }

            // Attempt the export.
            try {
                $export = new local_lessonexport($lesson->cm, $lesson, self::EXPORT_PDF);
                $filepath = $export->export(false);
                $filename = basename($filepath);
                email_to_user($touser, $touser, get_string('lessonupdated', 'local_lessonexport', $lesson->name), $msg, '',
                              $filepath, $filename, false);
                @unlink($filepath);

                // Export successful - update the queue.
                self::remove_from_queue($lesson);
            } catch (Exception $e) {
                print_r($e);
                print_r($lesson);
            }
        }
    }

    /**
     * Find any lessons that have been updated since we last refeshed the export queue.
     * Any lessons that have been updated will have thier export attempt count reset.
     *
     * @param $config
     */
    protected static function update_queue($config)
    {
        global $DB;

        if (empty($config->lastqueueupdate)) {
            $config->lastqueueupdate = $config->lastcron;
        }

        // Get a list of any lessons that have been changed since the last queue update.
        $sql = "SELECT DISTINCT l.id, l.lessonid
                  FROM {lesson} l
                  JOIN {lesson_pages} p ON p.lessonid = l.id AND p.timemodified > :lastqueueupdate
                 ORDER BY l.lessonid, l.id";
        $params = array('lastqueueupdate' => $config->lastqueueupdate);
        $lessons = $DB->get_records_sql($sql, $params);

        // Save a list of all lessons to be exported.
        $currentqueue = $DB->get_records('local_lessonexport_queue');
        foreach ($lessons as $lesson) {
            if (isset($currentqueue[$lesson->id])) {
                // A lesson already in the queue has been updated - reset the export attempts (if non-zero).
                $queueitem = $currentqueue[$lesson->id];
                if ($queueitem->exportattempts != 0) {
                    $DB->set_field('local_lessonexport_queue', 'exportattempts', 0, array('id' => $queueitem->id));
                }
            } else {
                $ins = (object)array(
                    'lessonid' => $lesson->id,
                    'exportattempts' => 0,
                );
                $DB->insert_record('local_lessonexport_queue', $ins, false);
            }
        }

        // Save the timestamp to detect any future lesson export changes.
        set_config('lastqueueupdate', time(), 'local_lessonexport');
    }

    /**
     * Get the next lesson in the queue - ignoring those that have already had too many export attempts.
     * The return object includes the lesson and cm as sub-objects.
     *
     * @return object|null null if none left to export
     */
    protected static function get_next_from_queue()
    {
        global $DB;

        static $cm = null;
        static $lesson = null;

        $sql = "SELECT l.id, q.id AS queueid, q.exportattempts
                FROM {local_lessonexport_queue} q
                JOIN {lesson} l ON l.id = q.lessonid
                WHERE q.exportattempts <= :maxexportattempts
                ORDER BY l.id";

        $params = array('maxexportattempts' => self::MAX_EXPORT_ATTEMPTS);
        $nextitems = $DB->get_records_sql($sql, $params, 0, 1); // Retrieve the first record found.
        $nextitem = reset($nextitems);
        if (!$nextitem) {
            return null;
        }

        // Update the 'export attempts' in the database.
        $DB->set_field('local_lessonexport_queue', 'exportattempts', $nextitem->exportattempts + 1, ['id' => $nextitem->queueid]);

        // Add the lesson + cm objects to the return object.
        if (!$lesson || $lesson->id != $nextitem->lessonid) {
            if (!$lesson == $DB->get_record('lesson', array('id' => $nextitem->lessonid))) {
                mtrace("Page updated for lesson ID {$nextitem->lessonid}, which does not exist\n");
                return self::get_next_from_queue();
            }
            if (!$cm = get_coursemodule_from_instance('lesson', $lesson->id)) {
                mtrace("Missing course module for lesson ID {$lesson->id}\n");
                return self::get_next_from_queue();
            }
        }
        $nextitem->lesson = $lesson;
        $nextitem->cm = $cm;

        return $nextitem;
    }

    /**
     * Remove the lesson from the export queue, after it has been successfully exported.
     *
     * @param object $lesson
     */
    protected static function remove_from_queue($lesson)
    {
        global $DB;
        $DB->delete_records('local_lessonexport_queue', array('id' => $lesson->queueid));
    }

    protected function load_pages()
    {
        global $DB, $USER;

        $lesson = new Lesson($this->lesson);
        $pages = $lesson->load_all_pages();
        $pageids = array_keys($pages);

        $context = context_module::instance($this->cm->id);

        foreach ($pages as $page) {
            $answers = $page->get_answers();
            $contents = $page->contents;

            // Append answers to the end of question pages.
            $contents = $this->format_answers($page);

            // Fix pluginfile urls.
            $contents = file_rewrite_pluginfile_urls($contents, 'pluginfile.php', $context->id,
                                                          'mod_lesson', 'page_contents', $page->id);

            $contents = format_text($contents, FORMAT_MOODLE, array('overflowdiv' => false, 'allowid' => true, 'para' => false));

            // Fix internal links.
            // Can't really use this with the content being a local variable.
            // $this->fix_internal_links($page, $pageids);

            // Note created/modified time (if earlier / later than already recorded).
            $this->lessoninfo->update_times($page->timecreated, $page->timemodified, $USER->id);

            $page->contents = $contents;
        }

        return $pages;
    }

    /**
     * Retrieve and format question pages to include answers.
     *
     * @param A Lesson page.
     * @return Formatted page contents.
     */
    protected function format_answers($page)
    {
        $pagetype = $page->get_typeid();
        $contents = $page->contents;
        $answers = $page->answers;
        $qtype = $page->qtype;

        // Don't look for answers in lesson types and don't print
        // short answer answer patterns.
        if ($pagetype == 1 || $pagetype == 20) {
            return $contents;
        }

        $pagetypes = array(
            1 => "shortanswer",
            2 => "truefalse",
            3 => "multichoice",
            5 => "matching",
            8 => "numerical",
            10 => "essay",
            20 => "lessonpage"
        );

        $pagetype = $pagetypes[$pagetype];

        $contents .= "<div class='export_answer_".$pagetype."_wrapper'>";

        foreach ($answers as $answer) {
            // If this is a matching question type, only print the answers, not responses.
            if ($pagetype == 5 && $answer->answerformat == 1) {
                continue;
            }

            $contents .= "<div class='export_answer_$pagetype'>$answer->answer</div>";
        }

        $contents .= "</div>";

        return $contents;
    }

    /**
     * Fix internal TOC links to include the pageid (to make them unique across all pages).
     * Replaces links to other pages with anchor links to '#pageid-[page id]'.
     * Replaces unnecessary links with blank anchors.
     *
     * @param page The page to fix.
     * @param padeids An array of page identifiers, from the loaded pages.
     * @see local_lessonexport::load_pages() for the array of pageids.
     */
    protected function fix_internal_links($page, $pageids)
    {
        if ($this->exporttype == self::EXPORT_PDF) {
            if (preg_match_all('|<a href="#([^"]+)"|', $page->contents, $matches)) {
                $anchors = $matches[1];
                foreach ($anchors as $anchor) {
                    $page->contents = str_replace($anchor, $anchor.'-'.$page->id, $page->contents);
                }
            }
        }

        // Replace links to other pages with anchor links to '#pageid-[page id]' (PDF).
        $baseurl = new moodle_url('/mod/lesson/view.php', array('pageid' => 'PAGEID'));
        $baseurl = $baseurl->out(false);
        $baseurl = preg_quote($baseurl);
        $baseurl = str_replace(array('&', 'PAGEID'), array('(&|&amp;)', '(\d+)'), $baseurl);
        if (preg_match_all("|$baseurl|", $page->contents, $matches)) {
            $ids = $matches[count($matches) - 1];
            $urls = $matches[0];
            foreach ($ids as $idx => $pageid) {
                if (in_array($pageid, $pageids)) {
                    $find = $urls[$idx];
                    $replace = '#pageid-'.$pageid;
                    $page->contents = str_replace($find, $replace, $page->contents);
                }
            }
        }

        // Replace any 'create' links with blank links.
        $baseurl = new moodle_url('/mod/lesson/create.php');
        $baseurl = $baseurl->out(false);
        $baseurl = preg_quote($baseurl);
        $baseurl = str_replace(array('&'), array('(&|&amp;)'), $baseurl);
        if (preg_match_all('|href="'.$baseurl.'[^"]*"|', $page->contents, $matches)) {
            foreach ($matches[0] as $createurl) {
                $page->contents = str_replace($createurl, '', $page->contents);
            }
        }

        // Remove any 'edit' links.
        $page->contents = preg_replace('|<a href="edit\.php.*?\[edit\]</a>|', '', $page->contents);
    }

    /**
     * The first step of exporting a document. This method creates an instance of the correct
     * export type and then sets the correct properties on it.
     *
     * @return object An instance of lessonexport_pdf
     */
    protected function start_export($download)
    {
        global $CFG;
        $exp = null;
        $exp = new lessonexport_pdf();
        $exp->setCourseModule($this->cm);
        $exp->setLesson($this->lesson);
        $restricttocontext = false;
        if ($download) {
            $restricttocontext = context_module::instance($this->cm->id);
        }
        $exp->use_direct_image_load($restricttocontext);
        $exp->SetMargins(20, 10, -1, true); // Set up wider left margin than default.

        return $exp;
    }

    /**
     * Add a page of content to the exported document.
     * A page is first added,the destination link is set and finally the HTML is written.
     *
     * @param exp The export object of type lessonexport_pdf.
     * @param page The page to add to the export object.
     */
    protected function export_page($exp, $page)
    {
        /** @var lessonexport_pdf $exp */
        $contents = $page->contents;
        $contents = '<h2>'.$page->title.'</h2>'.$contents;

        $exp->addPage();
        $exp->setDestination('pageid-'.$page->id);

        $pagebreaks = array();
        preg_match_all('/<[^>]+class\s*?=\s*?"\s*?pagebreak\s*?"\/?>(<\/[A-Za-z]+>)?/', $contents, $pagebreaks, PREG_OFFSET_CAPTURE);

        if (!empty($pagebreaks)) {
            $segments = preg_split('/<[^>]+class\s*?=\s*?"\s*?pagebreak\s*?"\/?>(<\/[A-Za-z]+>)?/', $contents);

            // Loop over pagebreak tags and split content, add pages.
            foreach ($segments as $index=>$segment) {
                $exp->writeHTML($segment);

                if ($index < sizeof($segments) - 1) {
                    $exp->addPage();
                }
            }
        } else {
            $exp->writeHTML($segment);
        }
    }

    /*
     * Finish exporting, with protection for PDF, export the document and
     * produce a file from the document object. The output can be a file name
     * or a path to the document depending on $download.
     *
     * @return string The file name or path to the document.
     */
    protected function end_export($exp, $download)
    {
        global $CFG;

        $filename = $this->get_filename($download);

        // Add the configured protection to the PDF
        $exp->protect($this->get_filename($download));

        if ($download) {
            $exp->Output($filename, 'D');
        } else {
            $exp->Output($filename, 'F');
        }

        // Remove 'dataroot' from the filename, so the email sending can put it back again.
        $filename = str_replace($CFG->dataroot.'/', '', $filename);

        return $filename;
    }

    /**
     * Generate a file name or file path based on whether the file will be
     * immediately downloaded or not.
     *
     * @param download A boolean of whether the file will be immediately downloaded.
     */
    protected function get_filename($download)
    {
        $info = (object)array(
            'timestamp' => userdate(time(), '%Y-%m-%d %H:%M'),
            'lessonname' => format_string($this->lesson->name),
        );
        $filename = get_string('filename', 'local_lessonexport', $info);
        $filename .= '.pdf';
        $filename = clean_filename($filename);

        if (!$download) {
            $filename = str_replace(' ', '_', $filename);
            $path = make_temp_directory('local_lessonexport');
            $filename = $path.'/'.$filename;
        }

        return $filename;
    }

    /**
     * Determine which export type to add the cover sheet to and
     * apply it.
     *
     * @param exp The export object to add the cover-sheet to.
     */
    protected function add_coversheet($exp)
    {
        $this->add_coversheet_pdf($exp);
    }

    /**
     * Add a cover sheet before all of the page contents containing the Lesson title,
     * the description, and other configurable data.
     *
     * @param exp The lessonexport_pdf object to add the cover-sheet to.
     */
    protected function add_coversheet_pdf(pdf $exp)
    {
        global $CFG;

        $config = get_config('local_lessonexport');

        $exp->startPage();
        // Rounded rectangle.
        // $exp->RoundedRect(9, 9, 192, 279, 6.5);
        // Logo.
        $exp->image($CFG->dirroot.'/local/lessonexport/pix/logo.png', 52, 27, 103, 36);

        // Title bar.
        $hexadecimal = null;
        if (empty($config->coverColour)) {
            $hexadecimal = '#12A053';
        } else {
            $hexadecimal = $config->coverColour;
        }

        $rgbColour = Util::hex_to_rgb($hexadecimal);
        $exp->Rect(0, 87.5, 220, 2.5, 'F', array(), $rgbColour);
        $exp->Rect(0, 90, 220, 30, 'F', array(), $rgbColour);
        $exp->Rect(0, 120, 220, 2.5, 'F', array(), $rgbColour);

        // Title text.
        $title = $this->lesson->name;
        $exp->SetFontSize(20);
        $exp->SetTextColorArray(array(255,255,255));
        $exp->Text(10, 100, $title, false, false, true, 0, 0, 'C', false, '', 1, false, 'T', 'C');
        $exp->SetTextColorArray(array(0,0,0)); // Set back to default colour.
        $exp->SetFontSize(11); // Set back to default.

        // Description.
        $description = format_text($this->lesson->intro, $this->lesson->introformat);
        $exp->writeHTMLCell(140, 40, 30, 130, $description);

        // Creation / modification / printing time.
        if ($info = $this->get_coversheet_info()) {
            $exp->writeHTMLCell(176, 20, 12, 255, $info);
        }
    }

    /**
     * Produce an array of information, from this instance, to apply to the
     * cover page of the document and turn it into HTML.
     *
     * @return string A HTML string of the imploded export data.
     */
    protected function get_coversheet_info()
    {
        $info = array();
        if ($this->lessoninfo->has_timemodified()) {
            $strinfo = (object)array(
                'timemodified' => $this->lessoninfo->format_timemodified(),
                'modifiedby' => $this->lessoninfo->get_modifiedby()
            );
            $info[] = get_string('modified', 'local_lessonexport', $strinfo);
        }
        if ($this->lessoninfo->has_timeprinted()) {
            $info[] = get_string('printed', 'local_lessonexport', $this->lessoninfo->format_timeprinted());
        }

        if ($info) {
            $info = implode("<br/>\n", $info);
        } else {
            $info = null;
        }

        return $info;
    }
}

/**
 * Insert the 'Export as PDF' link into the navigation.
 *
 * @param $unused
 */
function local_lessonexport_extends_navigation($unused)
{
    local_lessonexport_extend_navigation($unused);
}

function local_lessonexport_extend_navigation($unused)
{
    global $PAGE, $DB, $USER;

    $settingsnav = null;
    if (!$PAGE->cm || $PAGE->cm->modname != 'lesson') {
        return;
    } else {
        $settingsnav = $PAGE->settingsnav;
    }

    $groupid = groups_get_activity_group($PAGE->cm);
    $lesson = $DB->get_record('lesson', array('id' => $PAGE->cm->instance), '*', MUST_EXIST);

    if (!$links = local_lessonexport::get_links($PAGE->cm, $USER->id, $groupid)) {
        return;
    }

    $modulesettings = $settingsnav->get('modulesettings');
    if (!$modulesettings) {
        $modulesettings = $settingsnav->prepend(get_string('pluginadministration', 'mod_lesson'), null,
                                                navigation_node::TYPE_SETTING, null, 'modulesettings');
    }

    foreach ($links as $name => $url) {
        $modulesettings->add($name, $url, navigation_node::TYPE_SETTING);
    }

    // Use javascript to insert the pdf link.
    $jslinks = array();
    foreach ($links as $name => $url) {
        $link = html_writer::link($url, $name);
        $link = html_writer::div($link, 'exportpdf');
        $jslinks[] = $link;
    }
    $PAGE->requires->yui_module('moodle-local_lessonexport-printlinks', 'M.local_lessonexport.printlinks.init', array($jslinks));
}

function local_lessonexport_cron()
{
    local_lessonexport::cron();
}

/**
 * Class local_lessonexport_info
 */
class local_lessonexport_info
{
    protected $timecreated = 0;
    protected $timemodified = 0;
    protected $modifiedbyid = null;
    protected $modifiedby = null;
    protected $timeprinted = 0;

    public function __construct()
    {
        $this->timeprinted = time();
    }

    public function update_times($timecreated, $timemodified, $modifiedbyid)
    {
        if (!$this->timecreated || $this->timecreated > $timecreated) {
            $this->timecreated = $timecreated;
        }
        if ($this->timemodified < $timemodified) {
            $this->timemodified = $timemodified;
            if ($modifiedbyid != $this->modifiedbyid) {
                $this->modifiedbyid = $modifiedbyid;
                $this->modifiedby = null;
            }
        }
    }

    public function has_timecreated()
    {
        return (bool)$this->timecreated;
    }

    public function has_timemodified()
    {
        return (bool)$this->timemodified;
    }

    public function has_timeprinted()
    {
        return (bool)$this->timeprinted;
    }

    public function format_timecreated()
    {
        return userdate($this->timecreated);
    }

    public function format_timemodified()
    {
        return userdate($this->timemodified);
    }

    public function format_timeprinted()
    {
        return userdate($this->timeprinted);
    }

    public function get_modifiedby()
    {
        global $USER, $DB;

        if ($this->modifiedby === null) {
            if ($this->modifiedbyid == $USER->id) {
                $this->modifiedby = $USER;
            } else {
                $this->modifiedby = $DB->get_record('user', array('id' => $this->modifiedbyid), 'id, firstname, lastname');
            }
        }
        if (!$this->modifiedby) {
            return '';
        }
        return fullname($this->modifiedby);
    }
}

/**
 * Convert an image URL into a stored_file object, if it refers to a local file.
 * @param $fileurl
 * @param context $restricttocontext (optional) if set, only files from this lesson will be included
 * @return null|stored_file
 */
function local_lessonexport_get_image_file($fileurl, $restricttocontext = null)
{
    global $CFG;
    if (strpos($fileurl, $CFG->wwwroot.'/pluginfile.php') === false) {
        return null;
    }

    $fs = get_file_storage();
    $params = substr($fileurl, strlen($CFG->wwwroot.'/pluginfile.php'));
    if (substr($params, 0, 1) == '?') { // Slasharguments off.
        $pos = strpos($params, 'file=');
        $params = substr($params, $pos + 5);
    } else { // Slasharguments on.
        if (($pos = strpos($params, '?')) !== false) {
            $params = substr($params, 0, $pos - 1);
        }
    }
    $params = urldecode($params);
    $params = explode('/', $params);
    array_shift($params); // Remove empty first param.
    $contextid = (int)array_shift($params);
    $component = clean_param(array_shift($params), PARAM_COMPONENT);
    $filearea  = clean_param(array_shift($params), PARAM_AREA);
    $itemid = array_shift($params);

    if (empty($params)) {
        $filename = $itemid;
        $itemid = 0;
    } else {
        $filename = array_pop($params);
    }

    if (empty($params)) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $params).'/';
    }

    if ($restricttocontext) {
        if ($component != 'mod_lesson' || $contextid != $restricttocontext->id) {
            return null; // Only allowed to include files directly from this lesson.
        }
    }

    if (!$file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename)) {
        if ($itemid) {
            $filepath = '/'.$itemid.$filepath; // See if there was no itemid in the originalPath URL.
            $itemid = 0;
            $file = $fs->get_file($contextid, $component, $filename, $itemid, $filepath, $filename);
        }
    }

    if (!$file) {
        return null;
    }
    return $file;
}

/**
 * Class lessonexport_pdf
 */
class lessonexport_pdf extends pdf
{
    protected $directimageload = false;
    protected $restricttocontext = false;

    private $cm;
    private $lesson;

    public function setCourseModule($cm)
    {
        $this->cm = $cm;
    }

    public function setLesson($lesson)
    {
        $this->lesson = $lesson;
    }

    public function use_direct_image_load($restricttocontext = false)
    {
        $this->directimageload = true;
        $this->restricttocontext = $restricttocontext;

        $config = get_config('local_lessonexport');
        if (empty($config->customfont)) {
            $font = 'helvetica';
        } else {
            $font = $config->customfont;
        }

        $this->SetFont($font, '', 12);
    }

    /**
     * Override the existing function to:
     * a) Convert any spaces in filenames into '%20' (as TCPDF seems to incorrectly do the opposite).
     * b) Make any broken file errors non-fatal (replace the image with an error message).
     *
     * @param $file
     * @param string $x
     * @param string $y
     * @param int $w
     * @param int $h
     * @param string $type
     * @param string $link
     * @param string $align
     * @param bool $resize
     * @param int $dpi
     * @param string $palign
     * @param bool $ismask
     * @param bool $imgmask
     * @param int $border
     * @param bool $fitbox
     * @param bool $hidden
     * @param bool $fitonpage
     * @param bool $alt
     * @param array $altimgs
     */
    public function image(
        $file,
        $x = '',
        $y = '',
        $w = 0,
        $h = 0,
        $type = '',
        $link = '',
        $align = '',
        $resize = false,
        $dpi = 300,
        $palign = '',
        $ismask = false,
        $imgmask = false,
        $border = 0,
        $fitbox = false,
        $hidden = false,
        $fitonpage = false,
        $alt = false,
        $altimgs = array()
    ) {

        $config = get_config('local_lessonexport');
        $exportstrict = $config->exportstrict;

        if ($exportstrict) {
            if ($this->directimageload) {
                // Get the image data directly from the Moodle files API (needed when generating within cron, instead of downloading).
                $file = $this->get_image_data($file);
            } else {
                // Make sure the filename part of the URL is urlencoded (convert spaces => %20, etc.).
                if (strpos('pluginfile.php', $file) !== false) {
                    $urlparts = explode('/', $file);
                    $filename = array_pop($urlparts); // Get just the part at the end.
                    $filename = rawurldecode($filename); // Decode => make sure the URL isn't double-encoded.
                    $filename = rawurlencode($filename);
                    $urlparts[] = $filename;
                    $file = implode('/', $urlparts);
                }
            }
            try {
                parent::Image($file, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign,
                    $ismask, $imgmask, $border, $fitbox, $hidden, $fitonpage, $alt, $altimgs);
            } catch (Exception $e) {
                $this->writeHTML(get_string('failedinsertimage', 'local_lessonexport', $file));
            }
        } else {
            try {
                if ($this->directimageload) {
                    // Get the image data directly from the Moodle files API (needed when generating within cron, instead of downloading).
                    $file = $this->get_image_data($file);
                } else {
                    // Make sure the filename part of the URL is urlencoded (convert spaces => %20, etc.).
                    if (strpos('pluginfile.php', $file) !== false) {
                        $urlparts = explode('/', $file);
                        $filename = array_pop($urlparts); // Get just the part at the end.
                        $filename = rawurldecode($filename); // Decode => make sure the URL isn't double-encoded.
                        $filename = rawurlencode($filename);
                        $urlparts[] = $filename;
                        $file = implode('/', $urlparts);
                    }
                }
                parent::Image($file, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, $ismask, $imgmask, $border,
                            $fitbox, $hidden, $fitonpage, $alt, $altimgs);
            } catch (Exception $e) {
                // ignore
            }
        }
    }

    public function Header()
    {
        // No header.
    }

    public function Footer()
    {
        global $CFG;
        global $DB;

        $config = get_config('local_lessonexport');

        // TODO:- Configure font colours, fony style and single/double row.
        $this->SetTextColorArray(array(150,150,150));
        $this->SetFont('helvetica', '', 9);
        $this->SetY(-15);

        $frontCoverPageNumbers = $config->pdfFrontCoverPageNumbers;
        $contents = array(
            $config->pdfFooterTopLeft,
            $config->pdfFooterTopMiddle,
            $config->pdfFooterTopRight,
            $config->pdfFooterBottomLeft,
            $config->pdfFooterBottomMiddle,
            $config->pdfFooterBottomRight
        );

        $iterator = 1;
        $lcr = 'L';
        foreach ($contents as $content) {
            // Remove <p> and <br> tags in content to maintain Y position.
            $content = preg_replace("~<\/?p>|<br>~", "", $content);
            $pageNumber = $this->PageNo();
            // $numPages = $this->getNumPages();

            // Replace any [pagenumber] shortcodes the number on the current page.
            if (!(strpos($content, '[pagenumber]') === false)) {
                if ($frontCoverPageNumbers == true || $frontCoverPageNumbers == false && $pageNumber > 1) {
                    if ($frontCoverPageNumbers == true) {
                        $content = str_replace('[pagenumber]', $pageNumber, $content);
                    } else {
                        $content = str_replace('[pagenumber]', $pageNumber-1, $content);
                    }
                } else {
                    $content = '';
                }
            }

            // Replace any [numpages] shortcodes with the number of pages in the document.
            // if (!(strpos($content, '[numpages]') === false)) {
            //     if ($frontCoverPageNumbers == true || $frontCoverPageNumbers == false && $pageNumber > 1) {
            //         $content = str_replace('[numpages]', $numPages, $content);
            //     } else {
            //         $content = '';
            //     }
            // }

            // Replace any [date] shortcodes with the current date.
            if (!(strpos($content, '[date]') === false)) {
                $date = date("j F Y");
                $content = str_replace('[date]', $date, $content);
            }

            // Replace any [course] shortcodes with the current course context.
            if (!(strpos($content, '[coursename]') === false)) {
                $course = $this->cm->course;
                $course = $DB->get_record("course", array('id' => $course));
                $courseName = $course->fullname;

                $content = str_replace('[coursename]', $courseName, $content);
            }

            if (!(strpos($content, '[lessonname]') === false)) {
                $lesson = $this->lesson;
                $lessonName = $lesson->name;

                $content = str_replace('[lessonname]', $lessonName, $content);
            }

            // Reset the position to the left margin.
            // Each write will just align text from here.
            $this->SetX(15);
            $this->writeHTML(
                $content,
                false, true, true, false, $lcr
            );

            // Alter the text alignment based on the iterator.
            switch ($iterator) {
                case 1:
                    $lcr = 'C';
                    break;
                case 2:
                    $lcr = 'R';
                    break;
                case 3:
                    $lcr = 'L';
                    $this->SetY(-10);
                    $iterator = 0;
                    break;
            }

            $iterator++;
        }
    }

    /**
     * Copy the image data from the Moodle files API and return it directly.
     *
     * @param $fileurl
     * @return string either the originalPath fileurl param or the file content with '@' appended to the start.
     */
    protected function get_image_data($fileurl)
    {
        if ($file = local_lessonexport_get_image_file($fileurl, $this->restricttocontext)) {
            $fileurl = '@'.$file->get_content();
        }
        return $fileurl;
    }

    /**
     * Override the existing function to create anchor destinations for any '<a name="x">' tags.
     *
     * @param $dom
     * @param $key
     * @param $cell
     * @return mixed
     */
    protected function openhtmltaghandler($dom, $key, $cell)
    {
        $tag = $dom[$key];
        if (array_key_exists('name', $tag['attribute'])) {
            $this->setDestination($tag['attribute']['name']); // Store the destination for TOC links.
        }
        return parent::openHTMLTagHandler($dom, $key, $cell);
    }

    /**
     * Add protection to the PDF document, configured in the global administrative settings.
     *
     * @param file The file to apply the protection to.
     */
    public function protect($file)
    {
        global $CFG;

        $config = get_config('local_lessonexport');
        $userpassword = $config->pdfUserPassword;
        $ownerpassword = $config->pdfOwnerPassword;
        $defaults = array(
            get_string('printpermission', 'local_lessonexport'),
            get_string('modifypermission', 'local_lessonexport'),
            get_string('copypermission', 'local_lessonexport'),
            get_string('annotatepermission', 'local_lessonexport'),
            get_string('formfillpermission', 'local_lessonexport'),
            get_string('extractpermission', 'local_lessonexport'),
            get_string('assemblepermission', 'local_lessonexport'),
            get_string('highdefpermission', 'local_lessonexport'),
        );
        $permissions = $config->pdfProtection;

        if (strlen($permissions) > 0 && strrpos($permissions, ',') > 0) {
            $permissions = explode(',', $permissions);
        } elseif (strlen($permissions) > 0 && !(strrpos($permissions, ',') > 0)) {
            $permissions = array($permissions);
        } else {
            $permissions = array();
        }

        // Invert the selection so the user ticks boxes to _give_ permissions
        foreach ($permissions as $permission) {
            foreach ($defaults as $default) {
                if ($permission == $default) {
                    // delete the permission from the defaults
                    unset($defaults[array_search($permission, $defaults)]);
                }
            }
        }

        $permissions = $defaults;

        $this->SetProtection($permissions, $userpassword, $ownerpassword);
        $this->Output($file, 'D');

        return $file;
    }
}

class Util
{
    public static function hex_to_rgb($hex)
    {
        global $CFG;

        // If there is a hash symbol with the hex, remove it
        if (strpos($hex, '#') !== false) {
            $hex = substr($hex, 1);
        }

        // Split the hexadecimal into RGB chunks
        $hexColour = str_split($hex, 2);

        // Convert the base16 hex values into base10 decimals
        $rgbColour = array(
            hexdec($hexColour[0]),
            hexdec($hexColour[1]),
            hexdec($hexColour[2])
        );

        return $rgbColour;
    }
}
