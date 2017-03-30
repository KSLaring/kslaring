<?php
// This file is part of ksl
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

define('MY_LEVEL_STRUCTURE', 'level_');
defined('MOODLE_INTERNAL') || die();

class ksl
{
    /**
     * @param   integer $id It's the industrycode from the form
     * @return  array
     * @throws  Exception
     *
     * Function gets all the neccessary information about the selected industrycode
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function local_ksl_organizationsearch($id) {
        global $DB;
        $myusers = array();
        $rdo = null;

                $userquery = "SELECT  co.id,
                                      u.firstname, u.lastname,
                                      uinfo.data as 'Gender',
                                      u.email,
                                      course.fullname as 'coursename',
                                      role.shortname as 'role',
                                      co.name as 'levelthree',
                                      co_two.name as 'leveltwo',
                                      co_three.name as 'levelone',
                                      co_four.name as 'levelzero',
                                      ccomp.timecompleted as 'completed'
                              FROM  {user} u
                              JOIN  {user_info_competence_data}       cd        ON cd.userid = u.id
                         LEFT JOIN  {report_gen_companydata}          co        ON co.id = cd.companyid
                         LEFT JOIN  {report_gen_company_relation}     re        ON re.companyid = cd.companyid
                         LEFT JOIN  {user_info_data}                  uinfo     ON u.id = uinfo.userid
                         LEFT JOIN  {user_enrolments}                 enrol     ON enrol.userid = u.id
                         LEFT JOIN  {enrol}                           e         ON e.id = enrol.enrolid
                                                                                AND e.status = 0
                         LEFT JOIN  {role_assignments}                rolea     ON enrol.userid = rolea.userid
                         LEFT JOIN  {role}                            role      ON rolea.roleid = role.id
                              JOIN 	{course}                          course    ON course.id = e.courseid
                              JOIN 	{report_gen_company_relation}	  cr_two 	ON  cr_two.companyid = co.id
                              JOIN 	{report_gen_companydata}		  co_two 	ON  co_two.id = cr_two.parentid
                                                                                AND co_two.hierarchylevel = 2
                              JOIN	{report_gen_company_relation}	  cr_three  ON  cr_three.companyid = co_two.id
                              JOIN	{report_gen_companydata}		  co_three  ON  co_three.id = cr_three.parentid
                                                                                AND co_three.hierarchylevel = 1
                              JOIN	{report_gen_company_relation}	  cr_four   ON  cr_four.companyid = co_three.id
                              JOIN	{report_gen_companydata}		  co_four   ON  co_four.id = cr_four.parentid
                                                                                AND co_four.hierarchylevel = 0
                        LEFT JOIN   {course_completions}		      ccomp	    ON  ccomp.course = course.id
															                    AND ccomp.userid = enrol.userid
                             WHERE   uinfo.fieldid = 7 AND co.id = :myid";

        try {
            $params = array();
            $params['myid'] = $id;

            $rdo = $DB->get_records_sql($userquery, $params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $infouser = new stdClass();
                    $infouser->id = $instance->id;
                    $infouser->firstname = $instance->firstname;
                    $infouser->lastname = $instance->lastname;
                    $infouser->gender = $instance->gender;
                    $infouser->email = $instance->email;
                    $infouser->course = $instance->coursename;
                    $infouser->role = $instance->role;
                    $infouser->levelthree = $instance->levelthree;
                    $infouser->leveltwo = $instance->leveltwo;
                    $infouser->levelone = $instance->levelone;
                    $infouser->levelzero = $instance->levelzero;

                    if ($instance->gender = '1') {
                        $infouser->gender = 'Male';
                    } else if ($instance->gender = '2') {
                        $infouser->gender = 'Female';
                    } else {
                        $infouser->gender = '-';
                    }

                    $epoch = $instance->completed;
                    $dt = new DateTime("@$epoch");
                    $infouser->completed = $dt->format('d.m.Y');

                    $myusers[$instance->id] = $infouser;
                }
            }

            return $myusers;

        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch

    }  // end get_users

    /**
     * @param integer $id   It's the industrycode from the form
     * @return null|$mycount The amount of results
     * @throws Exception
     *
     * Function is neccessary for getting the correct page display
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function local_ksl_organizationsearch_count($id) {
        global $DB;
        $mycount = null;
        $rdo = null;

        $userquery = "SELECT  COUNT(co.id) as 'count'
                              FROM  {user} u
                              JOIN  {user_info_competence_data}       cd        ON cd.userid = u.id
                         LEFT JOIN  {report_gen_companydata}          co        ON co.id = cd.companyid
                         LEFT JOIN  {report_gen_company_relation}     re        ON re.companyid = cd.companyid
                         LEFT JOIN  {user_info_data}                  uinfo     ON u.id = uinfo.userid
                         LEFT JOIN  {user_enrolments}                 enrol     ON enrol.userid = u.id
                         LEFT JOIN  {enrol}                           e         ON e.id = enrol.enrolid
                                                                                AND e.status = 0
                         LEFT JOIN  {role_assignments}                rolea     ON enrol.userid = rolea.userid
                         LEFT JOIN  {role}                            role      ON rolea.roleid = role.id
                              JOIN 	{course}                          course    ON course.id = e.courseid
                              JOIN 	{report_gen_company_relation}	  cr_two 	ON  cr_two.companyid = co.id
                              JOIN 	{report_gen_companydata}		  co_two 	ON  co_two.id = cr_two.parentid
                                                                                AND co_two.hierarchylevel = 2
                              JOIN	{report_gen_company_relation}	  cr_three  ON  cr_three.companyid = co_two.id
                              JOIN	{report_gen_companydata}		  co_three  ON  co_three.id = cr_three.parentid
                                                                                AND co_three.hierarchylevel = 1
                              JOIN	{report_gen_company_relation}	  cr_four   ON  cr_four.companyid = co_three.id
                              JOIN	{report_gen_companydata}		  co_four   ON  co_four.id = cr_four.parentid
                                                                                AND co_four.hierarchylevel = 0
                         LEFT JOIN   {course_completions}		      ccomp	    ON  ccomp.course = course.id
															                    AND ccomp.userid = enrol.userid
                         WHERE   uinfo.fieldid = 7 AND co.id = :myid";

        try {
            $params = array();
            $params['myid'] = $id;

            $rdo = $DB->get_record_sql($userquery, $params);
            if ($rdo) {
                 $mycount = $rdo->count;
            }

            return $mycount;

        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch

    }  // end get_users

    /**
     * @param integer $industrycode It's the integer written in the form
     * @return array
     * @throws Exception
     *
     * Function used to get the neccessary information from the industrycode
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function local_ksl_industrysearch($industrycode) {

        // Variables!
        global $DB;
        $myusers = array();
        $rdo = null;

        $userquery = "SELECT 	uic.id									as 'ID',
		                        u.firstname                             as 'firstname',
		                        u.lastname                              as 'lastname',
                                uinfo.data								as 'gender',
                                u.email 								as 'email',
                                course.fullname							as 'coursename',
                                role.shortname							as 'role',
		                        ccomp.timecompleted						as 'completed',
                                co.name 								as 'levelthree',
                                co_two.name 							as 'leveltwo',
                                co_three.name							as 'levelone',
                                co_four.name							as 'levelzero'
                      FROM		mdl_user_info_competence_data uic
	                            JOIN	mdl_user 						  u         ON  u.id = uic.userid
																					AND u.deleted = 0
                                -- Level Three
                                JOIN	mdl_report_gen_companydata		  co        ON  co.id = uic.companyid
											                                        AND co.industrycode = :industrycode
                                -- Level Two
                                JOIN	mdl_report_gen_company_relation	  cr_two    ON  cr_two.companyid = co.id
                                JOIN	mdl_report_gen_companydata		  co_two    ON  co_two.id = cr_two.parentid
												                                    AND co_two.hierarchylevel = 2
                                -- Level One
	                            JOIN	mdl_report_gen_company_relation	  cr_three  ON  cr_three.companyid = co_two.id
                                JOIN	mdl_report_gen_companydata		  co_three  ON  co_three.id = cr_three.parentid
													                                AND co_three.hierarchylevel = 1
                                -- Level Zero
	                            JOIN	mdl_report_gen_company_relation	  cr_four   ON  cr_four.companyid = co_three.id
                                JOIN	mdl_report_gen_companydata		  co_four   ON  co_four.id = cr_four.parentid
													                                AND co_four.hierarchylevel = 0
  	                            -- Gender
	                       LEFT JOIN	mdl_user_info_data		          uinfo	    ON  u.id = uinfo.userid
	                            -- Role
                                JOIN    mdl_user_enrolments				  enrol	    ON  enrol.userid = u.id
                                JOIN    mdl_enrol						  e		    ON  e.id = enrol.enrolid
																					AND e.status = 0
								JOIN    mdl_role_assignments 			  rolea 	ON  enrol.userid = rolea.userid
                                JOIN    mdl_role						  role	    ON  rolea.roleid = role.id
                                -- Course
                                JOIN    mdl_course						  course	ON  course.id = e.courseid
                                -- Course Completion
	                       LEFT JOIN    mdl_course_completions		      ccomp	    ON  ccomp.course = course.id
																					AND ccomp.userid = enrol.userid
                                -- To only get the genderdata
                                WHERE   uinfo.fieldid = 7";
                                // LIMIT $currentpage, $rowsperpage
        try {
            $params = array();
            $params['industrycode'] = $industrycode;

            $rdo = $DB->get_records_sql($userquery, $params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $infouser = new stdClass();
                    $infouser->id = $instance->id;
                    $infouser->firstname = $instance->firstname;
                    $infouser->lastname = $instance->lastname;
                    $infouser->gender = $instance->gender;
                    $infouser->email = $instance->email;
                    $infouser->course = $instance->coursename;
                    $infouser->role = $instance->role;
                    $infouser->levelthree = $instance->levelthree;
                    $infouser->leveltwo = $instance->leveltwo;
                    $infouser->levelone = $instance->levelone;
                    $infouser->levelzero = $instance->levelzero;

                    if ($instance->gender = '1') {
                        $infouser->gender = 'Male';
                    } else if ($instance->gender = '2') {
                        $infouser->gender = 'Female';
                    } else {
                        $infouser->gender = '-';
                    }

                    $epoch = $instance->completed;
                    $dt = new DateTime("@$epoch");
                    $infouser->completed = $dt->format('d.m.Y');

                    $myusers[$instance->id] = $infouser;
                }
            }

            return $myusers;

        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch

    }  // end get_users

    /**
     * @param integer $industrycode It's the integer written in the form
     * @return array
     * @throws Exception
     *
     * Function used to get the amount of results and used to get the correct page display
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function local_ksl_industrysearch_count($industrycode) {

        // Variables!
        global $DB;
        $mycount = null;
        $rdo = null;

        $userquery = "SELECT 	COUNT(uic.id) as 'count'
                      FROM		mdl_user_info_competence_data uic
	                            JOIN	mdl_user 						  u         ON  u.id = uic.userid
																					AND u.deleted = 0
                                -- Level Three
                                JOIN	mdl_report_gen_companydata		  co        ON  co.id = uic.companyid
											                                        AND co.industrycode = :industrycode
                                -- Level Two
                                JOIN	mdl_report_gen_company_relation	  cr_two    ON  cr_two.companyid = co.id
                                JOIN	mdl_report_gen_companydata		  co_two    ON  co_two.id = cr_two.parentid
												                                    AND co_two.hierarchylevel = 2
                                -- Level One
	                            JOIN	mdl_report_gen_company_relation	  cr_three  ON  cr_three.companyid = co_two.id
                                JOIN	mdl_report_gen_companydata		  co_three  ON  co_three.id = cr_three.parentid
													                                AND co_three.hierarchylevel = 1
                                -- Level Zero
	                            JOIN	mdl_report_gen_company_relation	  cr_four   ON  cr_four.companyid = co_three.id
                                JOIN	mdl_report_gen_companydata		  co_four   ON  co_four.id = cr_four.parentid
													                                AND co_four.hierarchylevel = 0
  	                            -- Gender
	                       LEFT JOIN	mdl_user_info_data		          uinfo	    ON  u.id = uinfo.userid
	                            -- Role
                                JOIN    mdl_user_enrolments				  enrol	    ON  enrol.userid = u.id
                                JOIN    mdl_enrol						  e		    ON  e.id = enrol.enrolid
																					AND e.status = 0
								JOIN    mdl_role_assignments 			  rolea 	ON  enrol.userid = rolea.userid
                                JOIN    mdl_role						  role	    ON  rolea.roleid = role.id
                                -- Course
                                JOIN    mdl_course						  course	ON  course.id = e.courseid
                                -- Course Completionm
	                       LEFT JOIN    mdl_course_completions		      ccomp	    ON  ccomp.course = course.id
																					AND ccomp.userid = enrol.userid
                                -- To only get the genderdata
                                WHERE   uinfo.fieldid = 7";
        // LIMIT $currentpage, $rowsperpage
        try {
            $params = array();
            $params['industrycode'] = $industrycode;

            $rdo = $DB->get_record_sql($userquery, $params);
            if ($rdo) {
                $mycount = $rdo->count;
            }

            return $mycount;

        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch

    }  // end get_users_count

    /**
     * @param array     $userarray      All the users found
     * @param integer   $industrycode   The industrycode
     * @return string
     * @throws Exception
     *
     * Displays the users and calls other functions to create the tables
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function display_users($userarray, $industrycode) {
        $out = '';
        $url = null;
        global $CFG;

        if ($userarray) {
            try {
                // Display!
                if ($userarray) {
                    $out .= html_writer::start_div('Industrycoderpt');
                    $out .= "<h2>" . get_string('industrycoderpt', 'local_ksl') . "</h2>";
                    $out .= html_writer::end_div();
                    $out .= "</br>";
                    if ($industrycode) {
                        $out .= html_writer::start_div('industrytop');
                        $out .= "<h5>" . get_string('industrycode', 'local_ksl') . "</h5><h6>" .  " " . $industrycode . "</h6>";
                        $out .= html_writer::end_div();
                    }
                    $out .= html_writer::start_div('block_users');
                        // Add Users!
                        $out .= self::add_user_table($userarray);
                    $out .= html_writer::end_div();

                    // Add back url
                    $out .= html_writer::start_div('back_btn');
                        $url = new moodle_url('/local/ksl/index.php');
                        $back = get_String('back', 'local_ksl');
                        $out .= "<div><a href=$url> $back </a>";
                    $out .= html_writer::end_div();//back_btn
                } else {
                    $out .= "";
                }

            } catch (Exception $ex) {
                throw $ex;
            } // end try_catch
        } else {
            // Display!
                $out .= html_writer::start_div('Industrycoderpt');
                $out .= "<h2>" . get_string('industrycoderpt', 'local_ksl') . "</h2>";
                $out .= html_writer::end_div();
                $out .= "</br>";
                if ($industrycode) {
                    $out .= html_writer::start_div('industrytop');
                    $out .= "<h4>" . get_string('industrycode', 'local_ksl') . "</h4><h5>" .  " " . $industrycode . "</h5>";
                    $out .= html_writer::end_div();
                }
                $out .= html_writer::start_div('block_users');
                // Add Users!
                $out .= get_string('noresults', 'local_ksl');
                $out .= html_writer::end_div();

                // Add back url
                $out .= html_writer::start_div('back_btn');
                $url = new moodle_url('/local/ksl/index.php');
                $back = get_String('back', 'local_ksl');
                $out .= "<div><a href=$url> $back </a>";
                $out .= html_writer::end_div();//back_btn
        }
        return $out;
    } //end display_users

    /**
     * @param array $userarray  All the users
     * @return string
     *
     * Adds the users table and calls to create the headers and the content
     */
    public static function add_user_table($userarray) {
        // Variables!
        $table = '';
        $table .= html_writer::start_tag('table', array('class' => 'generaltable'));
        // ...adding the header.
        $table .= self::add_user_headertable();
        // ...adding the content.
        if ($userarray) {
            $table .= self::add_user_content($userarray);
        }
        $table .= html_writer::end_tag('table');

        return $table;
    } //end add_user_table

