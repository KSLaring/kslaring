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
 * Friadmin tests
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_courselist_testcase extends advanced_testcase {

    protected function import_db_data_from_json($tablename) {
        global $CFG, $DB;

        $DB->delete_records($tablename);

        $f = file_get_contents($CFG->dirroot .
            '/local/friadmin/tests/fixtures/' . $tablename . '.json');

        $feed = json_decode($f, true);
        for ($i = 0; $i < count($feed['data']); $i++) {
            $row = array();

            foreach ($feed['data'][$i] as $key => $value) {
                $row[$key] = (is_numeric($value)) ? $value :
                    mysql_real_escape_string($value);
            }

            $DB->insert_record($tablename, $row);
        }
    }

    public function test_courselist_page_renderable() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $page = new local_friadmin_courselist_page();

        $this->assertTrue(is_object($page), "Renderabel page is an object");
        $this->assertObjectHasAttribute('data', $page);

        $this->assertNotEmpty($page->data->title);
        $this->assertEquals(get_string('courselist_title', 'local_friadmin'),
            $page->data->title);

        $url = new moodle_url('/local/friadmin/courselist.php');
        $this->assertNotEmpty($page->data->url);
        $this->assertEquals($url, $page->data->url);
    }

    public function test_courselist_page_renderer() {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();

        $output = $PAGE->get_renderer('local_friadmin');

        $this->assertTrue(is_object($output), "Renderer output is an object");
        $this->assertTrue(method_exists($output, 'render_local_friadmin_courselist_page'));
    }

    public function test_courselist_filter_renderable() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $filter = new local_friadmin_courselist_filter();

        $this->assertTrue(is_object($filter), "Renderabel filter is an object");
        $this->assertObjectHasAttribute('data', $filter);
    }

    public function test_courselist_filter_renderer() {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();

        $output = $PAGE->get_renderer('local_friadmin');

        $this->assertTrue(is_object($output), "Renderer output is an object");
        $this->assertTrue(method_exists($output, 'render_local_friadmin_courselist_filter'));
    }

    public function test_courselist_table_renderable() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $this->import_db_data_from_json('friadmin_courselist_dev');

        $baseurl = new moodle_url('/local/friadmin/courselist.php');

        $table = new local_friadmin_courselist_table($baseurl);

        $this->assertTrue(is_object($table), "Renderabel table is an object");
        $this->assertObjectHasAttribute('data', $table);

        $this->assertTrue(method_exists($table, 'get_table'));
        $tablehtml = $table->get_table();
        $this->assertTrue(is_string($tablehtml));
    }

    public function test_courselist_table_renderer() {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();

        $output = $PAGE->get_renderer('local_friadmin');

        $this->assertTrue(is_object($output), "Renderer output is an object");
        $this->assertTrue(method_exists($output, 'render_local_friadmin_courselist_table'));
    }

    public function test_friadmin_basics() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $friadmin = new local_friadmin\friadmin();

        $this->assertTrue(is_object($friadmin), "friadmin is an object");
        $this->assertTrue(method_exists($friadmin, 'init_page'));
        $this->assertTrue(method_exists($friadmin, 'setup_courselist_page'));
        $this->assertTrue(method_exists($friadmin, 'display_courselist_page'));
    }

    public function test_friadmin_set_courselist_references() {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();

        $friadmin = new local_friadmin\friadmin();

        $this->assertTrue(is_object($friadmin), "friadmin is an object");
        $this->assertTrue(method_exists($friadmin, 'set_courselist_references'));

        // Get the renderer for this plugin
        $output = $PAGE->get_renderer('local_friadmin');

        // Create the renderables
        $page = new local_friadmin_courselist_page();
        $filter = new local_friadmin_courselist_filter();
        $table = new local_friadmin_courselist_table($page->data->url);

        $friadmin->set_courselist_references($page, $filter, $table, $output);

        $this->assertInstanceOf('local_friadmin_renderer', $friadmin->output);
        $this->assertInstanceOf('local_friadmin_courselist_filter', $friadmin->filter);
        $this->assertInstanceOf('local_friadmin_courselist_page', $friadmin->page);
        $this->assertInstanceOf('local_friadmin_courselist_table', $friadmin->table);
    }
}
