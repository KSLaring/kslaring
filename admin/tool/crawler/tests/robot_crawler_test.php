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
 *  Unit tests for link crawler robot
 *
 * @package    tool_crawler
 * @copyright  2016 Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden');

/**
 *  Unit tests for link crawler robot
 *
 * @package    tool_crawler
 * @copyright  2016 Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_crawler_test extends advanced_testcase {

    protected function setUp() {
        parent::setup();
        $this->resetAfterTest(true);

        $this->robot = new \tool_crawler\robot\crawler();

    }

    /**
     * @return array of test cases
     *
     * Combinations of base and relative parts of URL
     */
    public function absolute_urls_provider() {
        return array(
            array(
                'base' => 'http://test.com/sub/',
                'links' => array(
                    'mailto:me@test.com' => 'mailto:me@test.com',
                    '/file.php' => 'http://test.com/file.php',
                    'file.php' => 'http://test.com/sub/file.php',
                    '../sub2/file.php' => 'http://test.com/sub2/file.php',
                    'http://elsewhere.com/path/' => 'http://elsewhere.com/path/'
                )
            ),
            array(
                'base' => 'http://test.com/sub1/sub2/',
                'links' => array(
                    'mailto:me@test.com' => 'mailto:me@test.com',
                    '../../file.php' => 'http://test.com/file.php',
                    'file.php' => 'http://test.com/sub1/sub2/file.php',
                    '../sub3/file.php' => 'http://test.com/sub1/sub3/file.php',
                    'http://elsewhere.com/path/' => 'http://elsewhere.com/path/'
                )
            ),
            array(
                'base' => 'http://test.com/sub1/sub2/$%^/../../../',
                'links' => array(
                    'mailto:me@test.com' => 'mailto:me@test.com',
                    '/file.php' => 'http://test.com/file.php',
                    '/sub3/sub4//$%^/../../../file.php' => 'http://test.com/file.php',
                    'http://elsewhere.com/path/' => 'http://elsewhere.com/path/'
                    )
            ),
            array(
                'base' => 'http://test.com/sub1/sub2/file1.php',
                'links' => array(
                    'mailto:me@test.com' => 'mailto:me@test.com',
                    'file2.php' => 'http://test.com/sub1/sub2/file2.php',
                    '../file2.php' => 'http://test.com/sub1/file2.php',
                    'sub3/file2.php' => 'http://test.com/sub1/sub2/sub3/file2.php'
                )
            ),
            array(
                'base' => 'http://test.com/sub1/foo.php?id=12',
                'links' => array(
                    '/sub2/bar.php?id=34' => 'http://test.com/sub2/bar.php?id=34',
                    '/sub2/bar.php?id=34&foo=bar' => 'http://test.com/sub2/bar.php?id=34&foo=bar',
                ),
            ),
        );
    }

    /**
     * @dataProvider absolute_urls_provider
     *
     * Executing test cases returned by function provider()
     *
     * @param string $base Base part of URL
     * @param array $links Combinations of relative paths of URL and expected result
     */
    public function test_absolute_urls($base, $links) {
        foreach ($links as $key => $value) {
            $this->assertEquals($value, $this->robot->absolute_url($base, $key));
        }
    }

    /**
     * @return array of test cases
     *
     * Local and external URLs and their tricky combinations
     */
    public function should_auth_provider() {
        return array(
            array(false, 'http://my_moodle.com', 'http://evil.com/blah/http://my_moodle.com'),
            array(false, 'http://my_moodle.com', 'http://my_moodle.com.actually.im.evil.com'),
            array(true,  'http://my_moodle.com', 'http://my_moodle.com'),
            array(true,  'http://my_moodle.com', 'http://my_moodle.com/whatever/file1.php'),
            array(false, 'http://my_moodle.com/subdir', 'http://evil.com/blah/http://my_moodle.com/subdir'),
            array(false, 'http://my_moodle.com/subdir', 'http://my_moodle.com/subdir.actually.im.evil.com'),
            array(true,  'http://my_moodle.com/subdir', 'http://my_moodle.com/subdir'),
            array(true,  'http://my_moodle.com/subdir', 'http://my_moodle.com/subdir/whatever/file1.php'),
        );
    }

    /**
     * @dataProvider should_auth_provider
     *
     * Tests method should_be_authenticated($url) of class \tool_crawler\robot\crawler()
     *
     * @param bool $expected
     * @param string $myurl URL of current Moodle installation
     * @param string $testurl URL where we should authenticate
     */
    public function test_should_be_authenticated($expected, $myurl, $testurl) {
        global $CFG;
        $CFG->wwwroot = $myurl;
        $this->assertEquals((bool)$expected, $this->robot->should_be_authenticated($testurl));
        $this->resetAfterTest(true);
    }

    /**
     * Tests existence of new plugin parameter 'retentionperiod'
     */
    public function test_param_retention_exists() {
        $param = get_config('tool_crawler', 'retentionperiod');
        $this->assertNotEmpty($param);
    }
}


