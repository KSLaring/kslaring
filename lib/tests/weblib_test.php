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
 * These tests rely on the rsstest.xml file on download.moodle.org,
 * from eloys listing:
 *   rsstest.xml: One valid rss feed.
 *   md5:  8fd047914863bf9b3a4b1514ec51c32c
 *   size: 32188
 *
 * If networking/proxy configuration is wrong these tests will fail..
 *
 * @package    core
 * @category   phpunit
 * @copyright  &copy; 2006 The Open University
 * @author     T.J.Hunt@open.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();


class core_weblib_testcase extends advanced_testcase {

    public function test_format_string() {
        global $CFG;

        // Ampersands.
        $this->assertSame("&amp; &amp;&amp;&amp;&amp;&amp; &amp;&amp;", format_string("& &&&&& &&"));
        $this->assertSame("ANother &amp; &amp;&amp;&amp;&amp;&amp; Category", format_string("ANother & &&&&& Category"));
        $this->assertSame("ANother &amp; &amp;&amp;&amp;&amp;&amp; Category", format_string("ANother & &&&&& Category", true));
        $this->assertSame("Nick's Test Site &amp; Other things", format_string("Nick's Test Site & Other things", true));

        // String entities.
        $this->assertSame("&quot;", format_string("&quot;"));

        // Digital entities.
        $this->assertSame("&11234;", format_string("&11234;"));

        // Unicode entities.
        $this->assertSame("&#4475;", format_string("&#4475;"));

        // < and > signs.
        $originalformatstringstriptags = $CFG->formatstringstriptags;

        $CFG->formatstringstriptags = false;
        $this->assertSame('x &lt; 1', format_string('x < 1'));
        $this->assertSame('x &gt; 1', format_string('x > 1'));
        $this->assertSame('x &lt; 1 and x &gt; 0', format_string('x < 1 and x > 0'));

        $CFG->formatstringstriptags = true;
        $this->assertSame('x &lt; 1', format_string('x < 1'));
        $this->assertSame('x &gt; 1', format_string('x > 1'));
        $this->assertSame('x &lt; 1 and x &gt; 0', format_string('x < 1 and x > 0'));

        $CFG->formatstringstriptags = $originalformatstringstriptags;
    }

    public function test_s() {
        // Special cases.
        $this->assertSame('0', s(0));
        $this->assertSame('0', s('0'));
        $this->assertSame('0', s(false));
        $this->assertSame('', s(null));

        // Normal cases.
        $this->assertSame('This Breaks &quot; Strict', s('This Breaks " Strict'));
        $this->assertSame('This Breaks &lt;a&gt;&quot; Strict&lt;/a&gt;', s('This Breaks <a>" Strict</a>'));

        // Unicode characters.
        $this->assertSame('Café', s('Café'));
        $this->assertSame('一, 二, 三', s('一, 二, 三'));

        // Don't escape already-escaped numeric entities. (Note, this behaviour
        // may not be desirable. Perhaps we should remove these tests and that
        // functionality, but we can only do that if we understand why it was added.)
        $this->assertSame('An entity: &#x09ff;.', s('An entity: &#x09ff;.'));
        $this->assertSame('An entity: &#1073;.', s('An entity: &#1073;.'));
        $this->assertSame('An entity: &amp;amp;.', s('An entity: &amp;.'));
        $this->assertSame('Not an entity: &amp;amp;#x09ff;.', s('Not an entity: &amp;#x09ff;.'));
    }

    public function test_format_text_email() {
        $this->assertSame("This is a TEST",
            format_text_email('<p>This is a <strong>test</strong></p>', FORMAT_HTML));
        $this->assertSame("This is a TEST",
            format_text_email('<p class="frogs">This is a <strong class=\'fishes\'>test</strong></p>', FORMAT_HTML));
        $this->assertSame('& so is this',
            format_text_email('&amp; so is this', FORMAT_HTML));
        $this->assertSame('Two bullets: '.core_text::code2utf8(8226).' *',
            format_text_email('Two bullets: &#x2022; &#8226;', FORMAT_HTML));
        $this->assertSame(core_text::code2utf8(0x7fd2).core_text::code2utf8(0x7fd2),
            format_text_email('&#x7fd2;&#x7FD2;', FORMAT_HTML));
    }

    public function test_obfuscate_email() {
        $email = 'some.user@example.com';
        $obfuscated = obfuscate_email($email);
        $this->assertNotSame($email, $obfuscated);
        $back = core_text::entities_to_utf8(urldecode($email), true);
        $this->assertSame($email, $back);
    }

