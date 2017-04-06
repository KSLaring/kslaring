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
     * @return mixed|null
     * @throws Exception
     *
     * User to find out what field the gender is
     *
     * @creationeDate   31/03/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function get_genderfield() {
        global $DB;
        $genderid = null;
        $rdo = null;

        $userquery = "SELECT id
                      FROM {user_info_field}
                      WHERE datatype = 'gender'";

        try {
            $rdo = $DB->get_record_sql($userquery);
            if ($rdo) {
                return $rdo->id;
            };

        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    }

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
    public static function local_ksl_organizationsearch($id, $page, $perpage) {
        global $DB;
        $myusers = array();
        $rdo = null;

                $userquery = "SELECT 	CONCAT(u.id,'_',c.id) as 'id', 
                                        u.firstname           as 'firstname',
                                        u.lastname            as 'lastname',
                                        u.email               as 'email',
                                        uid.data              as 'gender',
                                        c.fullname            as 'coursename',
                                        r.shortname           as 'role',
                                        ccomp.timecompleted   as 'completed'
                                FROM	{user} u
                                    -- Gender
                                    LEFT JOIN {user_info_data}          uid   ON uid.userid = u.id
                                                                              AND uid.fieldid = :gender
                                    -- Get courses connected with the user
                                    JOIN 	{user_enrolments}		    ue	  ON  ue.userid 	= u.id
                                    JOIN	{enrol}				        e	  ON  e.id 		= ue.enrolid
                                                                              AND e.status 	= 0
                                    JOIN	{course}				    c	  ON  c.id 		= e.courseid
                                    -- Which role in the curse
                                    JOIN    {context}				    ct	  ON  ct.instanceid = c.id
                                    JOIN	{role_assignments}	        ra	  ON  ra.contextid  = ct.id
                                                                              AND ra.userid 	  = ue.userid
                                    JOIN 	{role}				        r	  ON  r.id		  = ra.roleid
                                    -- Completion
                                    LEFT JOIN    {course_completions}   ccomp ON  ccomp.course = c.id
                                                                              AND ccomp.userid = ue.userid
                                    -- Org
                                    JOIN {user_info_competence_data} 	uic   ON 	uic.userid = ra.userid
                                    JOIN {report_gen_companydata} 	    rgc   ON	rgc.id = uic.companyid
                                                                              AND rgc.id = :myid";

        try {
            // Get the fieldid for gender.
            $genderid = self::get_genderfield();

            // Criteria.
            $params = array();
            $params['gender'] = $genderid;
            $params['myid'] = $id;

            $rdo = $DB->get_records_sql($userquery, $params, $page, $perpage);
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

                    if ($instance->gender = '1') {
                        $infouser->gender = get_string('male', 'local_ksl');
                    } else if ($instance->gender = '2') {
                        $infouser->gender = get_string('female','local_ksl');
                    } else {
                        $infouser->gender = '-';
                    }

                    if ($instance->completed) {
                        $infouser->completed = userdate($instance->completed,'%d.%m.%Y', 99, false); //$dt->format('d.m.Y');
                    } else {
                        $infouser->completed = " - ";
                    }

                    $myusers[$instance->id] = $infouser;
                }
            }

            return $myusers;

        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch

    }  // end get_users

    public static function local_ksl_organizationsearch_empty($l0, $l1, $l2, $l3) {
        global $DB;
        $rdo = null;
        $lst = array();

        $userquery = "SELECT  co.id,
                              co.name
                      FROM    {report_gen_companydata} co
                      WHERE   co.id IN (:levelzero, :levelone, :leveltwo, :levelthree);";

        try {
            $params = array();
            $params['levelzero'] = $l0;
            $params['levelone'] = $l1;
            $params['leveltwo'] = $l2;
            $params['levelthree'] = $l3;

            $rdo = $DB->get_records_sql($userquery, $params);

            if ($rdo) {
                foreach($rdo as $instance) {
                    $lst[$instance->id] = $instance->name;
                }
            }
            return $lst;

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
                         LEFT JOIN  {course_completions}		      ccomp	    ON  ccomp.course = course.id
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
    public static function local_ksl_industrysearch($industrycode, $currentpage, $rowsperpage) {

        // Variables!
        global $DB;
        $myusers = array();
        $rdo = null;

        $userquery = "SELECT  DISTINCT 
                                CONCAT(u.id,'_',co.id,'_',c.id) as 'id', 
                                u.id          as 'userid',
                                u.firstname   as 'firstname',
                                u.lastname    as 'lastname', 
                                u.email       as 'email',
                                c.fullname    as 'coursename',
                                r.shortname   as 'role',
                                uid.data      as 'gender',
                                co.name       as 'levelthree',
                                co_two.name   as 'leveltwo',
                                co_one.name   as 'levelone',
                                co_zero.name  as 'levelzero',
                                ccomp.timecompleted as 'completed'
                                
                        FROM		{user} u
                            -- Get courses connected with the user
                            JOIN 	{user_enrolments}		ue	ON  ue.userid 	= u.id
                            JOIN	{enrol}				    e	ON  e.id 		= ue.enrolid
                                                                AND e.status 	= 0
                            JOIN	{course}				c	ON  c.id 		= e.courseid
                            -- Whic role in the curse
                            JOIN    {context}				ct	ON  ct.instanceid = c.id
                            JOIN	{role_assignments}	    ra	ON  ra.contextid  = ct.id
                                                                AND ra.userid 	  = ue.userid
                            JOIN 	{role}				    r	ON  r.id		  = ra.roleid
                            -- Gender
                            LEFT JOIN {user_info_data}   	uid ON  uid.userid 	  = ra.userid
                                                                AND uid.fieldid   = :gender
                            -- Competence profile
                            JOIN 	{user_info_competence_data} 	uic ON  uic.userid = ra.userid
                            JOIN	{report_gen_companydata}		co	ON  co.id = uic.companyid
                                                                        AND co.industrycode = :industrycode
                            -- Level Two
                            JOIN	{report_gen_company_relation}	  cr_two    ON  cr_two.companyid = co.id
                            JOIN	{report_gen_companydata}		  co_two    ON  co_two.id = cr_two.parentid
                                                                                AND co_two.hierarchylevel = 2
                            -- Level One
                            JOIN	{report_gen_company_relation}	  cr_one  	ON  cr_one.companyid = co_two.id
                            JOIN	{report_gen_companydata}		  co_one  	ON  co_one.id = cr_one.parentid
                                                                                AND co_one.hierarchylevel = 1
                            -- Level Zero
                            JOIN	{report_gen_company_relation}	  cr_zero   ON  cr_zero.companyid = co_one.id
                            JOIN	{report_gen_companydata}		  co_zero   ON  co_zero.id = cr_zero.parentid
                                                                                AND co_zero.hierarchylevel = 0
                            -- Completion
                            LEFT JOIN    {course_completions}		  ccomp	    ON  ccomp.course = c.id
                                                                                AND ccomp.userid = ue.userid
                            
                        WHERE 		u.deleted 	= 0
                            AND 	u.username != 'guest'
                        ORDER BY 	u.firstname, u.lastname";

        try {
            // Get the correct fieldid for gender.
            $genderid = self::get_genderfield();

            // Criteria.
            $params = array();
            $params['gender'] = $genderid;
            $params['industrycode'] = $industrycode;

            // Execute the sql and create the object.
            $rdo = $DB->get_records_sql($userquery, $params, $currentpage, $rowsperpage);
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
                        $infouser->gender = get_string('male', 'local_ksl');
                    } else if ($instance->gender = '2') {
                        $infouser->gender = get_string('female', 'local_ksl');
                    } else {
                        $infouser->gender = '-';
                    }

                    if ($instance->completed) {
                        $infouser->completed = userdate($instance->completed,'%d.%m.%Y', 99, false);
                    } else {
                        $infouser->completed = " - ";
                    }

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

        $userquery = "SELECT  COUNT(CONCAT(u.id,'_',co.id,'_',c.id)) as 'count'     
                        FROM		{user} u
                            -- Get courses connected with the user
                            JOIN 	{user_enrolments}		ue	ON  ue.userid 	= u.id
                            JOIN	{enrol}				    e	ON  e.id 		= ue.enrolid
                                                                AND e.status 	= 0
                            JOIN	{course}				c	ON  c.id 		= e.courseid
                            -- Whic role in the curse
                            JOIN    {context}				ct	ON  ct.instanceid = c.id
                            JOIN	{role_assignments}	    ra	ON  ra.contextid  = ct.id
                                                                AND ra.userid 	  = ue.userid
                            JOIN 	{role}				    r	ON  r.id		  = ra.roleid
                            -- Gender
                            LEFT JOIN {user_info_data}   	uid ON  uid.userid 	  = ra.userid
                                                                AND uid.fieldid   = 7
                            -- Competence profile
                            JOIN 	{user_info_competence_data} 	uic ON  uic.userid = ra.userid
                            JOIN	{report_gen_companydata}		co	ON  co.id = uic.companyid
                                                                        AND co.industrycode = :industrycode
                            -- Level Two
                            JOIN	{report_gen_company_relation}	  cr_two    ON  cr_two.companyid = co.id
                            JOIN	{report_gen_companydata}		  co_two    ON  co_two.id = cr_two.parentid
                                                                                AND co_two.hierarchylevel = 2
                            -- Level One
                            JOIN	{report_gen_company_relation}	  cr_one  	ON  cr_one.companyid = co_two.id
                            JOIN	{report_gen_companydata}		  co_one  	ON  co_one.id = cr_one.parentid
                                                                                AND co_one.hierarchylevel = 1
                            -- Level Zero
                            JOIN	{report_gen_company_relation}	  cr_zero   ON  cr_zero.companyid = co_one.id
                            JOIN	{report_gen_companydata}		  co_zero   ON  co_zero.id = cr_zero.parentid
                                                                                AND co_zero.hierarchylevel = 0
                            -- Completion
                            LEFT JOIN    {course_completions}		  ccomp	    ON  ccomp.course = c.id
                                                                                AND ccomp.userid = ue.userid
                            
                        WHERE 		u.deleted 	= 0
                            AND 	u.username != 'guest'
                        ORDER BY 	u.firstname, u.lastname";
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
        $url = new moodle_url('/local/ksl/index.php');
        $back = get_String('back', 'local_ksl');

        if ($userarray) {
            try {
                // Display!
                if ($userarray) {
                    $out .= "</br>";
                    if ($industrycode) {
                        $out .= html_writer::start_div('industrytop');
                        $out .= "<h5>" . get_string('industrycode', 'local_ksl') . "</h5><h6>" .  " " . $industrycode . "</h6>";
                        $out .= html_writer::end_div();
                    }

                    // Add back url
                    $out .= html_writer::start_div('back_btn');
                    $out .= "<div><a href=$url> $back </a>";
                    $out .= html_writer::end_div();//back_btn

                    $out .= html_writer::start_div('block_users');
                        // Add Users!
                        $out .= self::add_user_table($userarray);
                    $out .= html_writer::end_div();

                    // Add back url
                    $out .= html_writer::start_div('back_btn');
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

        $mylevelone = get_string('headerlevelone', 'local_ksl') . '</br>' . get_string('headerlevelone1', 'local_ksl');
        $mylevelzero = get_string('headerlevelzero', 'local_ksl') . '</br>' . get_string('headerlevelzero1', 'local_ksl');

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
    public static function display_org($userarray)
    {
        $out = '';
        $url = null;
        global $CFG;

        if ($userarray) {
            try {
                // Display!
                if ($userarray) {
                    $out .= "</br>"; /*
                    $out .= html_writer::start_div('orginfo');
                    $out .= self::add_orginfo($userarray);
                    $out .= html_writer::end_div(); */
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
            return $out;
        }
    }//end display_users

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
    public static function add_orginfo_empty($userarray) {
        GLOBAL $SESSION;
        $info = '';
        $mylevelthree   = get_string('headerlevelthree', 'local_ksl') . get_string('headerlevelthree1', 'local_ksl');
        $myleveltwo     = get_string('headerleveltwo', 'local_ksl') . get_string('headerleveltwo1', 'local_ksl');
        $mylevelone     = get_string('headerlevelone', 'local_ksl') . get_string('headerlevelone1', 'local_ksl');
        $mylevelzero    = get_string('headerlevelzero', 'local_ksl') . get_string('headerlevelzero1', 'local_ksl');

        $info .= html_writer::start_div('orginfo');
            $info .= html_writer::start_div('levellsttxt');
            $info .= $mylevelzero;
            $info .= html_writer::end_div(); // ...levellsttxt.

            $info .= html_writer::start_div('levellst');
            $info .= $userarray[$SESSION->organization0];
            $info .= html_writer::end_div(); // ...levellst.

            $info .= html_writer::start_div('levellsttxt');
            $info .= $mylevelone;
            $info .= html_writer::end_div(); // ...levellsttxt.

            $info .= html_writer::start_div('levellst');
            $info .= $userarray[$SESSION->organization1];
            $info .= html_writer::end_div(); // ...levellst.

            $info .= html_writer::start_div('levellsttxt');
            $info .= $myleveltwo;
            $info .= html_writer::end_div(); // ...levellsttxt.

            $info .= html_writer::start_div('levellst');
            $info .= $userarray[$SESSION->organization2];
            $info .= html_writer::end_div(); // ...levellst.

            $info .= html_writer::start_div('levellsttxt');
            $info .= $mylevelthree;
            $info .= html_writer::end_div(); // ...levellsttxt.

            $info .= html_writer::start_div('levellst');
            $info .= $userarray[$SESSION->organization3];
            $info .= html_writer::end_div(); // ...levellst.
        $info .= html_writer::end_div(); // ...orginfo.

        $info .= "<br>";

        return $info;
    }

    /**
     * @return string
     *
     * Function to say that no results were found.
     *
     * @creationeDate   31/03/2017
     * @author          eFaktor     (nas)
     */
    public static function say_empty() {
        $info = '';
        $info .= html_writer::start_div('block_users');
        $info .= get_string('noresults', 'local_ksl');
        $info .= html_writer::end_div();
        $info .= html_writer::start_div('back_btn');
        $url = new moodle_url('/local/ksl/index.php');
        $back = get_String('back', 'local_ksl');
        $info .= "<div><a href=$url> $back </a>";
        $info .= html_writer::end_div();
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