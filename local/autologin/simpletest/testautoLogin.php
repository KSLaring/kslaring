<?php

require_once $CFG->dirroot . '/local/autologin/autologin.class.php';

/**
 * Test Autologin
 * User: Urs Hunkler
 * Date: 2011-07-14
 *
 * Test Autologin class functionality
 * local/autologin/simpletest
 */
class testAutoLogin extends UnitTestCase
{
    protected $courseid;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp( )
    {
        $this->courseid = 5;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown( )
    {
    }

    /**
     *
     */
    public function testAutoLoginIfAutoLoginClassExisits()
    {
        $autologin = new AutoLogin( $this->courseid );
        $this->assertNotNull( $autologin,
                           'Test for exisitance of autologin class: [%s]' );
    }

    /**
     *
     */
    public function testAutoLoginCreateUser()
    {
        $autologin = new AutoLogin( $this->courseid );
        $user = $autologin->createUser();

        $this->assertEqual( $user->firstname,
                            get_string( 'firstname', 'local_autologin' ),
                            'Test if firstname equals given name in langfile: [%s]' );
        $this->assertEqual( $user->lastname,
                            get_string( 'lastname', 'local_autologin' ),
                            'Test if lastname equals given name in langfile: [%s]' );
        $this->assertEqual( $user->city,
                            get_string( 'city', 'local_autologin' ),
                            'Test if city equals given name in langfile: [%s]' );
        $this->assertEqual( $user->country,
                            get_string( 'countrycode', 'local_autologin' ),
                            'Test if countrycode equals given name in langfile: [%s]' );
//        $this->dump( $user );
    }

    /**
     *
     */
    public function testAutoLoginInsertUserInDb()
    {
        global $DB;

        $userfromdb = new stdClass();

        $autologin = new AutoLogin( $this->courseid );
        $autologinDB = new AutoLoginDBConnection();
        $user = $autologin->createUser();

        if( $user = $autologinDB->insertUserInDb( $user ) )
        {
            $userfromdb = $DB->get_record( 'user', array( 'id' => $user->id ) );
            $DB->delete_records( 'user', array( 'id' => $user->id ) );
        }

        $this->assertEqual( $user->username,
                            $userfromdb->username,
                            'Test if username from created user is equal to username from DB: [%s]' );

    }

    /**
     *
     */
    public function testAutoLoginEnrollNewUserInCourse()
    {
        global $DB;

        $enrollmentid = 0;

        $autologin = new AutoLogin( $this->courseid );
        $autologinDB = new AutoLoginDBConnection();
        $user = $autologin->createUser();

        if( $user = $autologinDB->insertUserInDb( $user ) )
        {
            $autologin->enrollUserInCourse( $user, $this->courseid );
        }

        $context = get_context_instance( CONTEXT_COURSE, $this->courseid );
        $enrolledusers = get_enrolled_users( $context );

        $this->assertTrue( array_key_exists( $user->id, $enrolledusers ),
                           'Test if the userid of the created user exists in the list of enrolled users: [%s]' );

//        $this->dump( $enrolledusers );
    }


    /** - - - Helper functions - - - - - - - - - - - - - - - - - - - - - - - */

}