    public function test_obfuscate_text() {
        $text = 'Žluťoučký koníček 32131';
        $obfuscated = obfuscate_text($text);
        $this->assertNotSame($text, $obfuscated);
        $back = core_text::entities_to_utf8($obfuscated, true);
        $this->assertSame($text, $back);
    }

    public function test_highlight() {
        $this->assertSame('This is <span class="highlight">good</span>', highlight('good', 'This is good'));
        $this->assertSame('<span class="highlight">span</span>', highlight('SpaN', 'span'));
        $this->assertSame('<span class="highlight">SpaN</span>', highlight('span', 'SpaN'));
        $this->assertSame('<span><span class="highlight">span</span></span>', highlight('span', '<span>span</span>'));
        $this->assertSame('He <span class="highlight">is</span> <span class="highlight">good</span>', highlight('good is', 'He is good'));
        $this->assertSame('This is <span class="highlight">good</span>', highlight('+good', 'This is good'));
        $this->assertSame('This is good', highlight('-good', 'This is good'));
        $this->assertSame('This is goodness', highlight('+good', 'This is goodness'));
        $this->assertSame('This is <span class="highlight">good</span>ness', highlight('good', 'This is goodness'));
    }

    public function test_replace_ampersands() {
        $this->assertSame("This &amp; that &nbsp;", replace_ampersands_not_followed_by_entity("This & that &nbsp;"));
        $this->assertSame("This &amp;nbsp that &nbsp;", replace_ampersands_not_followed_by_entity("This &nbsp that &nbsp;"));
    }

    public function test_strip_links() {
        $this->assertSame('this is a link', strip_links('this is a <a href="http://someaddress.com/query">link</a>'));
    }

    public function test_wikify_links() {
        $this->assertSame('this is a link [ http://someaddress.com/query ]', wikify_links('this is a <a href="http://someaddress.com/query">link</a>'));
    }

    /**
     * Test basic moodle_url construction.
     */
    public function test_moodle_url_constructor() {
        global $CFG;

        $url = new moodle_url('/index.php');
        $this->assertSame($CFG->wwwroot.'/index.php', $url->out());

        $url = new moodle_url('/index.php', array());
        $this->assertSame($CFG->wwwroot.'/index.php', $url->out());

        $url = new moodle_url('/index.php', array('id' => 2));
        $this->assertSame($CFG->wwwroot.'/index.php?id=2', $url->out());

        $url = new moodle_url('/index.php', array('id' => 'two'));
        $this->assertSame($CFG->wwwroot.'/index.php?id=two', $url->out());

        $url = new moodle_url('/index.php', array('id' => 1, 'cid' => '2'));
        $this->assertSame($CFG->wwwroot.'/index.php?id=1&amp;cid=2', $url->out());
        $this->assertSame($CFG->wwwroot.'/index.php?id=1&cid=2', $url->out(false));

        $url = new moodle_url('/index.php', null, 'test');
        $this->assertSame($CFG->wwwroot.'/index.php#test', $url->out());

        $url = new moodle_url('/index.php', array('id' => 2), 'test');
        $this->assertSame($CFG->wwwroot.'/index.php?id=2#test', $url->out());
    }

    /**
     * Tests moodle_url::get_path().
     */
    public function test_moodle_url_get_path() {
        $url = new moodle_url('http://www.example.org:447/my/file/is/here.txt?really=1');
        $this->assertSame('/my/file/is/here.txt', $url->get_path());

        $url = new moodle_url('http://www.example.org/');
        $this->assertSame('/', $url->get_path());

        $url = new moodle_url('http://www.example.org/pluginfile.php/slash/arguments');
        $this->assertSame('/pluginfile.php/slash/arguments', $url->get_path());
        $this->assertSame('/pluginfile.php', $url->get_path(false));
    }

    public function test_moodle_url_round_trip() {
        $strurl = 'http://moodle.org/course/view.php?id=5';
        $url = new moodle_url($strurl);
        $this->assertSame($strurl, $url->out(false));

        $strurl = 'http://moodle.org/user/index.php?contextid=53&sifirst=M&silast=D';
        $url = new moodle_url($strurl);
        $this->assertSame($strurl, $url->out(false));
    }