    /**
     * @return string
     *
     * Creates the headers
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function add_user_headertable() {
        // Variables!
        $header         = '';
        $strname        = get_string('headername', 'local_ksl');
        $stremail       = get_string('headeremail', 'local_ksl');
        $strcourse      = get_string('headercourse', 'local_ksl');
        $strrole        = get_string('headerrole', 'local_ksl');
        $strcompleted   = get_string('headercompleted', 'local_ksl');
        $mylevelthree   = get_string('headerlevelthree', 'local_ksl') . '</br>' . get_string('headerlevelthree1', 'local_ksl');
        $myleveltwo     = get_string('headerleveltwo', 'local_ksl') . '</br>' . get_string('headerleveltwo1', 'local_ksl');
        $mylevelone     = get_string('headerlevelone', 'local_ksl') . '</br>' . get_string('headerlevelone1', 'local_ksl');
        $mylevelzero    = get_string('headerlevelzero', 'local_ksl') . '</br>' . get_string('headerlevelzero1', 'local_ksl');
        $strgender      = get_string('headergender', 'local_ksl');

        $header .= html_writer::start_tag('thead');
        $header .= html_writer::start_tag('tr', array('class' => 'header_user'));
            // Username!
            $header .= html_writer::start_tag('th', array('class' => 'user'));
            $header .= $strname;
            $header .= html_writer::end_tag('th');
            // Email!
            $header .= html_writer::start_tag('th', array('class' => 'info'));
            $header .= $stremail;
            $header .= html_writer::end_tag('th');
            // Gender!
            $header .= html_writer::start_tag('th', array('class' => 'info'));
            $header .= $strgender;
            $header .= html_writer::end_tag('th');
            // Course!
            $header .= html_writer::start_tag('th', array('class' => 'info'));
            $header .= $strcourse;
            $header .= html_writer::end_tag('th');
            // Role!
            $header .= html_writer::start_tag('th', array('class' => 'info'));
            $header .= $strrole;
            $header .= html_writer::end_tag('th');
            // Completed!
            $header .= html_writer::start_tag('th', array('class' => 'date'));
            $header .= $strcompleted;
            $header .= html_writer::end_tag('th');
            // Level Three!
            $header .= html_writer::start_tag('th', array('class' => 'info'));
            $header .= $mylevelthree;
            $header .= html_writer::end_tag('th');
            // Level Two!
            $header .= html_writer::start_tag('th', array('class' => 'info'));
            $header .= $myleveltwo;
            $header .= html_writer::end_tag('th');
            // Level One!
            $header .= html_writer::start_tag('th', array('class' => 'info'));
            $header .= $mylevelone;
            $header .= html_writer::end_tag('th');
            // Level Zero!
            $header .= html_writer::start_tag('th', array('class' => 'info'));
            $header .= $mylevelzero;
        $header .= html_writer::end_tag('th');
        $header .= html_writer::end_tag('tr');
        $header .= html_writer::end_tag('thead');

        return $header;
    } //end add_user_headertable

    /**
     * @param array $userarray  All the users
     * @return string
     *
     * Creates all the content to the table
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function add_user_content($userarray) {
        // Variables!
        $body = ' ';
        $strname        = get_string('headername', 'local_ksl');
        $stremail       = get_string('headeremail', 'local_ksl');
        $strgender      = get_string('headergender', 'local_ksl');
        $strcourse      = get_string('headercourse', 'local_ksl');
        $strrole        = get_string('headerrole', 'local_ksl');
        $strcompleted   = get_string('headercompleted', 'local_ksl');
        $mylevelthree   = get_string('headerlevelthree', 'local_ksl') . '</br>' . get_string('headerlevelthree1', 'local_ksl');
        $myleveltwo     = get_string('headerleveltwo', 'local_ksl') . '</br>' . get_string('headerleveltwo1', 'local_ksl');
        $mylevelone     = get_string('headerlevelone', 'local_ksl') . '</br>' . get_string('headerlevelone1', 'local_ksl');
        $mylevelzero    = get_string('headerlevelzero', 'local_ksl') . '</br>' . get_string('headerlevelzero1', 'local_ksl');

        foreach ($userarray as $uservalue) {
            $body .= html_writer::start_tag('tr');
            // Username!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $strname));
            $body .= $uservalue->firstname;
            $body .= " ";
            $body .= $uservalue->lastname;
            $body .= html_writer::end_tag('td');
            // Email!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $stremail));
            $body .= $uservalue->email;
            $body .= html_writer::end_tag('td');
            // Gender!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $strgender));
            $body .= $uservalue->gender;
            $body .= html_writer::end_tag('td');
            // Course!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $strcourse));
            $body .= $uservalue->course;
            $body .= html_writer::end_tag('td');
            // Role!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $strrole));
            $body .= $uservalue->role;
            $body .= html_writer::end_tag('td');
            // Completed!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $strcompleted));
            $body .= $uservalue->completed;
            $body .= html_writer::end_tag('td');
            // Level Three!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $mylevelthree));
            $body .= $uservalue->levelthree;
            $body .= html_writer::end_tag('td');
            // Level Two!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $myleveltwo));
            $body .= $uservalue->leveltwo;
            $body .= html_writer::end_tag('td');
            // Level One!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $mylevelone));
            $body .= $uservalue->levelone;
            $body .= html_writer::end_tag('td');
            // Level Zero!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $mylevelzero));
            $body .= $uservalue->levelzero;
            $body .= html_writer::end_tag('td');

            $body .= html_writer::end_tag('tr');
        }
        return $body;
    } //end add_user_content

    /**
     * @param integer   $level      The level
     * @param int       $parentid   The parent industrycode
     * @return array
     * @throws Exception
     *
     * Creates the list of companies based on the parent
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_companies_level_lst($level, $parentid = 0) {
        /* Variables */
        global $DB;
        $levels = array();