    /**
     * Test Moodle URL objects created with a param with empty value.
     */
    public function test_moodle_url_empty_param_values() {
        $strurl = 'http://moodle.org/course/view.php?id=0';
        $url = new moodle_url($strurl, array('id' => 0));
        $this->assertSame($strurl, $url->out(false));

        $strurl = 'http://moodle.org/course/view.php?id';
        $url = new moodle_url($strurl, array('id' => false));
        $this->assertSame($strurl, $url->out(false));

        $strurl = 'http://moodle.org/course/view.php?id';
        $url = new moodle_url($strurl, array('id' => null));
        $this->assertSame($strurl, $url->out(false));

        $strurl = 'http://moodle.org/course/view.php?id';
        $url = new moodle_url($strurl, array('id' => ''));
        $this->assertSame($strurl, $url->out(false));

        $strurl = 'http://moodle.org/course/view.php?id';
        $url = new moodle_url($strurl);
        $this->assertSame($strurl, $url->out(false));
    }

    public function test_moodle_url_round_trip_array_params() {
        $strurl = 'http://example.com/?a%5B1%5D=1&a%5B2%5D=2';
        $url = new moodle_url($strurl);
        $this->assertSame($strurl, $url->out(false));

        $url = new moodle_url('http://example.com/?a[1]=1&a[2]=2');
        $this->assertSame($strurl, $url->out(false));

        // For un-keyed array params, we expect 0..n keys to be returned.
        $strurl = 'http://example.com/?a%5B0%5D=0&a%5B1%5D=1';
        $url = new moodle_url('http://example.com/?a[]=0&a[]=1');
        $this->assertSame($strurl, $url->out(false));
    }

    public function test_compare_url() {
        $url1 = new moodle_url('index.php', array('var1' => 1, 'var2' => 2));
        $url2 = new moodle_url('index2.php', array('var1' => 1, 'var2' => 2, 'var3' => 3));

        $this->assertFalse($url1->compare($url2, URL_MATCH_BASE));
        $this->assertFalse($url1->compare($url2, URL_MATCH_PARAMS));
        $this->assertFalse($url1->compare($url2, URL_MATCH_EXACT));

        $url2 = new moodle_url('index.php', array('var1' => 1, 'var3' => 3));

        $this->assertTrue($url1->compare($url2, URL_MATCH_BASE));
        $this->assertFalse($url1->compare($url2, URL_MATCH_PARAMS));
        $this->assertFalse($url1->compare($url2, URL_MATCH_EXACT));

        $url2 = new moodle_url('index.php', array('var1' => 1, 'var2' => 2, 'var3' => 3));

        $this->assertTrue($url1->compare($url2, URL_MATCH_BASE));
        $this->assertTrue($url1->compare($url2, URL_MATCH_PARAMS));
        $this->assertFalse($url1->compare($url2, URL_MATCH_EXACT));

        $url2 = new moodle_url('index.php', array('var2' => 2, 'var1' => 1));

        $this->assertTrue($url1->compare($url2, URL_MATCH_BASE));
        $this->assertTrue($url1->compare($url2, URL_MATCH_PARAMS));
        $this->assertTrue($url1->compare($url2, URL_MATCH_EXACT));
    }

    public function test_out_as_local_url() {
        global $CFG;
        // Test http url.
        $url1 = new moodle_url('/lib/tests/weblib_test.php');
        $this->assertSame('/lib/tests/weblib_test.php', $url1->out_as_local_url());

        // Test https url.
        $httpswwwroot = str_replace("http://", "https://", $CFG->wwwroot);
        $url2 = new moodle_url($httpswwwroot.'/login/profile.php');
        $this->assertSame('/login/profile.php', $url2->out_as_local_url());

        // Test http url matching wwwroot.
        $url3 = new moodle_url($CFG->wwwroot);
        $this->assertSame('', $url3->out_as_local_url());

        // Test http url matching wwwroot ending with slash (/).
        $url3 = new moodle_url($CFG->wwwroot.'/');
        $this->assertSame('/', $url3->out_as_local_url());
    }

    /**
     * @expectedException coding_exception
     * @return void
     */
    public function test_out_as_local_url_error() {
        $url2 = new moodle_url('http://www.google.com/lib/tests/weblib_test.php');
        $url2->out_as_local_url();
    }

    /**
     * You should get error with modified url
     *
     * @expectedException coding_exception
     * @return void
     */
    public function test_modified_url_out_as_local_url_error() {
        global $CFG;

        $modifiedurl = $CFG->wwwroot.'1';
        $url3 = new moodle_url($modifiedurl.'/login/profile.php');
        $url3->out_as_local_url();
    }

    /**
     * Try get local url from external https url and you should get error
     *
     * @expectedException coding_exception
     */
    public function test_https_out_as_local_url_error() {
        $url4 = new moodle_url('https://www.google.com/lib/tests/weblib_test.php');
        $url4->out_as_local_url();
    }

    public function test_clean_text() {
        $text = "lala <applet>xx</applet>";
        $this->assertSame($text, clean_text($text, FORMAT_PLAIN));
        $this->assertSame('lala xx', clean_text($text, FORMAT_MARKDOWN));
        $this->assertSame('lala xx', clean_text($text, FORMAT_MOODLE));
        $this->assertSame('lala xx', clean_text($text, FORMAT_HTML));
    }

    public function test_qualified_me() {
        global $PAGE, $FULLME, $CFG;
        $this->resetAfterTest();

        $PAGE = new moodle_page();

        $FULLME = $CFG->wwwroot.'/course/view.php?id=1&xx=yy';
        $this->assertSame($FULLME, qualified_me());

        $PAGE->set_url('/course/view.php', array('id'=>1));
        $this->assertSame($CFG->wwwroot.'/course/view.php?id=1', qualified_me());
    }

    public function test_null_progres_trace() {
        $this->resetAfterTest(false);

        $trace = new null_progress_trace();
        $trace->output('do');
        $trace->output('re', 1);
        $trace->output('mi', 2);
        $trace->finished();
        $output = ob_get_contents();
        $this->assertSame('', $output);
        $this->expectOutputString('');
    }

    public function test_text_progres_trace() {
        $this->resetAfterTest(false);

        $trace = new text_progress_trace();
        $trace->output('do');
        $trace->output('re', 1);
        $trace->output('mi', 2);
        $trace->finished();
        $this->expectOutputString("do\n  re\n    mi\n");
    }

    public function test_html_progres_trace() {
        $this->resetAfterTest(false);

        $trace = new html_progress_trace();
        $trace->output('do');
        $trace->output('re', 1);
        $trace->output('mi', 2);
        $trace->finished();
        $this->expectOutputString("<p>do</p>\n<p>&#160;&#160;re</p>\n<p>&#160;&#160;&#160;&#160;mi</p>\n");
    }

    public function test_html_list_progress_trace() {
        $this->resetAfterTest(false);

        $trace = new html_list_progress_trace();
        $trace->output('do');
        $trace->output('re', 1);
        $trace->output('mi', 2);
        $trace->finished();
        $this->expectOutputString("<ul>\n<li>do<ul>\n<li>re<ul>\n<li>mi</li>\n</ul>\n</li>\n</ul>\n</li>\n</ul>\n");
    }

    public function test_progres_trace_buffer() {
        $this->resetAfterTest(false);

        $trace = new progress_trace_buffer(new html_progress_trace());
        ob_start();
        $trace->output('do');
        $trace->output('re', 1);
        $trace->output('mi', 2);
        $trace->finished();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertSame("<p>do</p>\n<p>&#160;&#160;re</p>\n<p>&#160;&#160;&#160;&#160;mi</p>\n", $output);
        $this->assertSame($output, $trace->get_buffer());

        $trace = new progress_trace_buffer(new html_progress_trace(), false);
        $trace->output('do');
        $trace->output('re', 1);
        $trace->output('mi', 2);
        $trace->finished();
        $this->assertSame("<p>do</p>\n<p>&#160;&#160;re</p>\n<p>&#160;&#160;&#160;&#160;mi</p>\n", $trace->get_buffer());
        $this->assertSame("<p>do</p>\n<p>&#160;&#160;re</p>\n<p>&#160;&#160;&#160;&#160;mi</p>\n", $trace->get_buffer());
        $trace->reset_buffer();
        $this->assertSame('', $trace->get_buffer());
        $this->expectOutputString('');
    }

    public function test_combined_progres_trace() {
        $this->resetAfterTest(false);

        $trace1 = new progress_trace_buffer(new html_progress_trace(), false);
        $trace2 = new progress_trace_buffer(new text_progress_trace(), false);

        $trace = new combined_progress_trace(array($trace1, $trace2));
        $trace->output('do');
        $trace->output('re', 1);
        $trace->output('mi', 2);
        $trace->finished();
        $this->assertSame("<p>do</p>\n<p>&#160;&#160;re</p>\n<p>&#160;&#160;&#160;&#160;mi</p>\n", $trace1->get_buffer());
        $this->assertSame("do\n  re\n    mi\n", $trace2->get_buffer());
        $this->expectOutputString('');
    }
}