        try {
            // List companies!
            $levels[0] = get_string('select_level_list', 'report_manager');

            // Criteria!
            $params = array();
            $params['level']    = $level;

            // Query!
            $sql = " SELECT     DISTINCT  rcd.id,
                                          rcd.name,
                                          rcd.industrycode
                     FROM       {report_gen_companydata} rcd ";

            // Parent!
            if ($parentid) {
                $sql .= " JOIN  {report_gen_company_relation} rcr   ON    rcr.companyid = rcd.id
                                                                    AND   rcr.parentid  IN ($parentid) ";
            }

            // Conditions!
            $sql .= " WHERE     rcd.hierarchylevel = :level ";

            // Order!
            $sql .= " ORDER BY  rcd.industrycode, rcd.name ASC ";

            // Exec!
            $rdo = $DB->get_records_sql($sql, $params);
            if ($rdo) {
                foreach ($rdo as $field) {
                    $levels[$field->id] = $field->industrycode . ' - '. $field->name;
                }//foreach
            }//if_rdo

            return $levels;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_companies_levellist

    /**
     * @param integer $levelzero    Industrycode
     * @param integer $levelone     Industrycode
     * @param integer $leveltwo     Industrycode
     * @param integer $levelthree   Industrycode
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_javascript_values($levelzero, $levelone, $leveltwo, $levelthree) {

        global $PAGE;

        /* Initialise variables */
        $name       = 'lst_levels';
        $path       = '/local/ksl/js/levels.js';
        $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
        $grpthree   = array('none', 'moodle');
        $strings    = array($grpthree);

        /* Initialise js module */
        $jsmodule   = array('name'        => $name,
                            'fullpath'    => $path,
                            'requires'    => $requires,
                            'strings'     => $strings
        );

        $PAGE->requires->js_init_call('M.core_user.init_organization',
            array($levelzero, $levelone, $leveltwo, $levelthree),
            false,
            $jsmodule
        );
    }

    /**
     * @param array     $userarray  All the users
     * @return string
     * @throws Exception
     *
     * Displays with the organization structure
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function display_org($userarray) {
        $out = '';
        $url = null;
        global $CFG;

        if ($userarray) {
            try {
                // Display!
                if ($userarray) {
                    $out .= html_writer::start_div('organizationrpt');
                    $out .= "<h2>" . get_string('organizationrpt', 'local_ksl') . "</h2>";
                    $out .= html_writer::end_div();
                    $out .= "</br>";
                    $out .= html_writer::start_div('orginfo');
                    $out .= self::add_orginfo($userarray);
                    $out .= html_writer::end_div();
                    $out .= html_writer::start_div('block_users');
                    $out .= self::add_org_table($userarray);
                    $out .= html_writer::end_div();
                    $out .= html_writer::start_div('back_btn');
                    $url = new moodle_url('/local/ksl/index.php');
                    $back = get_String('back', 'local_ksl');
                    $out .= "<div><a href=$url> $back </a>";
                    $out .= html_writer::end_div();
                } else {
                    $out .= "";
                }

            } catch (Exception $ex) {
                throw $ex;
            } // end try_catch
        } else {

            $out .= html_writer::start_div('organizationrpt');
            $out .= "<h2>" . get_string('organizationrpt', 'local_ksl') . "</h2>";
            $out .= html_writer::end_div();
            $out .= "</br>";
            $out .= html_writer::start_div('orginfo');
            $out .= self::add_orginfo($userarray);
            $out .= html_writer::end_div();
            $out .= html_writer::start_div('block_users');
            $out .= get_string('noresults', 'local_ksl');
            $out .= html_writer::end_div();
            $out .= html_writer::start_div('back_btn');
            $url = new moodle_url('/local/ksl/index.php');
            $back = get_String('back', 'local_ksl');
            $out .= "<div><a href=$url> $back </a>";
            $out .= html_writer::end_div();

        }
        return $out;
    } //end display_users

    /**
     * @param array     $userarray  All the users
     * @return string
     *
     * Adds the organization info
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function add_orginfo($userarray) {
        $info = '';

        foreach ($userarray as $uservalue) {
            $info .= html_writer::start_div('levellsttxt');
            $info .= 'Level Zero';
            $info .= html_writer::end_div(); // ...levellsttxt.

            $info .= html_writer::start_div('levellst');
            $info .= $uservalue->levelzero;
            $info .= html_writer::end_div(); // ...levellst.

            $info .= html_writer::start_div('levellsttxt');
            $info .= 'Level One';
            $info .= html_writer::end_div(); // ...levellsttxt.

            $info .= html_writer::start_div('levellst');
            $info .= $uservalue->levelone;
            $info .= html_writer::end_div(); // ...levellst.

            $info .= html_writer::start_div('levellsttxt');
            $info .= 'Level Two';
            $info .= html_writer::end_div(); // ...levellsttxt.

            $info .= html_writer::start_div('levellst');
            $info .= $uservalue->leveltwo;
            $info .= html_writer::end_div(); // ...levellst.

            $info .= html_writer::start_div('levellsttxt');
            $info .= 'Level Three';
            $info .= html_writer::end_div(); // ...levellsttxt.

            $info .= html_writer::start_div('levellst');
            $info .= $uservalue->levelthree;
            $info .= html_writer::end_div(); // ...levellst.
        }
        return $info;
    }

    /**
     * @param array     $userarray  All the users
     * @return string
     *
     * Adds the organization table
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function add_org_table($userarray) {
        // Variables!
        $table = '';
        $table .= html_writer::start_tag('table', array('class' => 'generaltable'));
        // ...adding the header.
        $table .= self::add_org_headertable();
        // ...adding the content.
        if ($userarray) {
            $table .= self::add_org_content($userarray);
        }
        $table .= html_writer::end_tag('table');

        return $table;
    } //end add_user_table

    /**
     * @return string
     *
     * Adds the headertable
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function add_org_headertable() {
        // Variables!
        $header         = '';
        $strname        = get_string('headername', 'local_ksl');
        $stremail       = get_string('headeremail', 'local_ksl');
        $strcourse      = get_string('headercourse', 'local_ksl');
        $strrole        = get_string('headerrole', 'local_ksl');
        $strcompleted   = get_string('headercompleted', 'local_ksl');
        $strgender      = get_string('headergender', 'local_ksl');

        $header .= html_writer::start_tag('thead');
        $header .= html_writer::start_tag('tr', array('class' => 'header_user'));
        // Username!
        $header .= html_writer::start_tag('th', array('class' => 'user'));
        $header .= $strname;
        $header .= html_writer::end_tag('th');
        // Gender!
        $header .= html_writer::start_tag('th', array('class' => 'info'));
        $header .= $strgender;
        $header .= html_writer::end_tag('th');
        // Email!
        $header .= html_writer::start_tag('th', array('class' => 'info'));
        $header .= $stremail;
        $header .= html_writer::end_tag('th');
        // Course!
        $header .= html_writer::start_tag('th', array('class' => 'info'));
        $header .= $strcourse;
        $header .= html_writer::end_tag('th');
        // Role!
        $header .= html_writer::start_tag('th', array('class' => 'info'));
        $header .= $strrole;
        $header .= html_writer::end_tag('th');
        // Completed!
        $header .= html_writer::start_tag('th', array('class' => 'info'));
        $header .= $strcompleted;
        $header .= html_writer::end_tag('th');
        $header .= html_writer::end_tag('tr');
        $header .= html_writer::end_tag('thead');

        return $header;
    } //end add_user_headertable

    /**
     * @param array     $userarray  All the users
     * @return string
     *
     * Adds the organization table content
     *
     * @creationeDate   28/03/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function add_org_content($userarray) {
        // Variables!
        $body = ' ';
        $strname        = get_string('headername', 'local_ksl');
        $stremail       = get_string('headeremail', 'local_ksl');
        $strcourse      = get_string('headercourse', 'local_ksl');
        $strrole        = get_string('headerrole', 'local_ksl');
        $strcompleted   = get_string('headercompleted', 'local_ksl');
        $strgender      = get_string('headergender', 'local_ksl');

        foreach ($userarray as $uservalue) {
            $body .= html_writer::start_tag('tr');
            // Username!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $strname));
            $body .= $uservalue->firstname;
            $body .= " ";
            $body .= $uservalue->lastname;
            $body .= html_writer::end_tag('td');
            // Gender!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $stremail));
            $body .= $uservalue->gender;
            $body .= html_writer::end_tag('td');
            // Email!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $strcourse));
            $body .= $uservalue->email;
            $body .= html_writer::end_tag('td');
            // Course!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $strrole));
            $body .= $uservalue->course;
            $body .= html_writer::end_tag('td');
            // Role!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $strcompleted));
            $body .= $uservalue->role;
            $body .= html_writer::end_tag('td');
            // Completed!
            $body .= html_writer::start_tag('td', array('class' => 'user', 'data-label' => $strgender));
            $body .= $uservalue->completed;
            $body .= html_writer::end_tag('td');
            $body .= html_writer::end_tag('tr');
        }
        return $body;
    } //end add_user_content
}